<?php

namespace App\Http\Controllers;

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
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class DteController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->string('search')->toString(),
            'tipo' => $request->string('tipo')->toString(),
            'estado' => $request->string('estado')->toString(),
        ];

        $query = DocumentoDte::with([
            'tipoDte',
            'historial' => function ($historial) {
                $historial->orderBy('created_at');
            },
        ])->where('user_id', Auth::id());

        if (!empty($filters['search'])) {
            $query->where(function ($searchQuery) use ($filters) {
                $searchQuery
                    ->where('folio', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('razon_social_receptor', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('rut_receptor', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['tipo'])) {
            $query->where('tipo_dte_id', $filters['tipo']);
        }

        $dtes = $query
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($dte) {
                $dte->estado_actual = $dte->historial->last()?->estado_nuevo ?? 'Borrador';
                return $dte;
            });

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
                'Acuse Aceptado',
                'Acuse Rechazado',
                'Acuse con Reparos',
                'Anulada',
            ],
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        return Inertia::render('Dtes/Create', [
            'tipos' => TipoDte::all(),
            'regiones' => Region::query()->orderBy('nombre')->get(['id', 'codigo', 'nombre']),
            'comunas' => Comuna::query()->orderBy('nombre')->get(['id', 'region_id', 'nombre']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo_dte_id' => 'required|exists:tipos_dte,id',
            'rut_receptor' => 'required|string|max:12',
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
            'transporte_rut_chofer' => 'nullable|string|max:12',
            'transporte_nombre_chofer' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $tipo = TipoDte::findOrFail($validated['tipo_dte_id']);

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

        $montoNeto = 0;
        foreach ($validated['detalles'] as $det) {
            $montoNeto += $det['cantidad'] * $det['precio_unitario'];
        }
        $iva = $montoNeto * 0.19;
        $total = $montoNeto + $iva;

        $dte = DocumentoDte::create([
            'tipo_dte_id' => $tipo->id,
            'user_id' => $user->id,
            'folio' => 'F' . Str::random(8) . '-' . time(),
            'fecha_emision' => now(),
            'rut_emisor' => $user->rut ?? '12345678-9',
            'razon_social_emisor' => 'DTEsys SPA',
            'rut_receptor' => $validated['rut_receptor'],
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
            'estado_receptor' => 'Sin acuse',
            'direccion_destino' => $validated['direccion_destino'] ?? null,
            'transporte_patente' => $validated['transporte_patente'] ?? null,
            'transporte_rut_chofer' => $validated['transporte_rut_chofer'] ?? null,
            'transporte_nombre_chofer' => $validated['transporte_nombre_chofer'] ?? null,
        ]);

        foreach ($validated['detalles'] as $det) {
            DetalleDte::create([
                'documento_dte_id' => $dte->id,
                'cantidad' => $det['cantidad'],
                'descripcion' => $det['descripcion'],
                'precio_unitario' => $det['precio_unitario'],
                'subtotal' => $det['cantidad'] * $det['precio_unitario'],
            ]);
        }

        $dte->update([
            'timbre' => $this->generarTimbreElectronico($dte),
        ]);

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
        $this->authorize('ver-dtes');

        $dte->load([
            'region',
            'comuna',
            'firma.user',
            'historial' => function ($query) {
                $query->with('user')->orderBy('created_at');
            },
        ]);

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
        $this->authorize('firmar-dte');

        $request->validate([
            'tipo_firma' => 'required|in:texto,canvas',
            'firma_texto' => 'nullable|string|required_if:tipo_firma,texto',
            'firma_canvas' => 'nullable|string|required_if:tipo_firma,canvas',
        ]);

        if ($dte->firma) {
            return response()->json(['error' => 'Ya firmado'], 422);
        }

        if ($this->estadoActual($dte) !== 'Borrador') {
            return response()->json(['error' => 'Solo se puede firmar un DTE en estado Borrador'], 422);
        }

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
        $this->authorize('enviar-dte');

        if (!$dte->firma) {
            return redirect()->route('dtes.show', $dte->id)->with('error', 'Debes firmar el DTE antes de enviarlo al SII.');
        }

        $estadoActual = $this->estadoActual($dte);
        if ($estadoActual !== 'Firmada' && $estadoActual !== 'Rechazada SII') {
            return redirect()->route('dtes.show', $dte->id)->with('error', 'Estado invalido para enviar al SII.');
        }

        $dte->loadMissing(['tipoDte', 'detalles', 'firma', 'user', 'region', 'comuna']);
        $this->asegurarTimbreElectronico($dte);

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
        $this->authorize('enviar-dte');

        $estadoActual = $this->estadoActual($dte);
        if ($estadoActual !== 'Aceptada SII') {
            return redirect()->route('dtes.show', $dte->id)->with('error', 'Solo puedes enviar al receptor un DTE aceptado por SII.');
        }

        $dte->loadMissing(['tipoDte', 'detalles', 'firma', 'user', 'region', 'comuna']);

        $xml = $dte->xml_generado ?: view('xml.dte', ['dte' => $dte])->render();
        if (!$dte->xml_generado) {
            $dte->update(['xml_generado' => $xml]);
        }

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

        Mail::raw('Simulacion: DTE #' . $dte->folio . ' aceptado por SII y enviado a receptor.', function ($message) use ($dte, $xml, $pdfPath) {
            $message->to('receptor@test.cl')->subject('DTE Simulado ' . $dte->folio);
            $message->attachData($xml, 'DTE-' . $dte->folio . '.xml', ['mime' => 'application/xml']);
            $message->attach($pdfPath, ['as' => 'DTE-' . $dte->folio . '.pdf', 'mime' => 'application/pdf']);
        });

        $this->cambiarEstado($dte, 'Enviada Receptor', 'Envio XML/PDF al receptor (simulado)');

        return redirect()->route('dtes.show', $dte->id)->with('success', 'DTE enviado al receptor (simulado).');
    }

    public function registrarAcuseReceptor(Request $request, DocumentoDte $dte)
    {
        $this->authorize('enviar-dte');

        $validated = $request->validate([
            'respuesta' => 'required|in:aceptado,rechazado,reparo',
            'comentario' => 'nullable|string|max:1000',
        ]);

        if (empty($dte->fecha_envio_receptor)) {
            return redirect()->route('dtes.show', $dte->id)->with('error', 'Primero debes enviar el DTE al receptor.');
        }

        $estadoActual = $this->estadoActual($dte);
        if ($estadoActual === 'Anulada') {
            return redirect()->route('dtes.show', $dte->id)->with('error', 'No puedes registrar acuse en un DTE anulado.');
        }

        $mapa = [
            'aceptado' => ['estado' => 'Acuse Aceptado', 'etiqueta' => 'Aceptado'],
            'rechazado' => ['estado' => 'Acuse Rechazado', 'etiqueta' => 'Rechazado'],
            'reparo' => ['estado' => 'Acuse con Reparos', 'etiqueta' => 'Con reparos'],
        ];

        $seleccion = $mapa[$validated['respuesta']];

        if ($validated['respuesta'] !== 'aceptado' && empty(trim((string) ($validated['comentario'] ?? '')))) {
            throw ValidationException::withMessages([
                'comentario' => 'Debes registrar comentario cuando el acuse es rechazado o con reparos.',
            ]);
        }

        $comentario = trim((string) ($validated['comentario'] ?? ''));

        $dte->update([
            'estado_receptor' => $seleccion['etiqueta'],
            'fecha_respuesta_receptor' => now(),
            'observacion_receptor' => $comentario !== '' ? $comentario : null,
        ]);

        $this->cambiarEstado($dte, $seleccion['estado'], $comentario !== '' ? $comentario : 'Acuse receptor registrado');

        return redirect()->route('dtes.show', $dte->id)->with('success', 'Acuse receptor registrado.');
    }

    public function anular(Request $request, DocumentoDte $dte)
    {
        $this->authorize('anular-dte');

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
        $this->authorize('ver-dtes');

        $dte->load(['tipoDte', 'detalles', 'firma', 'user', 'region', 'comuna']);
        $this->asegurarTimbreElectronico($dte);

        $pdf = Pdf::loadView('pdf.dte', ['dte' => $dte]);

        return $pdf->download('DTE-' . $dte->folio . '.pdf');
    }

    public function descargarXml(DocumentoDte $dte)
    {
        $this->authorize('ver-dtes');

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
        if (!is_string($dte->timbre) || !str_starts_with($dte->timbre, 'data:image/')) {
            $dte->timbre = $this->generarTimbreElectronico($dte);
            $dte->save();
        }
    }

    private function simularValidacionSii(DocumentoDte $dte): array
    {
        $errores = [];

        if (!preg_match('/^[0-9kK.\-]+$/', (string) $dte->rut_emisor)) {
            $errores[] = 'RUT emisor invalido';
        }

        if (!preg_match('/^[0-9kK.\-]+$/', (string) $dte->rut_receptor)) {
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

    private function estadoActual(DocumentoDte $dte): string
    {
        return HistorialEstadoDte::query()
            ->where('documento_dte_id', $dte->id)
            ->latest('id')
            ->value('estado_nuevo') ?? 'Borrador';
    }

    private function cambiarEstado(DocumentoDte $dte, string $nuevoEstado, ?string $comentario = null): void
    {
        HistorialEstadoDte::create([
            'documento_dte_id' => $dte->id,
            'estado_anterior' => $this->estadoActual($dte),
            'estado_nuevo' => $nuevoEstado,
            'user_id' => Auth::id(),
            'comentario' => $comentario,
        ]);
    }

    public function reporteGeneral(Request $request)
    {
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
}
