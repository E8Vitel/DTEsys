<?php

use App\Http\Controllers\DteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (auth()->check()) {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    } else {
        return redirect()->route('login');
    }
});

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Usuarios con permisos para crear DTEs
    Route::middleware('can:crear-dte')->group(function () {
        Route::get('/dtes/create', [DteController::class, 'create'])->name('dtes.create');
        Route::post('/dtes', [DteController::class, 'store'])->name('dtes.store');
    });

    // Usuarios con permisos para ver DTEs
    Route::middleware('can:ver-dtes')->group(function () {
        Route::get('/dtes', [DteController::class, 'index'])->name('dtes.index');
        Route::get('/dtes/{id}', [DteController::class, 'show'])->name('dtes.show');
    });

    // Usuarios con permisos para firmar DTEs
    Route::middleware('can:firmar-dte')->group(function () {
        Route::post('/dtes/{id}/firmar', [DteController::class, 'firmar'])->name('dtes.firmar');
    });

    // Usuarios con permisos para enviar DTEs
    Route::middleware('can:enviar-dte')->group(function () {
        Route::post('/dtes/{id}/enviar', [DteController::class, 'enviar'])->name('dtes.enviar');
    });

    // Usuarios con permisos para anular DTEs
    Route::middleware('can:anular-dte')->group(function () {
        Route::post('/dtes/{id}/anular', [DteController::class, 'anular'])->name('dtes.anular');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Reportes
    Route::middleware('can:ver-reportes')->get('/reportes/general', [App\Http\Controllers\DteController::class, 'reporteGeneral'])->name('reportes.general');
    Route::middleware('can:ver-reportes')->get('/reportes/export-excel', [DteController::class, 'exportExcel'])->name('reportes.export-excel');

});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/usuarios', [App\Http\Controllers\UserController::class, 'index'])->name('usuarios.index');
    Route::post('/usuarios/{user}/asignar-rol', [App\Http\Controllers\UserController::class, 'asignarRol'])->name('usuarios.asignar-rol');
    Route::post('/usuarios/{user}/quitar-rol', [App\Http\Controllers\UserController::class, 'quitarRol'])->name('usuarios.quitar-rol');
});


require __DIR__.'/auth.php';
