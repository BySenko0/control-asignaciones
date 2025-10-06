<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientesAsignacionController;
use App\Http\Controllers\ClientesCrudController;

Route::get('/', function () {
    // Si está autenticado mándalo al dashboard; si no, al login
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
    Route::get('/clientes/seleccion', [ClientesAsignacionController::class, 'index'])
        ->name('clientes.seleccion');

    Route::get('/clientes',                [ClientesCrudController::class, 'index'])->name('clientes.index');
    Route::get('/clientes/create',         [ClientesCrudController::class, 'create'])->name('clientes.create');
    Route::post('/clientes',               [ClientesCrudController::class, 'store'])->name('clientes.store');
    Route::get('/clientes/{id}/edit',      [ClientesCrudController::class, 'edit'])->name('clientes.edit');
    Route::put('/clientes/{id}',           [ClientesCrudController::class, 'update'])->name('clientes.update');
    Route::delete('/clientes/{id}',        [ClientesCrudController::class, 'destroy'])->name('clientes.destroy');
});

require __DIR__.'/auth.php';
