<?php

use App\Services\Pagos\PagoConCheque;
use App\Services\Pagos\PagoConSaldo;

return [

    // Metodos de pago disponibles: clave que llega del formulario => clase
    // concreta que implementa App\Contracts\PagoInterface. Agregar un metodo
    // es crear la clase y agregar una entrada aqui.
    'metodos' => [
        'saldo' => PagoConSaldo::class,
        'cheque' => PagoConCheque::class,
    ],

    // Metodo que aparece seleccionado por defecto en el carrito.
    'predeterminado' => 'saldo',

];
