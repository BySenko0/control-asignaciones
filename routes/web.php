<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ClientesAsignacionController;
use App\Http\Controllers\ClientesCrudController;
use App\Http\Controllers\SolicitudesClienteController;
use App\Http\Controllers\UsuariosController;


Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth','role:admin|virtuality'])->group(function () {
    // Selector de clientes
    Route::get('/clientes/seleccion', [ClientesAsignacionController::class, 'index'])
        ->name('clientes.seleccion');

    // CRUD clientes
    Route::get('/clientes',                [ClientesCrudController::class, 'index'])->name('clientes.index');
    Route::get('/clientes/create',         [ClientesCrudController::class, 'create'])->name('clientes.create');
    Route::post('/clientes',               [ClientesCrudController::class, 'store'])->name('clientes.store');
    Route::get('/clientes/{id}/edit',      [ClientesCrudController::class, 'edit'])->name('clientes.edit');
    Route::put('/clientes/{id}',           [ClientesCrudController::class, 'update'])->name('clientes.update');
    Route::delete('/clientes/{id}',        [ClientesCrudController::class, 'destroy'])->name('clientes.destroy');

    // === Solicitudes del cliente ===
    // 1) Listado general o filtrado por cliente (el botón "Ir" usa la de abajo)
    Route::get('/solicitudes', [SolicitudesClienteController::class, 'index'])
        ->name('solicitudes.index');

    // 2) Vista de equipos/solicitudes por cliente (desde el botón "Ir")
    Route::get('/clientes/{cliente}/equipos-solicitudes', [SolicitudesClienteController::class, 'index'])
        ->name('clientes.equipos-solicitudes');

    // 3) Acciones desde los modals en la misma vista
    Route::post('/solicitudes',                          [SolicitudesClienteController::class, 'store'])->name('solicitudes.store');
    Route::put('/solicitudes/{solicitud}',               [SolicitudesClienteController::class, 'update'])->name('solicitudes.update');
    Route::delete('/solicitudes/{solicitud}',            [SolicitudesClienteController::class, 'destroy'])->name('solicitudes.destroy');
    Route::post('/solicitudes/{solicitud}/assign',       [SolicitudesClienteController::class, 'assign'])->name('solicitudes.assign');
});

Route::middleware(['auth','role:admin'])->group(function () {
    Route::get('/usuarios', [UsuariosController::class, 'index'])->name('usuarios.index');
});

require __DIR__.'/auth.php';
