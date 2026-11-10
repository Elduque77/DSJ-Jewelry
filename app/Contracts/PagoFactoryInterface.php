<?php

/**
 * Autor: Juan Fernando Duque (Desarrollador)
 */

namespace App\Contracts;

use App\Exceptions\PagoException;
use App\Models\Cliente;

/**
 * Contrato para obtener la forma de pago elegida por el cliente.
 *
 * CarritoController depende de esta interfaz: no sabe que metodos existen
 * ni que clase concreta atiende cada uno.
 */
interface PagoFactoryInterface
{
    /**
     * Devuelve la implementacion de PagoInterface para el metodo indicado.
     *
     * @throws PagoException si el metodo no existe
     */
    public function crear(string $metodo): PagoInterface;

    /**
     * @return list<string> claves validas de metodo de pago
     */
    public function metodosDisponibles(): array;

    /**
     * Opciones para pintar en el formulario del carrito.
     *
     * @return array<string, string> clave del metodo => texto para el cliente
     */
    public function opciones(Cliente $cliente): array;

    public function predeterminado(): string;
}
