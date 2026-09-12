<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Resena;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ResenaClienteTest extends TestCase
{
    use RefreshDatabase;

    public function test_ficha_de_producto_es_publica(): void
    {
        $producto = $this->crearProducto();

        $this->get('/producto/'.$producto->getIdProducto())->assertOk();
    }

    public function test_invitado_ve_enlace_a_login_en_lugar_del_formulario(): void
    {
        $producto = $this->crearProducto();

        $this->get('/producto/'.$producto->getIdProducto())
            ->assertOk()
            ->assertSee('Inicia sesión')
            ->assertDontSee('Escribe tu reseña');
    }

    public function test_cliente_sin_resena_ve_el_formulario_de_creacion(): void
    {
        $producto = $this->crearProducto();
        $cliente = $this->crearCliente('laura@gmail.com');

        $this->actingAs($cliente, 'cliente')
            ->get('/producto/'.$producto->getIdProducto())
            ->assertOk()
            ->assertSee('Escribe tu reseña')
            ->assertDontSee('Eliminar mi reseña');
    }

    public function test_cliente_con_resena_ve_el_formulario_de_edicion(): void
    {
        $producto = $this->crearProducto();
        $cliente = $this->crearCliente('laura@gmail.com');
        $this->crearResena($cliente, $producto, 5);

        $this->actingAs($cliente, 'cliente')
            ->get('/producto/'.$producto->getIdProducto())
            ->assertOk()
            ->assertSee('Editar mi reseña')
            ->assertSee('Eliminar mi reseña');
    }

    public function test_invitado_no_puede_publicar_resena(): void
    {
        $producto = $this->crearProducto();

        $this->post('/producto/'.$producto->getIdProducto().'/resena', [
            'calificacion' => 5,
            'comentario' => 'Excelente',
        ])->assertRedirect('/login');

        $this->assertDatabaseCount('resenas', 0);
    }

    public function test_cliente_autenticado_publica_resena(): void
    {
        $producto = $this->crearProducto();
        $cliente = $this->crearCliente('laura@gmail.com');

        $this->actingAs($cliente, 'cliente')
            ->post('/producto/'.$producto->getIdProducto().'/resena', [
                'calificacion' => 4,
                'comentario' => 'Muy bonito',
            ])
            ->assertRedirect('/producto/'.$producto->getIdProducto());

        $this->assertDatabaseHas('resenas', [
            'idCliente' => $cliente->getIdCliente(),
            'idProducto' => $producto->getIdProducto(),
            'calificacion' => 4,
            'comentario' => 'Muy bonito',
            'compraVerificada' => 0,
        ]);
    }

    public function test_calificacion_fuera_de_rango_es_rechazada(): void
    {
        $producto = $this->crearProducto();
        $cliente = $this->crearCliente('laura@gmail.com');

        $this->actingAs($cliente, 'cliente')
            ->post('/producto/'.$producto->getIdProducto().'/resena', [
                'calificacion' => 6,
            ])
            ->assertSessionHasErrors('calificacion');

        $this->assertDatabaseCount('resenas', 0);
    }

    public function test_cliente_no_puede_resenar_dos_veces_el_mismo_producto(): void
    {
        $producto = $this->crearProducto();
        $cliente = $this->crearCliente('laura@gmail.com');
        $this->crearResena($cliente, $producto, 5);

        $this->actingAs($cliente, 'cliente')
            ->post('/producto/'.$producto->getIdProducto().'/resena', [
                'calificacion' => 3,
            ])
            ->assertSessionHasErrors('calificacion');

        $this->assertDatabaseCount('resenas', 1);
    }

    public function test_cliente_edita_su_propia_resena(): void
    {
        $producto = $this->crearProducto();
        $cliente = $this->crearCliente('laura@gmail.com');
        $resena = $this->crearResena($cliente, $producto, 2);

        $this->actingAs($cliente, 'cliente')
            ->put('/resena/'.$resena->getIdResena(), [
                'calificacion' => 5,
                'comentario' => 'Cambie de opinion',
            ])
            ->assertRedirect('/producto/'.$producto->getIdProducto());

        $this->assertDatabaseHas('resenas', [
            'idResena' => $resena->getIdResena(),
            'calificacion' => 5,
            'comentario' => 'Cambie de opinion',
        ]);
    }

    public function test_cliente_no_puede_editar_resena_ajena(): void
    {
        $producto = $this->crearProducto();
        $autora = $this->crearCliente('laura@gmail.com');
        $intrusa = $this->crearCliente('andres@gmail.com');
        $resena = $this->crearResena($autora, $producto, 5);

        $this->actingAs($intrusa, 'cliente')
            ->put('/resena/'.$resena->getIdResena(), ['calificacion' => 1])
            ->assertForbidden();

        $this->assertDatabaseHas('resenas', [
            'idResena' => $resena->getIdResena(),
            'calificacion' => 5,
        ]);
    }

    public function test_cliente_no_puede_borrar_resena_ajena(): void
    {
        $producto = $this->crearProducto();
        $autora = $this->crearCliente('laura@gmail.com');
        $intrusa = $this->crearCliente('andres@gmail.com');
        $resena = $this->crearResena($autora, $producto, 5);

        $this->actingAs($intrusa, 'cliente')
            ->delete('/resena/'.$resena->getIdResena())
            ->assertForbidden();

        $this->assertDatabaseCount('resenas', 1);
    }

    public function test_cliente_borra_su_propia_resena(): void
    {
        $producto = $this->crearProducto();
        $cliente = $this->crearCliente('laura@gmail.com');
        $resena = $this->crearResena($cliente, $producto, 5);

        $this->actingAs($cliente, 'cliente')
            ->delete('/resena/'.$resena->getIdResena())
            ->assertRedirect('/producto/'.$producto->getIdProducto());

        $this->assertDatabaseCount('resenas', 0);
    }

    private function crearProducto(): Producto
    {
        $categoria = Categoria::create([
            'nombre' => 'Anillos',
            'descripcion' => 'Anillos de prueba',
        ]);

        return Producto::create([
            'idCategoria' => $categoria->getIdCategoria(),
            'nombre' => 'Anillo Luna',
            'descripcion' => 'Anillo de prueba',
            'material' => 'Plata',
            'precio' => 100.00,
            'stock' => 5,
        ]);
    }

    private function crearCliente(string $correo): Cliente
    {
        return Cliente::create([
            'nombre' => 'Cliente',
            'apellido' => 'De Prueba',
            'correo' => $correo,
            'contrasena' => Hash::make('12345678'),
        ]);
    }

    private function crearResena(Cliente $cliente, Producto $producto, int $calificacion): Resena
    {
        return Resena::create([
            'idCliente' => $cliente->getIdCliente(),
            'idProducto' => $producto->getIdProducto(),
            'calificacion' => $calificacion,
            'comentario' => 'Comentario de prueba',
            'compraVerificada' => false,
            'fecha' => now()->toDateString(),
        ]);
    }
}
