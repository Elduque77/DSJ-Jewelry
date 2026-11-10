<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PagoCompraTest extends TestCase
{
    use RefreshDatabase;

    public function test_pago_con_saldo_descuenta_el_saldo_y_deja_la_compra_pagada(): void
    {
        [$cliente, $producto] = $this->escenario(saldo: 1000);

        $this->actingAs($cliente, 'cliente')
            ->withSession(['carrito' => [$producto->getIdProducto() => 2]])
            ->post(route('carrito.confirmar'), ['metodo_pago' => 'saldo'])
            ->assertRedirect(route('home'));

        $this->assertSame(800.0, $cliente->fresh()->getSaldo());
        $this->assertSame('pagada', Compra::firstOrFail()->getEstado());
    }

    public function test_pago_con_saldo_insuficiente_no_crea_la_compra(): void
    {
        [$cliente, $producto] = $this->escenario(saldo: 50);

        $this->actingAs($cliente, 'cliente')
            ->withSession(['carrito' => [$producto->getIdProducto() => 1]])
            ->post(route('carrito.confirmar'), ['metodo_pago' => 'saldo'])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('compras', 0);
        $this->assertSame(50.0, $cliente->fresh()->getSaldo());
        $this->assertSame(5, $producto->fresh()->getStock());
    }

    public function test_pago_con_cheque_deja_la_compra_pendiente_y_muestra_el_pdf(): void
    {
        [$cliente, $producto] = $this->escenario(saldo: 1000);

        $this->actingAs($cliente, 'cliente')
            ->withSession(['carrito' => [$producto->getIdProducto() => 1]])
            ->post(route('carrito.confirmar'), ['metodo_pago' => 'cheque'])
            ->assertRedirect(route('home'))
            ->assertSessionHas('mensaje', __('messages.cheque_generated'));

        $compra = Compra::firstOrFail();
        $this->assertSame(route('compra.cheque', $compra->getIdCompra()), session('documento'));
        $this->assertSame('pendiente', $compra->getEstado());
        $this->assertSame(1000.0, $cliente->fresh()->getSaldo());

        $this->get(route('compra.cheque', $compra->getIdCompra()))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_un_cliente_no_puede_ver_el_cheque_de_otro(): void
    {
        [$cliente, $producto] = $this->escenario(saldo: 1000);
        $otro = $this->crearCliente('otro@gmail.com', 1000);

        $this->actingAs($cliente, 'cliente')
            ->withSession(['carrito' => [$producto->getIdProducto() => 1]])
            ->post(route('carrito.confirmar'), ['metodo_pago' => 'cheque']);

        $this->actingAs($otro, 'cliente')
            ->get(route('compra.cheque', Compra::firstOrFail()->getIdCompra()))
            ->assertForbidden();
    }

    public function test_metodo_de_pago_invalido_es_rechazado(): void
    {
        [$cliente, $producto] = $this->escenario(saldo: 1000);

        $this->actingAs($cliente, 'cliente')
            ->withSession(['carrito' => [$producto->getIdProducto() => 1]])
            ->post(route('carrito.confirmar'), ['metodo_pago' => 'bitcoin'])
            ->assertSessionHasErrors('metodo_pago');

        $this->assertDatabaseCount('compras', 0);
    }

    public function test_stock_insuficiente_no_crea_la_compra_ni_cobra(): void
    {
        [$cliente, $producto] = $this->escenario(saldo: 1000);

        $this->actingAs($cliente, 'cliente')
            ->withSession(['carrito' => [$producto->getIdProducto() => 6]])
            ->post(route('carrito.confirmar'), ['metodo_pago' => 'saldo'])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('compras', 0);
        $this->assertSame(1000.0, $cliente->fresh()->getSaldo());
        $this->assertSame(5, $producto->fresh()->getStock());
    }

    public function test_el_carrito_muestra_los_metodos_de_pago_configurados(): void
    {
        [$cliente, $producto] = $this->escenario(saldo: 1000);

        $this->actingAs($cliente, 'cliente')
            ->withSession(['carrito' => [$producto->getIdProducto() => 1]])
            ->get(route('carrito.index'))
            ->assertOk()
            ->assertSee('value="saldo"', false)
            ->assertSee('value="cheque"', false)
            ->assertSee(__('messages.pay_with_balance'))
            ->assertSee(__('messages.pay_with_cheque'));
    }

    private function escenario(float $saldo): array
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

        return [$this->crearCliente('laura@gmail.com', $saldo), $producto];
    }

    private function crearCliente(string $correo, float $saldo): Cliente
    {
        $cliente = Cliente::create([
            'nombre' => 'Cliente',
            'apellido' => 'De Prueba',
            'correo' => $correo,
            'contrasena' => Hash::make('12345678'),
        ]);

        // saldo no es mass-assignable a proposito: se fija por consulta directa.
        Cliente::where('idCliente', $cliente->getIdCliente())->update(['saldo' => $saldo]);

        return $cliente->fresh();
    }
}
