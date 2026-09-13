<?php

/**
 * Autor: Samuel Correa Velasquez (Desarrollador)
 * Autor: Juan Fernando Duque (Desarrollador) - busqueda por nombre
 */

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $busqueda = trim((string) $request->query('buscar', ''));

        $productos = Producto::with('categoria')
            ->when($busqueda !== '', fn ($query) => $query->buscarPorNombre($busqueda))
            ->get();

        return view('cliente.home', [
            'productos' => $productos,
            'busqueda' => $busqueda,
        ]);
    }
}
