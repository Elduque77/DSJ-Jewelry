<?php

/**
 * Autor: Juan Fernando Duque (Desarrollador)
 */

namespace App\Services\Pagos;

use App\Contracts\PagoInterface;
use App\Exceptions\PagoException;
use Illuminate\Contracts\Container\Container;

/**
 * Traduce el metodo elegido por el cliente ("saldo", "cheque") a la clase
 * concreta registrada en config/pagos.php. El controlador solo ve PagoInterface.
 */
class PagoFactory
{
    public function __construct(private readonly Container $contenedor) {}

    public function crear(string $metodo): PagoInterface
    {
        $clase = config('pagos.metodos.'.$metodo);

        if ($clase === null) {
            throw new PagoException(__('messages.payment_method_invalid'));
        }

        return $this->contenedor->make($clase);
    }

    /**
     * @return list<string>
     */
    public function metodosDisponibles(): array
    {
        return array_keys(config('pagos.metodos'));
    }
}
