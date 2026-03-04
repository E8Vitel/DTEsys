<?php

namespace App\Http\Controllers;

use App\Models\DetalleDte;
use App\Models\DocumentoDte;
use App\Models\FirmaDte;
use App\Models\TipoDte;
use App\Models\HistorialEstadoDte;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // ← este es el trait clave
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate; // ← fallback por si acaso
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;

/**
 * Controlador principal para gestión de DTE
 */
class DteController extends Controller
{
    use AuthorizesRequests; // ← agrégalo aquí, es obligatorio

    public function index()
    {
        $dtes = DocumentoDte::with(['tipoDte', 'user', 'firma', 'historial.user'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        // Estadísticas para dashboard
        $stats = [
            'total_dtes' => DocumentoDte::count(),
            'monto_total' => DocumentoDte::sum('monto_total'),
            'firmados' => DocumentoDte::whereHas('firma')->count(),
            'usuarios' => \App\Models\User::count(),
        ];

        $ultimosDtes = DocumentoDte::with('tipoDte')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($dte) {
                $dte->estado_actual = $dte->historial->last()?->estado_nuevo ?? 'Borrador';
                return $dte;
            });

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'ultimosDtes' => $ultimosDtes,
        ]);
    }

    public function create()
    {
        $tipos = \App\Models\TipoDte::all();

        return Inertia::render('Dtes/Create', [
            'tipos' => $tipos,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo_dte_id' => 'required|exists:tipos_dte,id',
            'rut_receptor' => 'required|string|max:12',
            'razon_social_receptor' => 'required|string|max:255',
            'detalles' => 'required|array|min:1',
            'detalles.*.cantidad' => 'required|numeric|min:1',
            'detalles.*.descripcion' => 'required|string|max:255',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();
        $tipo = \App\Models\TipoDte::find($validated['tipo_dte_id']);

        $monto_neto = 0;
        foreach ($validated['detalles'] as $det) {
            $monto_neto += $det['cantidad'] * $det['precio_unitario'];
        }
        $iva = $monto_neto * 0.19;
        $total = $monto_neto + $iva;

        $dte = DocumentoDte::create([
            'tipo_dte_id' => $tipo->id,
            'user_id' => $user->id,
            'folio' => 'F' . Str::random(8) . '-' . time(),
            'fecha_emision' => now(),
            'rut_emisor' => $user->rut ?? '12345678-9',
            'razon_social_emisor' => 'DTEsys SPA',
            'rut_receptor' => $validated['rut_receptor'],
            'razon_social_receptor' => $validated['razon_social_receptor'],
            'monto_neto' => $monto_neto,
            'iva' => $iva,
            'monto_total' => $total,
            'timbre' => 'TED-SIM-' . Str::random(12),
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

        HistorialEstadoDte::create([
            'documento_dte_id' => $dte->id,
            'estado_anterior' => null,
            'estado_nuevo' => 'Borrador',
            'user_id' => $user->id,
            'comentario' => 'Creación del documento',
        ]);

        return redirect()->route('dtes.show', $dte->id);
    }

    public function show($dte)
    {
        $dte = DocumentoDte::where('id', $dte)->firstOrFail();
        $tipoDte = TipoDte::where('id', $dte->tipo_dte_id)->first(); // Carga el tipo de DTE
        $detallesDte = DetalleDte::where('documento_dte_id', $dte->id)->get();

        $this->authorize('ver-dtes');

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
            'firma_texto' => 'required_if:tipo_firma,texto|string',
            'firma_canvas' => 'required_if:tipo_firma,canvas|string',
        ]);

        if ($dte->firma) {
            return response()->json(['error' => 'Ya firmado'], 422);
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

        $pdf = Pdf::loadView('pdf.dte', ['dte' => $dte->load(['detalles', 'firma', 'user'])]);
        $pdfPath = storage_path('app/public/dtes/dte-' . $dte->id . '.pdf');
        if (!file_exists(storage_path('app/public/dtes'))) {
            mkdir(storage_path('app/public/dtes'), 0755, true);
        }
        $pdf->save($pdfPath);

        $dte->update(['pdf_path' => $pdfPath]);

        Mail::raw('Simulación: DTE #' . $dte->folio . ' enviado a receptor.', function ($message) use ($dte) {
            $message->to('receptor@test.cl')->subject('DTE Simulado');
        });

        $this->cambiarEstado($dte, 'Enviada', 'Envío simulado por correo');

        return redirect()->route('dtes.show', $dte->id)->with('success', 'Enviado simulado');
    }

    public function anular(Request $request, DocumentoDte $dte)
    {
        $this->authorize('anular-dte');

        $request->validate(['comentario' => 'required|string']);

        $this->cambiarEstado($dte, 'Anulada', $request->comentario);

        return redirect()->route('dtes.show', $dte->id);
    }

    private function cambiarEstado(DocumentoDte $dte, $nuevoEstado, $comentario)
    {
        HistorialEstadoDte::create([
            'documento_dte_id' => $dte->id,
            'estado_anterior' => $dte->historial->last()?->estado_nuevo ?? 'Borrador',
            'estado_nuevo' => $nuevoEstado,
            'user_id' => Auth::id(),
            'comentario' => $comentario,
        ]);
    }

    public function reporteGeneral(Request $request)
    {
        $this->authorize('ver-reportes');

        $query = DocumentoDte::with(['tipoDte', 'user', 'firma', 'historial']);

        // Filtros simples (opcional)
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