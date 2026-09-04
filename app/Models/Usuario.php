<?php

/**
 * Autor: Samuel Correa Velasquez (Desarrollador)
 */

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

class Usuario extends Authenticatable
{
    /**
     * ATRIBUTOS DE USUARIO
     * $this->attributes['idUsuario'] - int - clave primaria del usuario administrador
     * $this->attributes['nombre'] - string - nombre del administrador
     * $this->attributes['correo'] - string - correo electronico, unico en la tabla
     * $this->attributes['contrasena'] - string - contrasena cifrada
     *
     * Usuario representa exclusivamente a los administradores de la tienda.
     * Se autentica con el guard "admin" (config/auth.php), independiente del
     * guard "cliente" que usa el modelo Cliente.
     */
    public $table = 'usuarios';

    public $primaryKey = 'idUsuario';

    public $fillable = [
        'nombre',
        'correo',
        'contrasena',
    ];

    public $hidden = [
        'contrasena',
    ];

    public function getIdUsuario(): int
    {
        return $this->attributes['idUsuario'];
    }

    public function getNombre(): string
    {
        return $this->attributes['nombre'];
    }

    public function setNombre(string $nombre): void
    {
        $this->attributes['nombre'] = $nombre;
    }

    public function getCorreo(): string
    {
        return $this->attributes['correo'];
    }

    public function setCorreo(string $correo): void
    {
        $this->attributes['correo'] = $correo;
    }

    // La contrasena no tiene getter: nunca se lee desde fuera del modelo.
    // Se guarda siempre cifrada y solo se compara con verificarContrasena().
    public function setContrasena(string $contrasena): void
    {
        $this->attributes['contrasena'] = Hash::make($contrasena);
    }

    public function verificarContrasena(string $contrasena): bool
    {
        return Hash::check($contrasena, $this->attributes['contrasena']);
    }

    // Le dice al sistema de autenticacion de Laravel en que columna esta
    // la contrasena cifrada, ya que aqui se llama "contrasena" y no
    // "password" como espera el paquete por defecto.
    public function getAuthPassword(): string
    {
        return $this->attributes['contrasena'];
    }
}
