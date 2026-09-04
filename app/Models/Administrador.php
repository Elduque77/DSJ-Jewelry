<?php

/**
 * Autor: Samuel Correa Velasquez (Desarrollador)
 */

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

class Administrador extends Authenticatable
{
    /**
     * ATRIBUTOS DE ADMINISTRADOR
     * $this->attributes['idAdministrador'] - int - clave primaria del administrador
     * $this->attributes['nombre'] - string - nombre del administrador
     * $this->attributes['correo'] - string - correo electronico, unico en la tabla
     * $this->attributes['contrasena'] - string - contrasena cifrada
     *
     * Administrador representa exclusivamente a los administradores de la tienda.
     * Se autentica con el guard "admin" (config/auth.php), independiente del
     * guard "cliente" que usa el modelo Cliente.
     */
    public $table = 'administradores';

    public $primaryKey = 'idAdministrador';

    public $fillable = [
        'nombre',
        'correo',
        'contrasena',
    ];

    public $hidden = [
        'contrasena',
    ];

    public function getIdAdministrador(): int
    {
        return $this->attributes['idAdministrador'];
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
