<?php

/**
 * Autor: Samuel Correa Velasquez (Desarrollador)
 */

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('cliente.home', [
            'productos' => Producto::with('categoria')->get(),
        ]);
    }
}
