<?php

/**
 * Autor: Diego (Arquitecto)
 */

use App\Http\Controllers\Admin\CategoriaController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductoController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\ClienteAuthController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Seccion de usuario final (guard "cliente")
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('guest:cliente')->group(function (): void {
    Route::get('/login', [ClienteAuthController::class, 'mostrarLogin'])->name('cliente.login');
    Route::post('/login', [ClienteAuthController::class, 'login'])->name('cliente.login.submit');
    Route::get('/registro', [ClienteAuthController::class, 'mostrarRegistro'])->name('cliente.registro');
    Route::post('/registro', [ClienteAuthController::class, 'registrar'])->name('cliente.registro.submit');
});

Route::middleware('auth:cliente')->group(function (): void {
    Route::post('/logout', [ClienteAuthController::class, 'logout'])->name('cliente.logout');
});

/*
|--------------------------------------------------------------------------
| Seccion de administracion (guard "admin")
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest:admin')->group(function (): void {
        Route::get('/login', [AdminAuthController::class, 'mostrarLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    });

    Route::middleware('auth:admin')->group(function (): void {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('categoria', CategoriaController::class)
            ->parameters(['categoria' => 'categoria'])
            ->except(['show']);

        Route::resource('producto', ProductoController::class)
            ->parameters(['producto' => 'producto'])
            ->except(['show']);
    });
});
