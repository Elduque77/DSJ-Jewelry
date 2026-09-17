<?php

/**
 * Autor: Samuel Correa Velasquez (Desarrollador)
 * Autor: Juan Fernando Duque (Desarrollador) - busqueda por nombre
 */

namespace App\Http\Controllers;

use App\Exceptions\RecomendacionProductoException;
use App\Models\Producto;
use App\Services\RecomendacionProductoService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $busqueda = trim((string) $request->query('buscar', ''));

        $productos = Producto::with(['categoria', 'resenas'])
            ->when($busqueda !== '', fn ($query) => $query->buscarPorNombre($busqueda))
            ->get();

        return $this->vistaCatalogo($productos, $busqueda);
    }

    public function recomendar(Request $request, RecomendacionProductoService $recomendador): View
    {
        $datos = $request->validate([
            'necesidad' => ['required', 'string', 'min:3', 'max:300'],
        ]);

        $productos = $this->catalogo();

        try {
            $resultado = $recomendador->recomendar($datos['necesidad']);
        } catch (RecomendacionProductoException $exception) {
            return $this->vistaCatalogo($productos, '')
                ->with('errorRecomendacion', $exception->getMessage())
                ->with('necesidad', $datos['necesidad']);
        }

        return view('cliente.home', [
            'productos' => $productos,
            'busqueda' => '',
            'necesidad' => $datos['necesidad'],
            'resultadoRecomendacion' => $resultado,
            'errorRecomendacion' => null,
        ]);
    }

    private function catalogo(): Collection
    {
        return Producto::with(['categoria', 'resenas'])->get();
    }

    private function vistaCatalogo(Collection $productos, string $busqueda): View
    {
        return view('cliente.home', [
            'productos' => $productos,
            'busqueda' => $busqueda,
            'necesidad' => '',
            'resultadoRecomendacion' => null,
            'errorRecomendacion' => null,
        ]);
    }
}
