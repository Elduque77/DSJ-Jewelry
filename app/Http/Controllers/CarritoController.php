<?php

/**
 * Autor: Juan Fernando Duque (Desarrollador)
 */

namespace App\Http\Controllers;

use App\Contracts\PagoFactoryInterface;
use App\Contracts\PagoInterface;
use App\Contracts\ResultadoPago;
use App\Exceptions\PagoException;
use App\Exceptions\StockInsuficienteException;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Producto;
use App\Services\CarritoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use InvalidArgumentException;

class CarritoController extends Controller
{
    public function __construct(private readonly CarritoService $carrito) {}

    public function index(PagoFactoryInterface $pagos): View
    {
        $cliente = Auth::guard('cliente')->user();

        return view('cliente.carrito', [
            'lineas' => $this->carrito->obtenerLineas(),
            'total' => $this->carrito->calcularTotal(),
            'metodosPago' => $cliente ? $pagos->opciones($cliente) : [],
            'metodoPredeterminado' => $pagos->predeterminado(),
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
    // concreta (saldo, cheque...) la entrega PagoFactoryInterface. Todo va en una
    // sola transaccion: si el pago falla, no queda nada a medias.
    public function confirmar(Request $request, PagoFactoryInterface $pagos): RedirectResponse
    {
        $datos = $request->validate([
            'metodo_pago' => ['required', Rule::in($pagos->metodosDisponibles())],
        ]);

        $lineas = $this->carrito->obtenerLineas();

        if ($lineas->isEmpty()) {
            return back()->with('error', __('messages.cart_empty'));
        }

        $cliente = Auth::guard('cliente')->user();
        $pago = $pagos->crear($datos['metodo_pago']);

        try {
            $resultado = DB::transaction(function () use ($lineas, $cliente, $pago): ResultadoPago {
                return $this->registrarCompra($lineas, $cliente, $pago);
            });
        } catch (PagoException|StockInsuficienteException $excepcion) {
            return back()->with('error', $excepcion->getMessage());
        }

        $this->carrito->vaciar();

        return redirect()->route('home')
            ->with('mensaje', $resultado->mensaje)
            ->with('documento', $resultado->urlDocumento);
    }

    private function registrarCompra(Collection $lineas, Cliente $cliente, PagoInterface $pago): ResultadoPago
    {
        $compra = Compra::crearCompra($cliente->getIdCliente());

        foreach ($lineas as $linea) {
            // Se relee el producto bloqueando su fila: el stock se valida y se
            // descuenta dentro de la transaccion, asi dos compras simultaneas
            // no pueden vender la misma unidad.
            $producto = Producto::lockForUpdate()->findOrFail($linea['producto']->getIdProducto());

            if ($linea['cantidad'] > $producto->getStock()) {
                throw new StockInsuficienteException(
                    __('messages.insufficient_stock', ['product' => $producto->getNombre()])
                );
            }

            $detalle = new DetalleCompra;
            $detalle->setIdProducto($producto->getIdProducto());
            $detalle->setCantidad($linea['cantidad']);
            $detalle->setPrecioUnitario($producto->getPrecio());

            $compra->agregarDetalle($detalle);

            $producto->actualizarStock($linea['cantidad']);
        }

        return $pago->pagar($compra, $cliente);
    }
}
