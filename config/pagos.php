<?php

use App\Services\Pagos\PagoConCheque;
use App\Services\Pagos\PagoConSaldo;

return [

    // Metodos de pago disponibles: clave que llega del formulario => clase
    // concreta que implementa App\Contracts\PagoInterface.
    'metodos' => [
        'saldo' => PagoConSaldo::class,
        'cheque' => PagoConCheque::class,
    ],

    'predeterminado' => 'saldo',

];
