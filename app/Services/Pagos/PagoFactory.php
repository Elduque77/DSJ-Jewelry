<?php

/**
 * Autor: Juan Fernando Duque (Desarrollador)
 */

namespace App\Services\Pagos;

use App\Contracts\PagoFactoryInterface;
use App\Contracts\PagoInterface;
use App\Exceptions\PagoException;
use App\Models\Cliente;
use Illuminate\Contracts\Container\Container;

/**
 * Traduce el metodo elegido por el cliente ("saldo", "cheque") a la clase
 * concreta registrada en config/pagos.php.
 */
class PagoFactory implements PagoFactoryInterface
{
    public function __construct(private readonly Container $contenedor) {}

    public function crear(string $metodo): PagoInterface
    {
        $clase = config('pagos.metodos.'.$metodo);

        if ($clase === null) {
            throw new PagoException(__('messages.payment_method_invalid'));
        }

        $pago = $this->contenedor->make($clase);

        if (! $pago instanceof PagoInterface) {
            throw new PagoException(__('messages.payment_method_invalid'));
        }

        return $pago;
    }

    public function metodosDisponibles(): array
    {
        return array_keys(config('pagos.metodos'));
    }

    public function opciones(Cliente $cliente): array
    {
        $opciones = [];

        foreach ($this->metodosDisponibles() as $metodo) {
            $opciones[$metodo] = $this->crear($metodo)->etiqueta($cliente);
        }

        return $opciones;
    }

    public function predeterminado(): string
    {
        return config('pagos.predeterminado');
    }
}
