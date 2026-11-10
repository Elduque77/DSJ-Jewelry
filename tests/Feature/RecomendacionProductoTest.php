<?php

/**
 * Autor: Samuel Correa Velasquez (Desarrollador)
 */

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RecomendacionProductoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('es');
    }

    public function test_recomienda_productos_usando_gemini(): void
    {
        config(['services.gemini.api_key' => 'test-key']);
        $categoria = Categoria::create([
            'nombre' => 'Anillos',
            'descripcion' => 'Anillos de prueba',
        ]);
        $producto = Producto::create([
            'idCategoria' => $categoria->getIdCategoria(),
            'nombre' => 'Anillo Luna',
            'descripcion' => 'Anillo elegante para regalo',
            'material' => 'Plata 925',
            'precio' => 180000,
            'stock' => 5,
            'imagen' => 'products/anillo-luna.png',
        ]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [[
                            'text' => '{"producto_ids":['.$producto->getIdProducto().'],"explicacion":"Te recomiendo este anillo de plata."}',
                        ]],
                    ],
                ]],
            ]),
        ]);

        $this->withSession(['locale' => 'es'])
            ->post('/recomendaciones', [
            'necesidad' => 'Busco un anillo elegante de plata',
            ])
            ->assertOk()
            ->assertSee('Recomendaciones para ti')
            ->assertSee('Te recomiendo este anillo de plata.')
            ->assertSee('Anillo Luna')
            ->assertSee('storage/products/anillo-luna.png')
            ->assertSee('Disponibles')
            ->assertSee('Agregar al carrito')
            ->assertSee('name="ancla" value="recomendacion-producto-', false)
            ->assertDontSee('Ver producto');

        Http::assertSentCount(1);
    }

    public function test_la_recomendacion_exige_una_necesidad(): void
    {
        $this->from('/')
            ->post('/recomendaciones', ['necesidad' => ''])
            ->assertRedirect('/')
            ->assertSessionHasErrors('necesidad');
    }

    public function test_producto_recomendado_se_agrega_al_carrito(): void
    {
        $categoria = Categoria::create([
            'nombre' => 'Anillos',
            'descripcion' => 'Anillos de prueba',
        ]);
        $producto = Producto::create([
            'idCategoria' => $categoria->getIdCategoria(),
            'nombre' => 'Anillo Luna',
            'descripcion' => 'Anillo elegante para regalo',
            'material' => 'Plata 925',
            'precio' => 180000,
            'stock' => 5,
            'imagen' => 'products/anillo-luna.png',
        ]);

        $this->from('/recomendaciones')
            ->post(route('carrito.agregar', $producto->getIdProducto()))
            ->assertRedirect(route('home'))
            ->assertSessionHas('carrito', [$producto->getIdProducto() => 1]);
    }

    public function test_producto_se_agrega_y_regresa_a_su_posicion(): void
    {
        $categoria = Categoria::create([
            'nombre' => 'Anillos',
            'descripcion' => 'Anillos de prueba',
        ]);
        $producto = Producto::create([
            'idCategoria' => $categoria->getIdCategoria(),
            'nombre' => 'Anillo Luna',
            'descripcion' => 'Anillo elegante para regalo',
            'material' => 'Plata 925',
            'precio' => 180000,
            'stock' => 5,
            'imagen' => 'products/anillo-luna.png',
        ]);

        $this->withSession([
            'recomendacion_pendiente' => [
                'ids' => [$producto->getIdProducto()],
                'explicacion' => 'Te recomiendo este anillo de plata.',
            ],
        ])->post(route('carrito.agregar', $producto->getIdProducto()), [
            'ancla' => 'recomendacion-producto-'.$producto->getIdProducto(),
        ])
            ->assertRedirect(route('home').'#recomendacion-producto-'.$producto->getIdProducto())
            ->assertSessionHas('carrito', [$producto->getIdProducto() => 1]);

        $this->get(route('home'))
            ->assertSee('Te recomiendo este anillo de plata.')
            ->assertSee('Anillo Luna')
            ->assertSee('storage/products/anillo-luna.png');
    }
}
