<?php

/**
 * Autor: Juan Fernando Duque (Desarrollador)
 */

namespace App\Contracts;

use App\Exceptions\PagoException;
use App\Models\Cliente;
use App\Models\Compra;

/**
 * Contrato de pago (inversion de dependencias).
 *
 * CarritoController depende de esta interfaz y no de una forma de pago
 * concreta: agregar un metodo nuevo (tarjeta, PSE...) es crear otra clase
 * que la implemente y registrarla en config/pagos.php, sin tocar el
 * controlador ni la vista.
 */
interface PagoInterface
{
    /**
     * Texto con el que el metodo se presenta al cliente en el carrito.
     */
    public function etiqueta(Cliente $cliente): string;

    /**
     * Cobra la compra al cliente y deja la compra en el estado que
     * corresponda al metodo.
     *
     * @throws PagoException si no se puede pagar
     */
    public function pagar(Compra $compra, Cliente $cliente): ResultadoPago;
}
