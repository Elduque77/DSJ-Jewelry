<?php

namespace Tests\Unit;

use App\Models\Personalizacion;
use App\Models\Producto;
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
}
