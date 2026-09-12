<?php

/**
 * Autor: Samuel Correa Velasquez (Desarrollador)
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'totalProductos' => Producto::count(),
            'totalCategorias' => Categoria::count(),
        ]);
    }
}
