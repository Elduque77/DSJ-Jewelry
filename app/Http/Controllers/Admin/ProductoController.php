<?php

/**
 * Autor: Samuel Correa Velasquez (Desarrollador)
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductoController extends Controller
{
    // Los listados del panel se paginan para no cargar la tabla entera en
    // memoria cuando el catalogo crezca.
    private const POR_PAGINA = 10;

    public function index(): View
    {
        $productos = Producto::with('categoria')->orderBy('nombre')->paginate(self::POR_PAGINA);

        return view('admin.producto.index', [
            'productos' => $productos,
        ]);
    }

    public function create(): View
    {
        $categorias = Categoria::all();

        return view('admin.producto.create', [
            'categorias' => $categorias,
            'producto' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validarDatos($request);

        $producto = new Producto;
        $producto->fill($datos);
        $producto->save();

        return redirect()->route('admin.producto.index')
            ->with('mensaje', 'Producto creado correctamente.');
    }

    public function edit(Producto $producto): View
    {
        $categorias = Categoria::all();

        return view('admin.producto.edit', [
            'producto' => $producto,
            'categorias' => $categorias,
        ]);
    }

    public function update(Request $request, Producto $producto): RedirectResponse
    {
        $datos = $this->validarDatos($request);

        $producto->fill($datos);
        $producto->save();

        return redirect()->route('admin.producto.index')
            ->with('mensaje', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto): RedirectResponse
    {
        $producto->delete();

        return redirect()->route('admin.producto.index')
            ->with('mensaje', 'Producto eliminado correctamente.');
    }

    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'idCategoria' => ['required', 'integer', 'exists:categorias,idCategoria'],
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['required', 'string'],
            'material' => ['required', 'string', 'max:100'],
            'precio' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
        ]);
    }
}
