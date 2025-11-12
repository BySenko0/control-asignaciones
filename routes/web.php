<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

use App\Http\Controllers\ClientesAsignacionController;
use App\Http\Controllers\ClientesCrudController;
use App\Http\Controllers\SolicitudesClienteController;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\PlantillasController;
use App\Http\Controllers\OrdenesServicioController; // Órdenes de servicio
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth','verified'])
    ->name('dashboard');

Route::get('/ticket/{solicitud}', [OrdenesServicioController::class, 'publicTicket'])
    ->whereNumber('solicitud')
    ->name('ticket.public');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth','role:admin|virtuality'])->group(function () {
    // =======================
    // Selector de clientes
    // =======================
    Route::get('/clientes/seleccion', [ClientesAsignacionController::class, 'index'])
        ->name('clientes.seleccion');

    // =======================
    // CRUD clientes
    // =======================
    Route::get('/clientes',                [ClientesCrudController::class, 'index'])->name('clientes.index');
    Route::get('/clientes/create',         [ClientesCrudController::class, 'create'])->name('clientes.create');
    Route::post('/clientes',               [ClientesCrudController::class, 'store'])->name('clientes.store');
    Route::get('/clientes/{id}/edit',      [ClientesCrudController::class, 'edit'])->name('clientes.edit');
    Route::put('/clientes/{id}',           [ClientesCrudController::class, 'update'])->name('clientes.update');
    Route::delete('/clientes/{id}',        [ClientesCrudController::class, 'destroy'])->name('clientes.destroy');

    // =======================
    // Solicitudes del cliente
    // =======================
    // 1) Listado general
    Route::get('/solicitudes', [SolicitudesClienteController::class, 'index'])
        ->name('solicitudes.index');

    // 2) Listado filtrado por cliente
    Route::get('/clientes/{cliente}/equipos-solicitudes', [SolicitudesClienteController::class, 'index'])
        ->name('clientes.equipos-solicitudes');

    // 3) Acciones CRUD de solicitudes (en la misma vista mediante modals)
    Route::post('/solicitudes',                    [SolicitudesClienteController::class, 'store'])->name('solicitudes.store');
    Route::put('/solicitudes/{solicitud}',         [SolicitudesClienteController::class, 'update'])->name('solicitudes.update');
    Route::delete('/solicitudes/{solicitud}',      [SolicitudesClienteController::class, 'destroy'])->name('solicitudes.destroy');
    Route::post('/solicitudes/{solicitud}/assign', [SolicitudesClienteController::class, 'assign'])->name('solicitudes.assign');

    // =======================
    // Plantillas y pasos
    // =======================
    Route::prefix('plantillas')->name('plantillas.')->group(function () {
        Route::get('/',                     [PlantillasController::class,'index'])->name('index');
        Route::post('/',                    [PlantillasController::class,'store'])->name('store');
        Route::put('/{plantilla}',          [PlantillasController::class,'update'])->name('update');
        Route::delete('/{plantilla}',       [PlantillasController::class,'destroy'])->name('destroy');

        // Pasos
        Route::get('/{plantilla}/pasos',               [PlantillasController::class,'pasos'])->name('pasos');
        Route::post('/{plantilla}/pasos',              [PlantillasController::class,'pasoStore'])->name('pasos.store');
        Route::put('/{plantilla}/pasos/{paso}',        [PlantillasController::class,'pasoUpdate'])->name('pasos.update');
        Route::delete('/{plantilla}/pasos/{paso}',     [PlantillasController::class,'pasoDestroy'])->name('pasos.destroy');
        Route::post('/{plantilla}/pasos/{paso}/mover', [PlantillasController::class,'pasoMover'])->name('pasos.mover');
    });

    // =======================
    // Órdenes de servicio
    // =======================
    // Listas por estado (rutas explícitas y con nombre)
    Route::get('/ordenes/pendiente',  [OrdenesServicioController::class, 'index'])
        ->name('ordenes.pendientes')->defaults('estado','pendiente');

    Route::get('/ordenes/en_proceso', [OrdenesServicioController::class, 'index'])
        ->name('ordenes.en_proceso')->defaults('estado','en_proceso');

    Route::get('/ordenes/finalizado', [OrdenesServicioController::class, 'index'])
        ->name('ordenes.resueltas')->defaults('estado','finalizado');

    Route::get('/ordenes/vencidas', [OrdenesServicioController::class, 'index'])
        ->name('ordenes.vencidas')->defaults('estado','vencidas');

    // Checklist (resolver/continuar una orden)
    Route::get('/ordenes/{solicitud}/checklist', [OrdenesServicioController::class,'checklist'])
        ->name('ordenes.checklist');

    // Marcar / desmarcar paso (PATCH). Nombre usado en la vista checklist.
    Route::patch('/ordenes/{solicitud}/paso/{paso}', [OrdenesServicioController::class,'togglePaso'])
        ->name('ordenes.toggle');

    // Finalizar manualmente (opcional)
    Route::post('/ordenes/{solicitud}/finalizar', [OrdenesServicioController::class,'finalizar'])
        ->name('ordenes.finalizar');

    Route::get('/ordenes/{solicitud}/ticket', [OrdenesServicioController::class,'ticket'])
        ->name('ordenes.ticket');
});

// Solo admin: usuarios (index + crear + actualizar)
Route::middleware(['auth','role:admin'])->group(function () {
    Route::get('/usuarios',                 [UsuariosController::class, 'index'])->name('usuarios.index');
    Route::post('/usuarios',                [UsuariosController::class, 'store'])->name('usuarios.store');
    Route::put('/usuarios/{usuario}',       [UsuariosController::class, 'update'])->name('usuarios.update');
});

require __DIR__.'/auth.php';
