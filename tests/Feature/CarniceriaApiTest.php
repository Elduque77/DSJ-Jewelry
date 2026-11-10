<?php

/**
 * Autor: Diego (Arquitecto)
 */

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CarniceriaApiTest extends TestCase
{
    private const URL = 'http://carniceria.test/api/productos-en-venta';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.carniceria.url' => self::URL]);
        Cache::flush();
    }

    private function respuesta(): array
    {
        return [
            'tienda' => 'Carniceria Virtual',
            'generado' => '2026-11-10T18:53:55+00:00',
            'total' => 3,
            'productos' => [
                ['id' => 1, 'nombre' => 'Lomo fino', 'tipo_carne' => 'Res', 'categoria' => 'Res', 'precio' => 38500, 'descuento_hoy' => 10, 'precio_hoy' => 34650, 'enlace' => 'http://carniceria.test/productos/1'],
                ['id' => 5, 'nombre' => 'Chorizo', 'tipo_carne' => 'Cerdo', 'categoria' => 'Embutidos', 'precio' => 9800, 'descuento_hoy' => 0, 'precio_hoy' => 9800, 'enlace' => 'javascript:alert(1)'],
                ['id' => 6, 'nombre' => 'Muslo de pollo', 'tipo_carne' => 'Pollo', 'categoria' => 'Pollo', 'precio' => 12000, 'descuento_hoy' => 0, 'precio_hoy' => 12000, 'enlace' => 'http://carniceria.test/productos/6'],
            ],
        ];
    }

    public function test_muestra_los_productos_de_la_api(): void
    {
        Http::fake([self::URL => Http::response($this->respuesta())]);

        $this->get('/carniceria')
            ->assertOk()
            ->assertSee('Carniceria Virtual')
            ->assertSee('Lomo fino')
            ->assertSee('$34.650')
            ->assertSee('$38.500')
            ->assertSee('-10% HOY')
            ->assertSee('Chorizo')
            ->assertSee('http://carniceria.test/productos/1')
            ->assertDontSee('javascript:alert(1)');
    }

    public function test_filtra_por_tipo_de_carne_y_ofertas(): void
    {
        Http::fake([self::URL => Http::response($this->respuesta())]);

        $this->get('/carniceria?tipo=Pollo')
            ->assertOk()
            ->assertSee('Muslo de pollo')
            ->assertDontSee('Lomo fino');

        $this->get('/carniceria?ofertas=1')
            ->assertOk()
            ->assertSee('Lomo fino')
            ->assertDontSee('Muslo de pollo');
    }

    public function test_usa_cache_y_no_consulta_la_api_en_cada_visita(): void
    {
        Http::fake([self::URL => Http::response($this->respuesta())]);

        $this->get('/carniceria')->assertOk();
        $this->get('/carniceria')->assertOk();

        Http::assertSentCount(1);
    }

    public function test_muestra_un_mensaje_si_la_api_falla(): void
    {
        Http::fake([self::URL => Http::response('error', 500)]);

        $this->get('/carniceria')
            ->assertOk()
            ->assertSee('No pudimos cargar los productos')
            ->assertSee('no está disponible');
    }

    public function test_usa_la_copia_de_respaldo_si_la_api_deja_de_responder(): void
    {
        Http::fakeSequence(self::URL)
            ->push($this->respuesta())
            ->push('error', 500);

        $this->get('/carniceria')->assertOk()->assertSee('Lomo fino');

        Cache::forget('carniceria.productos');

        $this->get('/carniceria')
            ->assertOk()
            ->assertSee('Lomo fino')
            ->assertSee('Mostramos la última información disponible');
    }
}
