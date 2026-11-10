<?php

namespace Tests\Unit;

use App\Services\CatalogoApiClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CatalogoApiClientTest extends TestCase
{
    public function test_maps_the_json_catalog_contract_to_products(): void
    {
        Http::fake([
            '*' => Http::response([
                'data' => [[
                    'id' => 7,
                    'name' => 'Anillo Luna',
                    'description' => 'Anillo de plata',
                    'material' => 'Plata 925',
                    'price' => 180000,
                    'stock' => 5,
                    'category' => 'Anillos',
                ]],
                'meta' => ['count' => 1, 'service' => 'catalog', 'version' => 'v1'],
            ]),
        ]);

        $producto = app(CatalogoApiClient::class)->obtenerProductos()->first();

        $this->assertSame(7, $producto->getIdProducto());
        $this->assertSame('Anillo Luna', $producto->getNombre());
        $this->assertSame('Anillos', $producto->getCategoria()->getNombre());
        $this->assertSame(5, $producto->getStock());
    }

    public function test_rejects_an_invalid_catalog_response(): void
    {
        Http::fake(['*' => Http::response(['unexpected' => true])]);

        $this->expectExceptionMessage('La respuesta del catálogo no tiene el formato esperado.');

        app(CatalogoApiClient::class)->obtenerProductos();
    }
}
