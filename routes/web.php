<?php

use App\Http\Controllers\DteController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    Route::middleware('can:ver-reportes')->get('/reportes/general', [App\Http\Controllers\DteController::class, 'reporteGeneral'])->name('reportes.general');

    Route::middleware('can:crear-dte')->group(function () {
        Route::get('/dtes/create', [DteController::class, 'create'])->name('dtes.create');
        Route::post('/dtes', [DteController::class, 'store'])->name('dtes.store');
    });

    // Rutas DTE protegidas con permisos
    Route::middleware('can:ver-dtes')->group(function () {
        Route::get('/dtes', [DteController::class, 'index'])->name('dtes.index');
        Route::get('/dtes/{id}', [DteController::class, 'show'])->name('dtes.show');
    });

    Route::middleware('can:firmar-dte')->group(function () {
        Route::post('/dtes/{id}/firmar', [DteController::class, 'firmar'])->name('dtes.firmar');
    });

    Route::middleware('can:enviar-dte')->group(function () {
        Route::post('/dtes/{id}/enviar', [DteController::class, 'enviar'])->name('dtes.enviar');
    });

    Route::middleware('can:anular-dte')->group(function () {
        Route::post('/dtes/{id}/anular', [DteController::class, 'anular'])->name('dtes.anular');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/usuarios', [App\Http\Controllers\UserController::class, 'index'])->name('usuarios.index');
    Route::post('/usuarios/{user}/asignar-rol', [App\Http\Controllers\UserController::class, 'asignarRol'])->name('usuarios.asignar-rol');
    Route::post('/usuarios/{user}/quitar-rol', [App\Http\Controllers\UserController::class, 'quitarRol'])->name('usuarios.quitar-rol');
});


require __DIR__.'/auth.php';
