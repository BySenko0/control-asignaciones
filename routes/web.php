<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

use App\Http\Controllers\ClientesAsignacionController;
use App\Http\Controllers\ClientesCrudController;
use App\Http\Controllers\SolicitudesClienteController;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\PlantillasController;
use App\Http\Controllers\OrdenesServicioController; // Órdenes de servicio

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

    // === Plantillas y pasos ===
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

    // === Órdenes de servicio ===
    // Listado por estado
    Route::get('/ordenes/{estado}', [OrdenesServicioController::class, 'index'])
        ->whereIn('estado', ['pendiente','en_proceso','finalizado'])
        ->name('ordenes.index');

    // Aliases para el menú
    Route::get('/ordenes/pendientes', fn() => redirect()->route('ordenes.index','pendiente'))
        ->name('ordenes.pendientes');
    Route::get('/ordenes/en-proceso', fn() => redirect()->route('ordenes.index','en_proceso'))
        ->name('ordenes.enproceso');
    Route::get('/ordenes/resueltas', fn() => redirect()->route('ordenes.index','finalizado'))
        ->name('ordenes.resueltas');

    // Checklist (resolver una orden "en proceso")
    Route::get('/ordenes/{solicitud}/checklist', [OrdenesServicioController::class,'checklist'])
        ->name('ordenes.checklist');

    // Marcar / desmarcar paso
    Route::post('/ordenes/{solicitud}/paso/{paso}/toggle', [OrdenesServicioController::class,'togglePaso'])
        ->name('ordenes.paso.toggle');

    // (Opcional) Finalizar manualmente
    Route::post('/ordenes/{solicitud}/finalizar', [OrdenesServicioController::class,'finalizar'])
        ->name('ordenes.finalizar');
});

// Solo admin
Route::middleware(['auth','role:admin'])->group(function () {
    Route::get('/usuarios', [UsuariosController::class, 'index'])->name('usuarios.index');
});

require __DIR__.'/auth.php';
