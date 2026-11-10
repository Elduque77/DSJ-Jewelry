<?php

namespace Tests\Unit;

use App\Models\Categoria;
use App\Models\Personalizacion;
use App\Models\Producto;
use App\Models\Resena;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\TestCase;

class DomainModelsTest extends TestCase
{
    public function test_producto_getters_return_business_attributes(): void
    {
        $producto = new Producto([
            'nombre' => 'Anillo',
            'descripcion' => 'Anillo de prueba',
            'material' => 'Plata',
            'precio' => 100.50,
            'stock' => 3,
        ]);

        $this->assertSame('Anillo', $producto->getNombre());
        $this->assertSame('Anillo de prueba', $producto->getDescripcion());
        $this->assertSame('Plata', $producto->getMaterial());
        $this->assertSame(100.50, $producto->getPrecio());
        $this->assertSame(3, $producto->getStock());
    }

    public function test_personalizacion_calcula_el_precio_total(): void
    {
        $producto = new Producto(['precio' => 100.00]);
        $personalizacion = new Personalizacion(['precioAdicional' => 25.50]);
        $personalizacion->setRelation('producto', $producto);

        $this->assertSame(125.50, $personalizacion->calcularPrecioTotal());
    }

    public function test_producto_calcula_el_promedio_de_calificacion(): void
    {
        $producto = new Producto(['nombre' => 'Anillo']);
        $producto->setRelation('resenas', new Collection([
            new Resena(['calificacion' => 5]),
            new Resena(['calificacion' => 4]),
            new Resena(['calificacion' => 4]),
        ]));

        $this->assertSame(3, $producto->getTotalResenas());
        $this->assertSame(4.3, $producto->getPromedioCalificacion());
    }

    public function test_producto_sin_resenas_tiene_promedio_cero(): void
    {
        $producto = new Producto(['nombre' => 'Anillo']);
        $producto->setRelation('resenas', new Collection);

        $this->assertSame(0, $producto->getTotalResenas());
        $this->assertSame(0.0, $producto->getPromedioCalificacion());
    }

    public function test_producto_consulta_disponibilidad_segun_stock(): void
    {
        $disponible = new Producto(['stock' => 2]);
        $agotado = new Producto(['stock' => 0]);

        $this->assertTrue($disponible->consultarDisponibilidad());
        $this->assertFalse($agotado->consultarDisponibilidad());
    }

    public function test_producto_setters_actualizan_sus_atributos(): void
    {
        $producto = new Producto([
            'nombre' => 'Anillo',
            'descripcion' => 'Descripción inicial',
            'material' => 'Plata',
            'precio' => 100.00,
            'stock' => 1,
        ]);

        $producto->setNombre('Cadena');
        $producto->setDescripcion('Descripción actualizada');
        $producto->setMaterial('Oro');
        $producto->setPrecio(250.50);
        $producto->setStock(4);

        $this->assertSame('Cadena', $producto->getNombre());
        $this->assertSame('Descripción actualizada', $producto->getDescripcion());
        $this->assertSame('Oro', $producto->getMaterial());
        $this->assertSame(250.50, $producto->getPrecio());
        $this->assertSame(4, $producto->getStock());
    }

    public function test_producto_puede_tener_una_imagen(): void
    {
        $producto = new Producto;

        $producto->setImagen('products/anillo-luna.webp');

        $this->assertSame('products/anillo-luna.webp', $producto->getImagen());
    }

    public function test_producto_nuevo_usa_imagen_predeterminada(): void
    {
        $producto = new Producto(['nombre' => 'Anillo sin foto']);

        $this->assertSame(Producto::IMAGEN_PREDETERMINADA, $producto->getImagen());
        $this->assertTrue($producto->usaImagenPredeterminada());
    }

    public function test_categoria_setters_actualizan_sus_atributos(): void
    {
        $categoria = new Categoria([
            'nombre' => 'Anillos',
            'descripcion' => 'Piezas para regalar',
        ]);

        $categoria->setNombre('Cadenas');
        $categoria->setDescripcion('Piezas para usar a diario');

        $this->assertSame('Cadenas', $categoria->getNombre());
        $this->assertSame('Piezas para usar a diario', $categoria->getDescripcion());
    }

    public function test_personalizacion_setters_actualizan_la_opcion(): void
    {
        $personalizacion = new Personalizacion([
            'nombreOpcion' => 'Grabado',
            'precioAdicional' => 10.00,
        ]);

        $personalizacion->setNombreOpcion('Baño dorado');
        $personalizacion->setPrecioAdicional(35.50);

        $this->assertSame('Baño dorado', $personalizacion->getNombreOpcion());
        $this->assertSame(35.50, $personalizacion->getPrecioAdicional());
    }
}
