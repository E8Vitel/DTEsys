<?php

namespace App\Http\Controllers;

use App\Models\DetalleDte;
use App\Models\DocumentoDte;
use App\Models\FirmaDte;
use App\Models\TipoDte;
use App\Models\HistorialEstadoDte;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;

class DashboardController extends Controller
{
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
}
