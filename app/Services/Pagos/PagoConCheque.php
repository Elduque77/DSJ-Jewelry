<?php

/**
 * Autor: Juan Fernando Duque (Desarrollador)
 */

namespace App\Services\Pagos;

use App\Contracts\PagoInterface;
use App\Contracts\ResultadoPago;
use App\Models\Cliente;
use App\Models\Compra;

/**
 * Pago simulado con cheque: no cobra nada al instante. La compra queda
 * "pendiente" y se entrega un PDF con los datos del cheque para pagar.
 */
class PagoConCheque implements PagoInterface
{
    public function etiqueta(Cliente $cliente): string
    {
        return __('messages.pay_with_cheque');
    }

    public function pagar(Compra $compra, Cliente $cliente): ResultadoPago
    {
        return new ResultadoPago(
            __('messages.cheque_generated'),
            route('compra.cheque', $compra->getIdCompra()),
        );
    }
}
