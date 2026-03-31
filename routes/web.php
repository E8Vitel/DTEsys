<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DteController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Punto de entrada principal del sistema.
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

// Respuesta del receptor desde correo firmado, sin autenticacion.
Route::middleware('signed')->group(function () {
    Route::get('/acuse-receptor/email/{dte}/{respuesta}', [DteController::class, 'responderAcuseDesdeCorreo'])->name('dtes.acuse-receptor.email');
    Route::post('/acuse-receptor/email/{dte}/{respuesta}', [DteController::class, 'guardarAcuseDesdeCorreo'])->name('dtes.acuse-receptor.email.store');
});

// Rutas disponibles para cualquier usuario autenticado.
Route::middleware('auth')->group(function () {

    // Dashboard con metricas y actividad reciente.
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Creacion de DTE.
    Route::middleware('can:crear-dte')->group(function () {
        Route::get('/dtes/create', [DteController::class, 'create'])->name('dtes.create');
        Route::post('/dtes', [DteController::class, 'store'])->name('dtes.store');
    });

    // Consulta, detalle y descargas de DTE.
    Route::middleware('can:ver-dtes')->group(function () {
        Route::get('/dtes', [DteController::class, 'index'])->name('dtes.index');
        Route::get('/dtes/{dte}', [DteController::class, 'show'])->name('dtes.show');
        Route::get('/dtes/{dte}/download-pdf', [DteController::class, 'descargarPdf'])->name('dtes.download-pdf');
        Route::get('/dtes/{dte}/download-xml', [DteController::class, 'descargarXml'])->name('dtes.download-xml');
    });

    // Firma del documento.
    Route::middleware('can:firmar-dte')->group(function () {
        Route::post('/dtes/{dte}/firmar', [DteController::class, 'firmar'])->name('dtes.firmar');
    });

    // Flujo posterior a la firma: envio y acuse.
    Route::middleware('can:enviar-dte')->group(function () {
        Route::post('/dtes/{dte}/enviar', [DteController::class, 'enviar'])->name('dtes.enviar');
        Route::post('/dtes/{dte}/enviar-sii', [DteController::class, 'enviar'])->name('dtes.enviar-sii');
        Route::post('/dtes/{dte}/enviar-receptor', [DteController::class, 'enviarReceptor'])->name('dtes.enviar-receptor');
        Route::post('/dtes/{dte}/acuse-receptor', [DteController::class, 'registrarAcuseReceptor'])->name('dtes.acuse-receptor');
    });

    // Anulacion de documentos.
    Route::middleware('can:anular-dte')->group(function () {
        Route::post('/dtes/{dte}/anular', [DteController::class, 'anular'])->name('dtes.anular');
    });

    // Perfil del usuario autenticado.
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Reportes consolidados del sistema.
    Route::middleware('can:ver-reportes')->get('/reportes/general', [DteController::class, 'reporteGeneral'])->name('reportes.general');
    Route::middleware('can:ver-reportes')->get('/reportes/export-excel', [DteController::class, 'exportExcel'])->name('reportes.export-excel');
});

// Gestion de usuarios, roles y permisos.
Route::middleware(['auth', 'can:gestionar-usuarios'])->group(function () {
    Route::get('/usuarios', [App\Http\Controllers\UserController::class, 'index'])->name('usuarios.index');
    Route::post('/usuarios/{user}/asignar-rol', [App\Http\Controllers\UserController::class, 'asignarRol'])->name('usuarios.asignar-rol');
    Route::post('/usuarios/{user}/quitar-rol', [App\Http\Controllers\UserController::class, 'quitarRol'])->name('usuarios.quitar-rol');
    Route::post('/usuarios/{user}/asignar-permiso', [App\Http\Controllers\UserController::class, 'asignarPermiso'])->name('usuarios.asignar-permiso');
    Route::post('/usuarios/{user}/quitar-permiso', [App\Http\Controllers\UserController::class, 'quitarPermiso'])->name('usuarios.quitar-permiso');
});

require __DIR__.'/auth.php';
