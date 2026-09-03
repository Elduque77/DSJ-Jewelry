<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;

class Cliente extends Model
{
    /**
     * ATRIBUTOS DE CLIENTE
     * $this->attributes['idCliente'] - int - clave primaria del cliente
     * $this->attributes['nombre'] - string - nombre del cliente
     * $this->attributes['apellido'] - string - apellido del cliente
     * $this->attributes['correo'] - string - correo electronico, unico en la tabla
     * $this->attributes['telefono'] - string - telefono de contacto, opcional
     * $this->attributes['direccion'] - string - direccion de envio, opcional
     * $this->attributes['contrasena'] - string - contrasena cifrada
     * $this->resenas - Collection - resenas escritas por el cliente
     */
    protected $primaryKey = 'idCliente';

    protected $fillable = [
        'nombre',
        'apellido',
        'correo',
        'telefono',
        'direccion',
        'contrasena',
    ];

    protected $hidden = [
        'contrasena',
    ];

    public function getIdCliente(): int
    {
        return $this->attributes['idCliente'];
    }

    public function getNombre(): string
    {
        return $this->attributes['nombre'];
    }

    public function setNombre(string $nombre): void
    {
        $this->attributes['nombre'] = $nombre;
    }

    public function getApellido(): string
    {
        return $this->attributes['apellido'];
    }

    public function setApellido(string $apellido): void
    {
        $this->attributes['apellido'] = $apellido;
    }

    public function getCorreo(): string
    {
        return $this->attributes['correo'];
    }

    public function setCorreo(string $correo): void
    {
        $this->attributes['correo'] = $correo;
    }

    public function getTelefono(): ?string
    {
        return $this->attributes['telefono'];
    }

    public function setTelefono(?string $telefono): void
    {
        $this->attributes['telefono'] = $telefono;
    }

    public function getDireccion(): ?string
    {
        return $this->attributes['direccion'];
    }

    public function setDireccion(?string $direccion): void
    {
        $this->attributes['direccion'] = $direccion;
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

    public function getNombreCompleto(): string
    {
        return $this->attributes['nombre'].' '.$this->attributes['apellido'];
    }

    public function resenas(): HasMany
    {
        return $this->hasMany(Resena::class, 'idCliente', 'idCliente');
    }

    public function getResenas(): Collection
    {
        return $this->resenas;
    }
}
