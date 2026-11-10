<?php

/**
 * Autor: Juan Fernando Duque (Desarrollador)
 */

namespace App\Http\Controllers;

use App\Contracts\PagoInterface;
use App\Exceptions\PagoException;
use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Producto;
use App\Services\CarritoService;
use App\Services\Pagos\PagoFactory;
use App\Services\Pagos\ResultadoPago;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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
        $ancla = preg_match('/^(producto|recomendacion-producto)-\d+$/', (string) $request->input('ancla'))
            ? $request->input('ancla')
            : null;

        try {
            $this->carrito->agregar($producto->getIdProducto(), $cantidad);
        } catch (InvalidArgumentException $excepcion) {
            return $this->redireccionarAlProducto($ancla)
                ->with('error', $excepcion->getMessage());
        }

        return $this->redireccionarAlProducto($ancla)
            ->with('mensaje', __('messages.product_added'));
    }

    private function redireccionarAlProducto(?string $ancla): RedirectResponse
    {
        return redirect()->to(route('home').($ancla ? '#'.$ancla : ''));
    }

    public function actualizar(Request $request, Producto $producto): RedirectResponse
    {
        $cantidad = (int) $request->input('cantidad', 1);

        $this->carrito->actualizarCantidad($producto->getIdProducto(), $cantidad);

        return back()->with('mensaje', __('messages.cart_updated'));
    }

    public function eliminar(Producto $producto): RedirectResponse
    {
        $this->carrito->eliminar($producto->getIdProducto());

        return back()->with('mensaje', __('messages.product_removed'));
    }

    // Convierte el carrito de sesion en una Compra real: valida stock,
    // congela el precio de cada linea, descuenta inventario y cobra con el
    // metodo elegido. Este metodo solo conoce PagoInterface: la clase
    // concreta (saldo, cheque...) la entrega PagoFactory. Todo va en una
    // sola transaccion: si el pago falla, no queda nada a medias.
    public function confirmar(Request $request, PagoFactory $pagos): RedirectResponse
    {
        $datos = $request->validate([
            'metodo_pago' => ['required', Rule::in($pagos->metodosDisponibles())],
        ]);

        $lineas = $this->carrito->obtenerLineas();

        if ($lineas->isEmpty()) {
            return back()->with('error', __('messages.cart_empty'));
        }

        foreach ($lineas as $linea) {
            if ($linea['cantidad'] > $linea['producto']->getStock()) {
                return back()->with(
                    'error',
                    __('messages.insufficient_stock', ['product' => $linea['producto']->getNombre()])
                );
            }
        }

        $cliente = Auth::guard('cliente')->user();
        $pago = $pagos->crear($datos['metodo_pago']);

        try {
            $resultado = DB::transaction(function () use ($lineas, $cliente, $pago): ResultadoPago {
                return $this->registrarCompra($lineas, $cliente, $pago);
            });
        } catch (PagoException $excepcion) {
            return back()->with('error', $excepcion->getMessage());
        }

        $this->carrito->vaciar();

        if ($resultado->urlDocumento !== null) {
            return redirect()->to($resultado->urlDocumento);
        }

        return redirect()->route('home')->with('mensaje', $resultado->mensaje);
    }

    private function registrarCompra($lineas, $cliente, PagoInterface $pago): ResultadoPago
    {
        $compra = Compra::crearCompra($cliente->getIdCliente());

        foreach ($lineas as $linea) {
            $detalle = new DetalleCompra;
            $detalle->setIdProducto($linea['producto']->getIdProducto());
            $detalle->setCantidad($linea['cantidad']);
            $detalle->setPrecioUnitario($linea['producto']->getPrecio());

            $compra->agregarDetalle($detalle);

            $linea['producto']->actualizarStock($linea['cantidad']);
        }

        return $pago->pagar($compra, $cliente);
    }
}
