<?php

use App\Http\Controllers\Api\ProductoController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/productos', [ProductoController::class, 'index'])
        ->name('api.v1.productos.index');
});
