<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductoApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_api_returns_only_products_with_stock_and_expected_contract(): void
    {
        $categoria = Categoria::create([
            'nombre' => 'Anillos',
            'descripcion' => 'Anillos de prueba',
        ]);

        Producto::create([
            'idCategoria' => $categoria->getIdCategoria(),
            'nombre' => 'Disponible',
            'descripcion' => 'Producto disponible',
            'material' => 'Plata',
            'precio' => 100,
            'stock' => 2,
        ]);
        Producto::create([
            'idCategoria' => $categoria->getIdCategoria(),
            'nombre' => 'Agotado',
            'descripcion' => 'Producto agotado',
            'material' => 'Oro',
            'precio' => 200,
            'stock' => 0,
        ]);

        $this->getJson('/api/v1/productos')
            ->assertOk()
            ->assertJsonPath('meta.count', 1)
            ->assertJsonPath('meta.service', 'catalog')
            ->assertJsonPath('data.0.name', 'Disponible')
            ->assertJsonMissing(['name' => 'Agotado'])
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'name',
                    'description',
                    'material',
                    'price',
                    'stock',
                    'category',
                ]],
                'meta' => ['count', 'service', 'version'],
            ]);
    }
}
