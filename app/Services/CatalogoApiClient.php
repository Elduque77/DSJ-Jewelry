<?php

namespace App\Services;

use App\Exceptions\CatalogoApiException;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;

class CatalogoApiClient
{
    public function obtenerProductos(): Collection
    {
        try {
            $response = Http::acceptJson()
                ->connectTimeout(3)
                ->timeout(10)
                ->get(route('api.v1.productos.index'));
        } catch (\Throwable $exception) {
            throw new CatalogoApiException(__('messages.catalog_unavailable'), 0, $exception);
        }

        if ($response->failed()) {
            throw new CatalogoApiException(__('messages.catalog_unavailable'));
        }

        $productos = $response->json('data');
        if (! is_array($productos)) {
            throw new CatalogoApiException(__('messages.catalog_invalid_response'));
        }

        return new Collection(array_map(
            fn (array $datos): Producto => $this->crearProducto($datos),
            $productos,
        ));
    }

    private function crearProducto(array $datos): Producto
    {
        $categoria = new Categoria(['nombre' => (string) ($datos['category'] ?? '')]);
        $producto = new Producto([
            'nombre' => (string) ($datos['name'] ?? ''),
            'descripcion' => (string) ($datos['description'] ?? ''),
            'material' => (string) ($datos['material'] ?? ''),
            'precio' => (float) ($datos['price'] ?? 0),
            'stock' => (int) ($datos['stock'] ?? 0),
        ]);
        $producto->setAttribute('idProducto', (int) ($datos['id'] ?? 0));
        $producto->setRelation('categoria', $categoria);
        $producto->setRelation('resenas', new Collection);

        return $producto;
    }
}
