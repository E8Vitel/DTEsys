<?php

namespace App\Http\Controllers;

use App\Mail\DteReceptorMail;
use App\Models\Comuna;
use App\Models\DetalleDte;
use App\Models\DocumentoDte;
use App\Models\FirmaDte;
use App\Models\HistorialEstadoDte;
use App\Models\Region;
use App\Models\TipoDte;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

// Controlador principal del flujo DTE.
// Centraliza consulta, creacion, firma, envio, descargas y reportes.
class DteController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        // Filtros visibles en la pantalla de listado.
        $filters = [
            'search' => $request->string('search')->toString(),
            'tipo' => $request->string('tipo')->toString(),
            'estado' => $request->string('estado')->toString(),
        ];

        // Cada usuario ve solo sus propios documentos.
        $query = DocumentoDte::with([
            'tipoDte',
            'historial' => function ($historial) {
                $historial->orderBy('created_at');
            },
        ])->where('user_id', Auth::id());

        // Busqueda libre por folio, razon social o RUT del receptor.
        if (!empty($filters['search'])) {
            $query->where(function ($searchQuery) use ($filters) {
                $searchQuery
                    ->where('folio', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('razon_social_receptor', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('rut_receptor', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Filtro por tipo de documento.
        if (!empty($filters['tipo'])) {
            $query->where('tipo_dte_id', $filters['tipo']);
        }

        // El estado actual se obtiene desde el ultimo registro del historial.
        $dtes = $query
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($dte) {
                $dte->estado_actual = $dte->historial->last()?->estado_nuevo ?? 'Borrador';
                return $dte;
            });

        // El filtro por estado se hace sobre la coleccion ya enriquecida con estado_actual.
        if (!empty($filters['estado'])) {
            $dtes = $dtes
                ->filter(fn ($dte) => $dte->estado_actual === $filters['estado'])
                ->values();
        }

        return Inertia::render('Dtes/Index', [
            'dtes' => $dtes,
            'tipos' => TipoDte::query()->orderBy('nombre')->get(['id', 'nombre']),
            'estados' => [
                'Borrador',
                'Firmada',
                'Enviada SII',
                'Aceptada SII',
                'Rechazada SII',
                'Enviada Receptor',
                'Documento Aceptado',
                'Documento Rechazado',
                'Documento con Reparos',
                'Anulada',
            ],
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        // Entrega catalogos base para poblar el formulario de emision.
        return Inertia::render('Dtes/Create', [
            'tipos' => TipoDte::all(),
            'regiones' => Region::query()->orderBy('nombre')->get(['id', 'codigo', 'nombre']),
            'comunas' => Comuna::query()->orderBy('nombre')->get(['id', 'region_id', 'nombre']),
        ]);
    }

    public function store(Request $request)
    {
        // Valida datos tributarios, receptor e items del documento.
        $validated = $request->validate([
            'tipo_dte_id' => 'required|exists:tipos_dte,id',
            'rut_receptor' => ['required', 'string', 'max:12', function ($attribute, $value, $fail) {
                if (!$this->esRutChilenoValido($value)) {
                    $fail('El RUT receptor no es valido.');
                }
            }],
            'razon_social_receptor' => 'required|string|max:255',
            'giro_receptor' => 'nullable|string|max:255',
            'direccion_receptor' => 'nullable|string|max:255',
            'region_id' => 'nullable|exists:regiones,id',
            'comuna_id' => [
                'nullable',
                'exists:comunas,id',
                function ($attribute, $value, $fail) use ($request) {
                    if (!$value || !$request->filled('region_id')) {
                        return;
                    }

                    $perteneceARegion = Comuna::query()
                        ->where('id', $value)
                        ->where('region_id', $request->region_id)
                        ->exists();

                    if (!$perteneceARegion) {
                        $fail('La comuna no pertenece a la region seleccionada.');
                    }
                },
            ],
            'detalles' => 'required|array|min:1',
            'detalles.*.cantidad' => 'required|numeric|min:1',
            'detalles.*.descripcion' => 'required|string|max:255',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
            'direccion_destino' => 'nullable|string|max:255',
            'transporte_patente' => 'nullable|string|max:12',
            'transporte_rut_chofer' => ['nullable', 'string', 'max:12', function ($attribute, $value, $fail) {
                if (!blank($value) && !$this->esRutChilenoValido($value)) {
                    $fail('El RUT del chofer no es valido.');
                }
            }],
            'transporte_nombre_chofer' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $tipo = TipoDte::findOrFail($validated['tipo_dte_id']);

        // Regla especial: desde el 1 de mayo de 2026 la Guia de Despacho exige datos de transporte.
        $fechaAnexo25 = now()->startOfDay()->greaterThanOrEqualTo(\Carbon\Carbon::create(2026, 5, 1, 0, 0, 0));
        if ($tipo->codigo === '52' && $fechaAnexo25) {
            $errores = [];

            if (empty($validated['direccion_destino'])) {
                $errores['direccion_destino'] = 'Direccion de destino es obligatoria para Guias desde 2026-05-01.';
            }

            if (empty($validated['transporte_patente'])) {
                $errores['transporte_patente'] = 'Patente de transporte es obligatoria para Guias desde 2026-05-01.';
            }

            if (empty($validated['transporte_rut_chofer'])) {
                $errores['transporte_rut_chofer'] = 'RUT chofer es obligatorio para Guias desde 2026-05-01.';
            }

            if (empty($validated['transporte_nombre_chofer'])) {
                $errores['transporte_nombre_chofer'] = 'Nombre chofer es obligatorio para Guias desde 2026-05-01.';
            }

            if (!empty($errores)) {
                throw ValidationException::withMessages($errores);
            }
        }

        // Recalcula montos en backend para no confiar en calculos del cliente.
        $montoNeto = 0;
        foreach ($validated['detalles'] as $det) {
            $montoNeto += $det['cantidad'] * $det['precio_unitario'];
        }
        $iva = $montoNeto * 0.19;
        $total = $montoNeto + $iva;

        // Se crea el encabezado del documento con estados iniciales y folio interno.
        $dte = DocumentoDte::create([
            'tipo_dte_id' => $tipo->id,
            'user_id' => $user->id,
            'folio' => 'TMP-' . (string) Str::uuid(),
            'fecha_emision' => now(),
            'rut_emisor' => $this->normalizarRutChileno($user->rut ?? '12345678-9'),
            'razon_social_emisor' => 'DTEsys SPA',
            'rut_receptor' => $this->normalizarRutChileno($validated['rut_receptor']),
            'razon_social_receptor' => $validated['razon_social_receptor'],
            'giro_receptor' => $validated['giro_receptor'] ?? null,
            'direccion_receptor' => $validated['direccion_receptor'] ?? null,
            'region_id' => $validated['region_id'] ?? null,
            'comuna_id' => $validated['comuna_id'] ?? null,
            'monto_neto' => $montoNeto,
            'iva' => $iva,
            'monto_total' => $total,
            'timbre' => 'TED-SIM-' . Str::random(12),
            'estado_sii' => 'No enviado',
            'estado_receptor' => 'Sin acusar',
            'direccion_destino' => $validated['direccion_destino'] ?? null,
            'transporte_patente' => $validated['transporte_patente'] ?? null,
            'transporte_rut_chofer' => !empty($validated['transporte_rut_chofer'])
                ? $this->normalizarRutChileno($validated['transporte_rut_chofer'])
                : null,
            'transporte_nombre_chofer' => $validated['transporte_nombre_chofer'] ?? null,
        ]);

        $dte->update([
            'folio' => $this->generarFolioInterno($dte),
        ]);

        // Se inserta cada linea del documento.
        foreach ($validated['detalles'] as $det) {
            DetalleDte::create([
                'documento_dte_id' => $dte->id,
                'cantidad' => $det['cantidad'],
                'descripcion' => $det['descripcion'],
                'precio_unitario' => $det['precio_unitario'],
                'subtotal' => $det['cantidad'] * $det['precio_unitario'],
            ]);
        }

        // Luego de tener el documento persistido, se genera el timbre usando sus datos finales.
        $dte->update([
            'timbre' => $this->generarTimbreElectronico($dte),
        ]);

        // Primer evento del flujo: el documento nace como Borrador.
        HistorialEstadoDte::create([
            'documento_dte_id' => $dte->id,
            'estado_anterior' => 'Creacion',
            'estado_nuevo' => 'Borrador',
            'user_id' => $user->id,
            'comentario' => 'Creacion del documento',
        ]);

        return redirect()->route('dtes.show', $dte->id);
    }

    public function show(DocumentoDte $dte)
    {
        // Valida permisos y propiedad antes de mostrar el documento.
        $this->authorize('ver-dtes');
        $this->asegurarPropiedadDte($dte);

        $dte->load([
            'region',
            'comuna',
            'firma.user',
            'historial' => function ($query) {
                $query->with('user')->orderBy('created_at');
            },
        ]);

        // Si el timbre no existe o es invalido, se regenera al vuelo.
        $this->asegurarTimbreElectronico($dte);

        $tipoDte = TipoDte::where('id', $dte->tipo_dte_id)->first();
        $detallesDte = DetalleDte::where('documento_dte_id', $dte->id)->get();

        return Inertia::render('Dtes/Show', [
            'dte' => $dte,
            'tipoDte' => $tipoDte,
            'detallesDte' => $detallesDte,
        ]);
    }

    public function firmar(Request $request, DocumentoDte $dte)
    {
        // Solo el propietario autorizado puede firmar un documento en borrador.
        $this->authorize('firmar-dte');
        $this->asegurarPropiedadDte($dte);

        $request->validate([
            'tipo_firma' => 'required|in:texto,canvas',
            'firma_texto' => 'nullable|string|required_if:tipo_firma,texto',
            'firma_canvas' => 'nullable|string|required_if:tipo_firma,canvas',
        ]);

        // Se evita aplicar mas de una firma al mismo documento.
        if ($dte->firma) {
            return response()->json(['error' => 'Ya firmado'], 422);
        }

        if ($this->estadoActual($dte) !== 'Borrador') {
            return response()->json(['error' => 'Solo se puede firmar un DTE en estado Borrador'], 422);
        }

        // Se persiste la firma simulada en texto o imagen base64.
        FirmaDte::create([
            'documento_dte_id' => $dte->id,
            'tipo_firma' => $request->tipo_firma,
            'firma_texto' => $request->firma_texto,
            'firma_canvas' => $request->firma_canvas,
            'user_id' => Auth::id(),
        ]);

        $this->cambiarEstado($dte, 'Firmada', 'Firma digital simulada aplicada');

        return redirect()->route('dtes.show', $dte->id);
    }

    public function enviar(DocumentoDte $dte)
    {
        // Simula el envio al SII y guarda la respuesta estructural.
        $this->authorize('enviar-dte');
        $this->asegurarPropiedadDte($dte);

        if (!$dte->firma) {
            return redirect()->route('dtes.show', $dte->id)->with('error', 'Debes firmar el DTE antes de enviarlo al SII.');
        }

        $estadoActual = $this->estadoActual($dte);
        if ($estadoActual !== 'Firmada' && $estadoActual !== 'Rechazada SII') {
            return redirect()->route('dtes.show', $dte->id)->with('error', 'Estado invalido para enviar al SII.');
        }

        $dte->loadMissing(['tipoDte', 'detalles', 'firma', 'user', 'region', 'comuna']);
        $this->asegurarTimbreElectronico($dte);

        // El XML se renderiza desde una vista para poder reutilizarlo despues.
        $xml = view('xml.dte', ['dte' => $dte])->render();

        $dte->update([
            'xml_generado' => $xml,
            'track_id' => 'TRK-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6)),
            'estado_sii' => 'Enviado',
            'fecha_envio_sii' => now(),
            'codigo_respuesta_sii' => null,
            'mensaje_respuesta_sii' => null,
            'fecha_respuesta_sii' => null,
        ]);

        $this->cambiarEstado($dte, 'Enviada SII', 'Envio simulado a SII');

        // Luego del envio se ejecuta una validacion local que simula al SII.
        $resultadoSii = $this->simularValidacionSii($dte);
        $dte->update([
            'estado_sii' => $resultadoSii['aceptado'] ? 'Aceptado' : 'Rechazado',
            'codigo_respuesta_sii' => $resultadoSii['codigo'],
            'mensaje_respuesta_sii' => $resultadoSii['mensaje'],
            'fecha_respuesta_sii' => now(),
        ]);

        if ($resultadoSii['aceptado']) {
            $this->cambiarEstado($dte, 'Aceptada SII', 'SII acepto DTE. Codigo ' . $resultadoSii['codigo']);

            return redirect()->route('dtes.show', $dte->id)->with('success', 'DTE aceptado por SII (simulado).');
        }

        $this->cambiarEstado($dte, 'Rechazada SII', 'SII rechazo DTE. Codigo ' . $resultadoSii['codigo'] . ': ' . $resultadoSii['mensaje']);

        return redirect()->route('dtes.show', $dte->id)->with('error', 'DTE rechazado por SII (simulado): ' . $resultadoSii['mensaje']);
    }

    public function enviarReceptor(DocumentoDte $dte)
    {
        // Simula la entrega del XML y PDF al receptor una vez aceptado por SII.
        $this->authorize('enviar-dte');
        $this->asegurarPropiedadDte($dte);

        $estadoActual = $this->estadoActual($dte);
        if ($estadoActual !== 'Aceptada SII') {
            return redirect()->route('dtes.show', $dte->id)->with('error', 'Solo puedes enviar al receptor un DTE aceptado por SII.');
        }

        $dte->loadMissing(['tipoDte', 'detalles', 'firma', 'user', 'region', 'comuna']);

        // Reutiliza el XML ya generado o lo reconstruye si aun no existe.
        $xml = $dte->xml_generado ?: view('xml.dte', ['dte' => $dte])->render();
        if (!$dte->xml_generado) {
            $dte->update(['xml_generado' => $xml]);
        }

        // El PDF se genera y se guarda para adjuntarlo al correo simulado.
        $pdf = Pdf::loadView('pdf.dte', ['dte' => $dte]);
        $pdfPath = storage_path('app/public/dtes/dte-' . $dte->id . '.pdf');
        if (!file_exists(storage_path('app/public/dtes'))) {
            mkdir(storage_path('app/public/dtes'), 0755, true);
        }
        $pdf->save($pdfPath);

        $dte->update([
            'pdf_path' => $pdfPath,
            'fecha_envio_receptor' => now(),
        ]);

        $correoReceptor = config('mail.recipient_fallback.address', 'receptor@test.cl');
        $urlsAcuse = [
            'aceptado' => URL::temporarySignedRoute('dtes.acuse-receptor.email', now()->addDays(7), [
                'dte' => $dte->id,
                'respuesta' => 'aceptado',
            ]),
            'reparo' => URL::temporarySignedRoute('dtes.acuse-receptor.email', now()->addDays(7), [
                'dte' => $dte->id,
                'respuesta' => 'reparo',
            ]),
            'rechazado' => URL::temporarySignedRoute('dtes.acuse-receptor.email', now()->addDays(7), [
                'dte' => $dte->id,
                'respuesta' => 'rechazado',
            ]),
        ];

        Mail::to($correoReceptor)->send(new DteReceptorMail($dte, $xml, $pdfPath, $urlsAcuse));

        $this->cambiarEstado($dte, 'Enviada Receptor', 'Envio XML/PDF al receptor (simulado)');

        return redirect()->route('dtes.show', $dte->id)->with('success', 'DTE enviado al receptor (simulado).');
    }

    public function registrarAcuseReceptor(Request $request, DocumentoDte $dte)
    {
        // Permite registrar la respuesta comercial del receptor.
        $this->authorize('enviar-dte');
        $this->asegurarPropiedadDte($dte);

        $validated = $request->validate([
            'respuesta' => 'required|in:aceptado,rechazado,reparo',
            'comentario' => 'nullable|string|max:1000',
        ]);

        $comentario = trim((string) ($validated['comentario'] ?? ''));
        $this->registrarAcuseEnDocumento($dte, $validated['respuesta'], $comentario, Auth::id());

        return redirect()->route('dtes.show', $dte->id)->with('success', 'Acuse receptor registrado.');
    }

    public function responderAcuseDesdeCorreo(Request $request, DocumentoDte $dte, string $respuesta)
    {
        abort_unless(in_array($respuesta, ['aceptado', 'reparo', 'rechazado'], true), 404);

        if ($respuesta === 'aceptado') {
            $this->registrarAcuseEnDocumento($dte, $respuesta, '', $dte->user_id);

            return view('acuse.resultado', [
                'titulo' => 'Acuse registrado',
                'mensaje' => 'El documento ' . $dte->folio . ' fue acusado como recibido sin reparos.',
                'estado' => 'Aceptado',
            ]);
        }

        return view('acuse.formulario', [
            'dte' => $dte,
            'respuesta' => $respuesta,
            'titulo' => $respuesta === 'reparo' ? 'Acusar con reparos' : 'Rechazar documento',
            'accion' => URL::signedRoute('dtes.acuse-receptor.email.store', [
                'dte' => $dte->id,
                'respuesta' => $respuesta,
            ]),
        ]);
    }

    public function guardarAcuseDesdeCorreo(Request $request, DocumentoDte $dte, string $respuesta)
    {
        abort_unless(in_array($respuesta, ['reparo', 'rechazado'], true), 404);

        $validated = $request->validate([
            'comentario' => 'required|string|max:1000',
        ]);

        $comentario = trim((string) $validated['comentario']);
        $this->registrarAcuseEnDocumento($dte, $respuesta, $comentario, $dte->user_id);

        return view('acuse.resultado', [
            'titulo' => 'Respuesta registrada',
            'mensaje' => 'La respuesta para el documento ' . $dte->folio . ' fue registrada correctamente.',
            'estado' => $respuesta === 'reparo' ? 'Con reparos' : 'Rechazado',
        ]);
    }

    public function anular(Request $request, DocumentoDte $dte)
    {
        // Registra una anulacion con comentario obligatorio para trazabilidad.
        $this->authorize('anular-dte');
        $this->asegurarPropiedadDte($dte);

        $request->validate(['comentario' => 'required|string']);

        $estadoActual = $this->estadoActual($dte);
        if ($estadoActual === 'Anulada') {
            return redirect()->route('dtes.show', $dte->id)->with('error', 'El DTE ya esta anulado.');
        }

        $this->cambiarEstado($dte, 'Anulada', $request->comentario);

        return redirect()->route('dtes.show', $dte->id);
    }

    public function descargarPdf(DocumentoDte $dte)
    {
        // Descarga una representacion PDF del documento.
        $this->authorize('ver-dtes');
        $this->asegurarPropiedadDte($dte);

        $dte->load(['tipoDte', 'detalles', 'firma', 'user', 'region', 'comuna']);
        $this->asegurarTimbreElectronico($dte);

        $pdf = Pdf::loadView('pdf.dte', ['dte' => $dte]);

        return $pdf->download('DTE-' . $dte->folio . '.pdf');
    }

    public function descargarXml(DocumentoDte $dte)
    {
        // Descarga el XML estructurado del DTE.
        $this->authorize('ver-dtes');
        $this->asegurarPropiedadDte($dte);

        $dte->load(['tipoDte', 'detalles', 'firma', 'user', 'region', 'comuna']);
        $this->asegurarTimbreElectronico($dte);

        $xml = $dte->xml_generado ?: view('xml.dte', ['dte' => $dte])->render();

        if (!$dte->xml_generado) {
            $dte->update(['xml_generado' => $xml]);
        }

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="DTE-' . $dte->folio . '.xml"',
        ]);
    }

    private function asegurarTimbreElectronico(DocumentoDte $dte): void
    {
        // Si el timbre guardado no es una imagen embebida valida, se genera nuevamente.
        if (!is_string($dte->timbre) || !str_starts_with($dte->timbre, 'data:image/')) {
            $dte->timbre = $this->generarTimbreElectronico($dte);
            $dte->save();
        }
    }

    private function simularValidacionSii(DocumentoDte $dte): array
    {
        // Reglas minimas de validacion para simular aceptacion o rechazo del SII.
        $errores = [];

        if (!$this->esRutChilenoValido((string) $dte->rut_emisor)) {
            $errores[] = 'RUT emisor invalido';
        }

        if (!$this->esRutChilenoValido((string) $dte->rut_receptor)) {
            $errores[] = 'RUT receptor invalido';
        }

        $netoCalculado = (float) $dte->detalles->sum(function ($det) {
            return (float) $det->cantidad * (float) $det->precio_unitario;
        });

        $ivaCalculado = round($netoCalculado * 0.19, 2);
        $totalCalculado = round($netoCalculado + $ivaCalculado, 2);

        if (abs($netoCalculado - (float) $dte->monto_neto) > 0.01) {
            $errores[] = 'Monto neto descuadrado';
        }

        if (abs($ivaCalculado - (float) $dte->iva) > 0.01) {
            $errores[] = 'IVA descuadrado';
        }

        if (abs($totalCalculado - (float) $dte->monto_total) > 0.01) {
            $errores[] = 'Monto total descuadrado';
        }

        $fechaAnexo25 = now()->startOfDay()->greaterThanOrEqualTo(\Carbon\Carbon::create(2026, 5, 1, 0, 0, 0));
        if (($dte->tipoDte?->codigo === '52') && $fechaAnexo25) {
            if (empty($dte->direccion_destino)) {
                $errores[] = 'Direccion de destino obligatoria para Guia';
            }

            if (empty($dte->transporte_patente)) {
                $errores[] = 'Patente obligatoria para Guia';
            }

            if (empty($dte->transporte_rut_chofer)) {
                $errores[] = 'RUT chofer obligatorio para Guia';
            } elseif (!$this->esRutChilenoValido((string) $dte->transporte_rut_chofer)) {
                $errores[] = 'RUT chofer invalido';
            }

            if (empty($dte->transporte_nombre_chofer)) {
                $errores[] = 'Nombre chofer obligatorio para Guia';
            }
        }

        if (!empty($errores)) {
            return [
                'aceptado' => false,
                'codigo' => 'RCH-90',
                'mensaje' => implode('; ', $errores),
            ];
        }

        return [
            'aceptado' => true,
            'codigo' => '0',
            'mensaje' => 'DTE aceptado por validacion estructural y comercial',
        ];
    }

    private function generarTimbreElectronico(DocumentoDte $dte): string
    {
        // Genera un SVG pseudoaleatorio y deterministico a partir de datos del documento.
        $seedSource = implode('|', [
            (string) $dte->id,
            (string) $dte->folio,
            (string) $dte->rut_emisor,
            (string) $dte->rut_receptor,
            (string) round((float) $dte->monto_total),
            (string) $dte->fecha_emision,
            (string) $dte->created_at,
        ]);

        $seed = hash('sha256', $seedSource);
        $rows = 58;
        $cols = 260;
        $module = 2;
        $leftMargin = 20;
        $topMargin = 14;
        $rightMargin = 20;
        $captionHeight = 40;

        $barcodeWidth = $cols * $module;
        $barcodeHeight = $rows * $module;
        $width = $leftMargin + $barcodeWidth + $rightMargin;
        $height = $topMargin + $barcodeHeight + $captionHeight;

        $bits = '';
        $counter = 0;
        $needed = $rows * $cols;
        while (strlen($bits) < $needed) {
            $hashBytes = hash('sha256', $seed . '|' . $counter, true);
            for ($i = 0; $i < strlen($hashBytes); $i++) {
                $bits .= str_pad(decbin(ord($hashBytes[$i])), 8, '0', STR_PAD_LEFT);
            }
            $counter++;
        }

        $svg = [];
        $svg[] = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '" viewBox="0 0 ' . $width . ' ' . $height . '">';
        $svg[] = '<rect width="100%" height="100%" fill="#ffffff"/>';

        $xStart = $leftMargin;
        $yStart = $topMargin;

        for ($i = 0; $i < 8; $i++) {
            $barXLeft = $xStart - 16 + ($i * 2);
            $barXRight = $xStart + $barcodeWidth + 2 + ($i * 2);
            $barWidth = ($i % 2 === 0) ? 2 : 1;
            $svg[] = '<rect x="' . $barXLeft . '" y="' . $yStart . '" width="' . $barWidth . '" height="' . $barcodeHeight . '" fill="#000"/>';
            $svg[] = '<rect x="' . $barXRight . '" y="' . $yStart . '" width="' . $barWidth . '" height="' . $barcodeHeight . '" fill="#000"/>';
        }

        for ($row = 0; $row < $rows; $row++) {
            $base = $row * $cols;
            $col = 0;
            while ($col < $cols) {
                $idx = $base + $col;
                if ($bits[$idx] !== '1') {
                    $col++;
                    continue;
                }

                $run = 1;
                while (($col + $run) < $cols && $bits[$base + $col + $run] === '1') {
                    $run++;
                }

                $x = $xStart + ($col * $module);
                $y = $yStart + ($row * $module);
                $w = $run * $module;
                $svg[] = '<rect x="' . $x . '" y="' . $y . '" width="' . $w . '" height="' . $module . '" fill="#000"/>';

                $col += $run;
            }
        }

        $svg[] = '<text x="' . ($width / 2) . '" y="' . ($height - 12) . '" text-anchor="middle" font-family="Arial, sans-serif" font-size="12" fill="#111">Timbre Electronico SII (Simulado)</text>';
        $svg[] = '</svg>';

        return 'data:image/svg+xml;base64,' . base64_encode(implode('', $svg));
    }

    private function asegurarPropiedadDte(DocumentoDte $dte): void
    {
        // Protege contra acceso a documentos de otros usuarios.
        if ((int) $dte->user_id !== (int) Auth::id()) {
            abort(403, 'No autorizado para operar este DTE.');
        }
    }

    private function generarFolioInterno(DocumentoDte $dte): string
    {
        return 'Folio-' . $dte->id;
    }

    private function normalizarRutChileno(?string $rut): string
    {
        $rutLimpio = preg_replace('/[^0-9kK]/', '', (string) $rut) ?? '';
        $cuerpo = substr($rutLimpio, 0, -1);
        $dv = strtoupper(substr($rutLimpio, -1));

        return ltrim($cuerpo, '0') . '-' . $dv;
    }

    private function esRutChilenoValido(?string $rut): bool
    {
        $rutLimpio = preg_replace('/[^0-9kK]/', '', (string) $rut) ?? '';

        if (strlen($rutLimpio) < 2) {
            return false;
        }

        $cuerpo = substr($rutLimpio, 0, -1);
        $dvIngresado = strtoupper(substr($rutLimpio, -1));

        if ($cuerpo === '' || !ctype_digit($cuerpo)) {
            return false;
        }

        $suma = 0;
        $multiplicador = 2;

        foreach (array_reverse(str_split($cuerpo)) as $digito) {
            $suma += ((int) $digito) * $multiplicador;
            $multiplicador = $multiplicador === 7 ? 2 : $multiplicador + 1;
        }

        $resto = 11 - ($suma % 11);
        $dvEsperado = match ($resto) {
            11 => '0',
            10 => 'K',
            default => (string) $resto,
        };

        return $dvIngresado === $dvEsperado;
    }

    private function estadoActual(DocumentoDte $dte): string
    {
        // El estado actual siempre corresponde al ultimo movimiento del historial.
        return HistorialEstadoDte::query()
            ->where('documento_dte_id', $dte->id)
            ->latest('id')
            ->value('estado_nuevo') ?? 'Borrador';
    }

    private function registrarAcuseEnDocumento(DocumentoDte $dte, string $respuesta, string $comentario = '', ?int $actorId = null): void
    {
        if (empty($dte->fecha_envio_receptor)) {
            throw ValidationException::withMessages([
                'respuesta' => 'Primero debes enviar el DTE al receptor.',
            ]);
        }

        $estadoActual = $this->estadoActual($dte);
        if ($estadoActual === 'Anulada') {
            throw ValidationException::withMessages([
                'respuesta' => 'No puedes registrar acuso en un DTE anulado.',
            ]);
        }

        $mapa = [
            'aceptado' => ['estado' => 'Documento Aceptado', 'etiqueta' => 'Aceptado'],
            'rechazado' => ['estado' => 'Documento Rechazado', 'etiqueta' => 'Rechazado'],
            'reparo' => ['estado' => 'Documento con Reparos', 'etiqueta' => 'Con reparos'],
        ];

        $seleccion = $mapa[$respuesta] ?? null;
        if (!$seleccion) {
            throw ValidationException::withMessages([
                'respuesta' => 'La respuesta del receptor no es valida.',
            ]);
        }

        if ($respuesta !== 'aceptado' && $comentario === '') {
            throw ValidationException::withMessages([
                'comentario' => 'Debes registrar comentario cuando el acuse es rechazado o con reparos.',
            ]);
        }

        $dte->update([
            'estado_receptor' => $seleccion['etiqueta'],
            'fecha_respuesta_receptor' => now(),
            'observacion_receptor' => $comentario !== '' ? $comentario : null,
        ]);

        $this->cambiarEstado($dte, $seleccion['estado'], $comentario !== '' ? $comentario : 'Acuse receptor registrado', $actorId);
    }

    private function cambiarEstado(DocumentoDte $dte, string $nuevoEstado, ?string $comentario = null, ?int $actorId = null): void
    {
        // Inserta una nueva transicion de estado sin sobrescribir el historial previo.
        HistorialEstadoDte::create([
            'documento_dte_id' => $dte->id,
            'estado_anterior' => $this->estadoActual($dte),
            'estado_nuevo' => $nuevoEstado,
            'user_id' => $actorId ?? Auth::id() ?? $dte->user_id,
            'comentario' => $comentario,
        ]);
    }

    public function reporteGeneral(Request $request)
    {
        // Genera un PDF consolidado para seguimiento operativo.
        $this->authorize('ver-reportes');

        $query = DocumentoDte::with(['tipoDte', 'user', 'firma', 'historial']);

        if ($request->estado) {
            $query->whereHas('historial', function ($q) use ($request) {
                $q->where('estado_nuevo', $request->estado);
            });
        }

        if ($request->desde && $request->hasta) {
            $query->whereBetween('fecha_emision', [$request->desde, $request->hasta]);
        }

        $dtes = $query->orderBy('fecha_emision', 'desc')->get();

        $pdf = Pdf::loadView('pdf.reporte-general', [
            'dtes' => $dtes,
            'total_neto' => $dtes->sum('monto_neto'),
            'total_iva' => $dtes->sum('iva'),
            'total_general' => $dtes->sum('monto_total'),
            'fecha_generacion' => now()->format('d/m/Y H:i'),
        ]);

        return $pdf->download('reporte-general-dtes-' . now()->format('Ymd-His') . '.pdf');
    }
    public function exportExcel(Request $request)
    {
        // Exporta un CSV UTF-8 compatible con Excel y otras herramientas.
        $this->authorize('ver-reportes');

        $query = DocumentoDte::with(['tipoDte', 'user', 'historial']);

        if (!(Auth::user()?->can('gestionar-usuarios') ?? false)) {
            $query->where('user_id', Auth::id());
        }

        if ($request->filled('estado')) {
            $query->whereHas('historial', function ($q) use ($request) {
                $q->where('estado_nuevo', $request->estado);
            });
        }

        if ($request->filled('desde') && $request->filled('hasta')) {
            $query->whereBetween('fecha_emision', [$request->desde, $request->hasta]);
        }

        $dtes = $query->orderBy('fecha_emision', 'desc')->get();

        $headers = [
            'Folio',
            'Tipo DTE',
            'Fecha Emision',
            'Emisor',
            'RUT Receptor',
            'Razon Social Receptor',
            'Monto Neto',
            'IVA',
            'Monto Total',
            'Estado Actual',
            'Estado SII',
            'Estado Receptor',
            'Track ID',
        ];

        $filename = 'reporte-dtes-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($dtes, $headers) {
            $output = fopen('php://output', 'w');
            fwrite($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($output, $headers, ';');

            foreach ($dtes as $dte) {
                $estadoActual = $dte->historial->sortBy('created_at')->last()?->estado_nuevo ?? 'Borrador';

                fputcsv($output, [
                    $dte->folio,
                    $dte->tipoDte?->nombre,
                    optional($dte->fecha_emision)->format('Y-m-d'),
                    $dte->razon_social_emisor,
                    $dte->rut_receptor,
                    $dte->razon_social_receptor,
                    number_format((float) $dte->monto_neto, 2, '.', ''),
                    number_format((float) $dte->iva, 2, '.', ''),
                    number_format((float) $dte->monto_total, 2, '.', ''),
                    $estadoActual,
                    $dte->estado_sii,
                    $dte->estado_receptor,
                    $dte->track_id,
                ], ';');
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
