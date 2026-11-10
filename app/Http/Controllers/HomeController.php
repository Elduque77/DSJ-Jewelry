<?php

/**
 * Autor: Samuel Correa Velasquez (Desarrollador)
 * Autor: Juan Fernando Duque (Desarrollador) - busqueda por nombre
 */

namespace App\Http\Controllers;

use App\Exceptions\RecomendacionProductoException;
use App\Models\Producto;
use App\Services\CatalogoService;
use App\Services\RecomendacionProductoService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    private const RECOMENDACION_PENDIENTE = 'recomendacion_pendiente';

    public function index(Request $request, CatalogoService $catalogo): View
    {
        $busqueda = trim((string) $request->query('buscar', ''));

        $productos = $catalogo->obtenerProductos()
            ->when($busqueda !== '', fn ($productos) => $productos->filter(
                fn (Producto $producto): bool => str_contains(
                    mb_strtolower($producto->getNombre()),
                    mb_strtolower($busqueda),
                ),
            ))
            ->values();

        $recomendacion = session()->pull(self::RECOMENDACION_PENDIENTE);

        return $this->vistaCatalogo($productos, $busqueda, $recomendacion);
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

        session()->put(self::RECOMENDACION_PENDIENTE, [
            'ids' => $resultado['productos']->map(
                fn (Producto $producto): int => $producto->getIdProducto(),
            )->all(),
            'explicacion' => $resultado['explicacion'],
        ]);

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

    private function vistaCatalogo(
        Collection $productos,
        string $busqueda,
        ?array $recomendacion = null,
    ): View {
        return view('cliente.home', [
            'productos' => $productos,
            'busqueda' => $busqueda,
            'necesidad' => '',
            'resultadoRecomendacion' => $this->reconstruirRecomendacion($recomendacion),
            'errorRecomendacion' => null,
        ]);
    }

    private function reconstruirRecomendacion(?array $recomendacion): ?array
    {
        if ($recomendacion === null) {
            return null;
        }

        $productos = Producto::with(['categoria', 'resenas'])
            ->whereIn('idProducto', $recomendacion['ids'])
            ->where('stock', '>', 0)
            ->get()
            ->sortBy(fn (Producto $producto): int => array_search(
                $producto->getIdProducto(),
                $recomendacion['ids'],
                true,
            ))
            ->values();

        return $productos->isEmpty() ? null : [
            'productos' => $productos,
            'explicacion' => $recomendacion['explicacion'],
        ];
    }
}
