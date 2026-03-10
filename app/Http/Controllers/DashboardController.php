<?php

namespace App\Http\Controllers;

use App\Models\DocumentoDte;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $canViewGlobal = $user?->can('gestionar-usuarios') ?? false;

        $baseQuery = DocumentoDte::query();
        if (!$canViewGlobal) {
            $baseQuery->where('user_id', Auth::id());
        }

        $stats = [
            'total_dtes' => (clone $baseQuery)->count(),
            'monto_total' => (clone $baseQuery)->sum('monto_total'),
            'firmados' => (clone $baseQuery)->whereHas('firma')->count(),
            'usuarios' => $canViewGlobal ? User::count() : 1,
        ];

        $ultimosDtes = (clone $baseQuery)
            ->with(['tipoDte', 'historial'])
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
