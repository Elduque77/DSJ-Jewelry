<?php

namespace Tests\Feature;

use App\Contracts\GeneradorPdfInterface;
use App\Contracts\PagoInterface;
use App\Contracts\ResultadoPago;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Comprueba que CarritoController y ChequeController dependen de contratos:
 * se cambian las implementaciones sin tocar el codigo de los controladores.
 */
class InversionDependenciasPagoTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_metodo_de_pago_nuevo_funciona_solo_registrandolo_en_la_configuracion(): void
    {
        config(['pagos.metodos.prueba' => PagoDePrueba::class]);
        [$cliente, $producto] = $this->escenario();

        $this->actingAs($cliente, 'cliente')
            ->withSession(['carrito' => [$producto->getIdProducto() => 1]])
            ->get(route('carrito.index'))
            ->assertSee('value="prueba"', false)
            ->assertSee('Pago de prueba');

        $this->actingAs($cliente, 'cliente')
            ->withSession(['carrito' => [$producto->getIdProducto() => 1]])
            ->post(route('carrito.confirmar'), ['metodo_pago' => 'prueba'])
            ->assertRedirect(route('home'))
            ->assertSessionHas('mensaje', 'Pagado con el metodo de prueba');

        $this->assertSame('enviada', Compra::firstOrFail()->getEstado());
    }

    public function test_el_cheque_usa_el_generador_de_pdf_registrado_en_el_contenedor(): void
    {
        $this->app->bind(GeneradorPdfInterface::class, GeneradorPdfFalso::class);
        [$cliente, $producto] = $this->escenario();

        $this->actingAs($cliente, 'cliente')
            ->withSession(['carrito' => [$producto->getIdProducto() => 1]])
            ->post(route('carrito.confirmar'), ['metodo_pago' => 'cheque']);

        $respuesta = $this->get(route('compra.cheque', Compra::firstOrFail()->getIdCompra()))
            ->assertOk();

        $this->assertStringStartsWith('PDF-FALSO|landscape|', $respuesta->getContent());
        $this->assertStringContainsString('CHQ-', $respuesta->getContent());
    }

    private function escenario(): array
    {
        $categoria = Categoria::create(['nombre' => 'Anillos', 'descripcion' => 'Anillos de prueba']);

        $producto = Producto::create([
            'idCategoria' => $categoria->getIdCategoria(),
            'nombre' => 'Anillo Luna',
            'descripcion' => 'Anillo de prueba',
            'material' => 'Plata',
            'precio' => 100.00,
            'stock' => 5,
        ]);

        $cliente = Cliente::create([
            'nombre' => 'Cliente',
            'apellido' => 'De Prueba',
            'correo' => 'laura@gmail.com',
            'contrasena' => Hash::make('12345678'),
        ]);

        return [$cliente->fresh(), $producto];
    }
}

class PagoDePrueba implements PagoInterface
{
    public function etiqueta(Cliente $cliente): string
    {
        return 'Pago de prueba';
    }

    public function pagar(Compra $compra, Cliente $cliente): ResultadoPago
    {
        $compra->cambiarEstado('enviada');

        return new ResultadoPago('Pagado con el metodo de prueba');
    }
}

class GeneradorPdfFalso implements GeneradorPdfInterface
{
    public function generar(string $html, string $papel = 'A4', string $orientacion = 'portrait'): string
    {
        return 'PDF-FALSO|'.$orientacion.'|'.$html;
    }
}
