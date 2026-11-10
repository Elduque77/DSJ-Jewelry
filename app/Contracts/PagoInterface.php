<?php

/**
 * Autor: Juan Fernando Duque (Desarrollador)
 */

namespace App\Contracts;

use App\Exceptions\PagoException;
use App\Models\Cliente;
use App\Models\Compra;
use App\Services\Pagos\ResultadoPago;

/**
 * Contrato de pago (inversion de dependencias).
 *
 * CarritoController depende de esta interfaz y no de una forma de pago
 * concreta: agregar un metodo nuevo (tarjeta, PSE...) es crear otra clase
 * que la implemente, sin tocar el controlador.
 */
interface PagoInterface
{
    /**
     * Cobra la compra al cliente y deja la compra en el estado que
     * corresponda al metodo. Lanza PagoException si no se puede pagar.
     */
    public function pagar(Compra $compra, Cliente $cliente): ResultadoPago;
}
