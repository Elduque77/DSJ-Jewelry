<?php

/**
 * Autor: Juan Fernando Duque (Desarrollador)
 */

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Producto;
use App\Services\CarritoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use InvalidArgumentException;

class CarritoController extends Controller
{
    public function __construct(private readonly CarritoService $carrito) {}

    public function index(): View
    {
        return view('cliente.carrito', [
            'lineas' => $this->carrito->obtenerLineas(),
            'total' => $this->carrito->calcularTotal(),
        ]);
    }

    public function agregar(Request $request, Producto $producto): RedirectResponse
    {
        $cantidad = max(1, (int) $request->input('cantidad', 1));

        try {
            $this->carrito->agregar($producto->getIdProducto(), $cantidad);
        } catch (InvalidArgumentException $excepcion) {
            return back()->with('error', $excepcion->getMessage());
        }

        return back()->with('mensaje', 'Producto agregado al carrito.');
    }

    public function actualizar(Request $request, Producto $producto): RedirectResponse
    {
        $cantidad = (int) $request->input('cantidad', 1);

        $this->carrito->actualizarCantidad($producto->getIdProducto(), $cantidad);

        return back()->with('mensaje', 'Carrito actualizado.');
    }

    public function eliminar(Producto $producto): RedirectResponse
    {
        $this->carrito->eliminar($producto->getIdProducto());

        return back()->with('mensaje', 'Producto eliminado del carrito.');
    }

    // Convierte el carrito de sesion en una Compra real: valida stock,
    // congela el precio de cada linea y descuenta inventario, todo en una
    // sola transaccion para no dejar datos a medias si algo falla.
    public function confirmar(): RedirectResponse
    {
        $lineas = $this->carrito->obtenerLineas();

        if ($lineas->isEmpty()) {
            return back()->with('error', 'Tu carrito esta vacio.');
        }

        foreach ($lineas as $linea) {
            if ($linea['cantidad'] > $linea['producto']->getStock()) {
                return back()->with(
                    'error',
                    'No hay suficiente stock de "'.$linea['producto']->getNombre().'".'
                );
            }
        }

        $idCliente = Auth::guard('cliente')->id();

        DB::transaction(function () use ($lineas, $idCliente): void {
            $compra = Compra::crearCompra($idCliente);

            foreach ($lineas as $linea) {
                $detalle = new DetalleCompra;
                $detalle->setIdProducto($linea['producto']->getIdProducto());
                $detalle->setCantidad($linea['cantidad']);
                $detalle->setPrecioUnitario($linea['producto']->getPrecio());

                $compra->agregarDetalle($detalle);

                $linea['producto']->actualizarStock($linea['cantidad']);
            }

            $compra->cambiarEstado('pagada');
        });

        $this->carrito->vaciar();

        return redirect()->route('home')->with('mensaje', '¡Compra confirmada! Gracias por tu pedido.');
    }
}
