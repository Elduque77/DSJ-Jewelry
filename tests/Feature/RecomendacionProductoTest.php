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

    public function test_recomienda_productos_usando_gemini(): void
    {
        config(['services.gemini.api_key' => 'test-key']);
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [[
                            'text' => '{"producto_ids":[1],"explicacion":"Te recomiendo este anillo de plata."}',
                        ]],
                    ],
                ]],
            ]),
        ]);

        $categoria = Categoria::create([
            'nombre' => 'Anillos',
            'descripcion' => 'Anillos de prueba',
        ]);
        Producto::create([
            'idCategoria' => $categoria->getIdCategoria(),
            'nombre' => 'Anillo Luna',
            'descripcion' => 'Anillo elegante para regalo',
            'material' => 'Plata 925',
            'precio' => 180000,
            'stock' => 5,
        ]);

        $this->post('/recomendaciones', [
            'necesidad' => 'Busco un anillo elegante de plata',
        ])
            ->assertOk()
            ->assertSee('Recomendaciones para ti')
            ->assertSee('Te recomiendo este anillo de plata.')
            ->assertSee('Anillo Luna');

        Http::assertSentCount(1);
    }

    public function test_la_recomendacion_exige_una_necesidad(): void
    {
        $this->from('/')
            ->post('/recomendaciones', ['necesidad' => ''])
            ->assertRedirect('/')
            ->assertSessionHasErrors('necesidad');
    }
}
