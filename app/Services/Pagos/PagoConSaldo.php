<?php

/**
 * Autor: Juan Fernando Duque (Desarrollador)
 */

namespace App\Services\Pagos;

use App\Contracts\PagoInterface;
use App\Exceptions\PagoException;
use App\Models\Cliente;
use App\Models\Compra;

/**
 * Pago simulado descontando el dinero disponible del cliente (clientes.saldo).
 */
class PagoConSaldo implements PagoInterface
{
    public function pagar(Compra $compra, Cliente $cliente): ResultadoPago
    {
        $monto = (float) $compra->getTotal();

        // Un solo UPDATE condicionado: solo descuenta si el saldo alcanza.
        // Evita que dos compras simultaneas dejen el saldo en negativo.
        $descontados = Cliente::where('idCliente', $cliente->getIdCliente())
            ->where('saldo', '>=', $monto)
            ->decrement('saldo', $monto);

        if ($descontados === 0) {
            throw new PagoException(__('messages.insufficient_balance'));
        }

        $compra->cambiarEstado('pagada');

        return new ResultadoPago(__('messages.purchase_confirmed'));
    }
}
