<?php

namespace Tests\Unit;

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
}
