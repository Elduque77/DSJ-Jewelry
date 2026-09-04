<?php

/**
 * Autor: Samuel Correa Velasquez (Desarrollador)
 */

use App\Models\Cliente;
use App\Models\Administrador;

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | DSJ Jewelry usa dos guards independientes: "cliente" para la seccion
    | publica (usuario final) y "admin" para el panel de administracion. El
    | guard "cliente" es el que se usa por defecto porque la mayor parte del
    | sitio es la seccion de usuario final. El modelo User nativo de Laravel
    | no se usa para autenticacion en este proyecto.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'cliente'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'clientes'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    */

    'guards' => [
        'cliente' => [
            'driver' => 'session',
            'provider' => 'clientes',
        ],

        'admin' => [
            'driver' => 'session',
            'provider' => 'administradores',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    */

    'providers' => [
        'clientes' => [
            'driver' => 'eloquent',
            'model' => Cliente::class,
        ],

        'administradores' => [
            'driver' => 'eloquent',
            'model' => Administrador::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    */

    'passwords' => [
        'clientes' => [
            'provider' => 'clientes',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
