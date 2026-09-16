<?php

/**
 * Autor: Juan Fernando Duque (Desarrollador)
 */

namespace App\Services;

use App\Models\Producto;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;

/**
 * El carrito vive solo en la sesion del navegador (idProducto => cantidad).
 * No se persiste en base de datos hasta que el cliente confirma la compra,
 * momento en el que CarritoController crea la Compra y sus DetalleCompra.
 */
class CarritoService
{
    private const CLAVE_SESION = 'carrito';

    public function agregar(int $idProducto, int $cantidad = 1): void
    {
        $producto = Producto::findOrFail($idProducto);

        if (! $producto->consultarDisponibilidad()) {
            throw new InvalidArgumentException('El producto no tiene stock disponible.');
        }

        $contenido = $this->contenidoCrudo();
        $cantidadActual = $contenido[$idProducto] ?? 0;
        $nuevaCantidad = min($cantidadActual + $cantidad, $producto->getStock());

        $contenido[$idProducto] = $nuevaCantidad;
        Session::put(self::CLAVE_SESION, $contenido);
    }

    public function actualizarCantidad(int $idProducto, int $cantidad): void
    {
        if ($cantidad <= 0) {
            $this->eliminar($idProducto);

            return;
        }

        $producto = Producto::findOrFail($idProducto);
        $contenido = $this->contenidoCrudo();
        $contenido[$idProducto] = min($cantidad, $producto->getStock());

        Session::put(self::CLAVE_SESION, $contenido);
    }

    public function eliminar(int $idProducto): void
    {
        $contenido = $this->contenidoCrudo();
        unset($contenido[$idProducto]);

        Session::put(self::CLAVE_SESION, $contenido);
    }

    public function vaciar(): void
    {
        Session::forget(self::CLAVE_SESION);
    }

    public function estaVacio(): bool
    {
        return $this->contenidoCrudo() === [];
    }

    // Convierte el array crudo de sesion en una coleccion con el producto ya
    // cargado y su subtotal calculado, lista para pintar en la vista.
    public function obtenerLineas(): Collection
    {
        $contenido = $this->contenidoCrudo();

        if ($contenido === []) {
            return collect();
        }

        return Producto::whereIn('idProducto', array_keys($contenido))
            ->get()
            ->map(fn (Producto $producto) => [
                'producto' => $producto,
                'cantidad' => $contenido[$producto->getIdProducto()],
                'subtotal' => $producto->getPrecio() * $contenido[$producto->getIdProducto()],
            ]);
    }

    public function calcularTotal(): float
    {
        return $this->obtenerLineas()->sum('subtotal');
    }

    // Array crudo tal como se guarda en sesion: [idProducto => cantidad].
    public function contenidoCrudo(): array
    {
        return Session::get(self::CLAVE_SESION, []);
    }
}
