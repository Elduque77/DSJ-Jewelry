<?php

/**
 * Autor: Samuel Correa Velasquez (Desarrollador)
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoriaController extends Controller
{
    public function index(): View
    {
        $categorias = Categoria::all();

        return view('admin.categoria.index', [
            'categorias' => $categorias,
        ]);
    }

    public function create(): View
    {
        return view('admin.categoria.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['required', 'string'],
        ]);

        $categoria = new Categoria;
        $categoria->setNombre($datos['nombre']);
        $categoria->setDescripcion($datos['descripcion']);
        $categoria->save();

        return redirect()->route('admin.categoria.index')
            ->with('mensaje', 'Categoria creada correctamente.');
    }

    public function edit(Categoria $categoria): View
    {
        return view('admin.categoria.edit', [
            'categoria' => $categoria,
        ]);
    }

    public function update(Request $request, Categoria $categoria): RedirectResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['required', 'string'],
        ]);

        $categoria->setNombre($datos['nombre']);
        $categoria->setDescripcion($datos['descripcion']);
        $categoria->save();

        return redirect()->route('admin.categoria.index')
            ->with('mensaje', 'Categoria actualizada correctamente.');
    }

    public function destroy(Categoria $categoria): RedirectResponse
    {
        $categoria->delete();

        return redirect()->route('admin.categoria.index')
            ->with('mensaje', 'Categoria eliminada correctamente.');
    }
}
