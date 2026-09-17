<?php

/**
 * Autor: Diego (Arquitecto)
 */

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProductoPublicoController extends Controller
{
    public function show(Producto $producto): View
    {
        $producto->load(['categoria', 'resenas.cliente']);

        // Si el visitante es un cliente con sesion iniciada se busca su propia
        // resena para decidir si el formulario se muestra en modo crear o editar.
        $resenaPropia = Auth::guard('cliente')->check()
            ? $producto->getResenas()->firstWhere('idCliente', Auth::guard('cliente')->id())
            : null;

        return view('cliente.producto.show', [
            'producto' => $producto,
            'resenaPropia' => $resenaPropia,
        ]);
    }
}
