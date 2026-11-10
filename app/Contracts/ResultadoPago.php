<?php

/**
 * Autor: Juan Fernando Duque (Desarrollador)
 */

namespace App\Contracts;

/**
 * Forma parte del contrato de PagoInterface (es lo que devuelve pagar()),
 * por eso vive junto a la interfaz y no junto a las implementaciones.
 *
 * Lo que devuelve un pago: un mensaje para el cliente y, si el metodo
 * genera un documento (ej. el cheque en PDF), la URL para verlo.
 */
final class ResultadoPago
{
    public function __construct(
        public readonly string $mensaje,
        public readonly ?string $urlDocumento = null,
    ) {}
}
