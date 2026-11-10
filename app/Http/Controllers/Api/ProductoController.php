<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Services\CatalogoService;
use Illuminate\Http\JsonResponse;

class ProductoController extends Controller
{
    /**
     * Public product contract for the future consuming application.
     */
    public function index(CatalogoService $catalogo): JsonResponse
    {
        $productos = $catalogo->obtenerProductos(soloDisponibles: true)
            ->map(fn (Producto $producto): array => $this->producto($producto))
            ->values();

        return response()->json([
            'data' => $productos,
            'meta' => [
                'count' => $productos->count(),
                'service' => 'catalog',
                'version' => 'v1',
            ],
        ]);
    }

    private function producto(Producto $producto): array
    {
        return [
            'id' => $producto->getIdProducto(),
            'name' => $producto->getNombre(),
            'description' => $producto->getDescripcion(),
            'material' => $producto->getMaterial(),
            'price' => $producto->getPrecio(),
            'stock' => $producto->getStock(),
            'category' => $producto->getCategoria()->getNombre(),
            'image_url' => $producto->getImagen()
                ? asset('storage/'.$producto->getImagen())
                : null,
        ];
    }
}
