<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

class Resena extends Model
{
    /**
     * ATRIBUTOS DE RESENA
     * $this->attributes['idResena'] - int - clave primaria de la resena
     * $this->attributes['idCliente'] - int - cliente que escribio la resena
     * $this->attributes['idProducto'] - int - producto resenado
     * $this->attributes['calificacion'] - int - puntuacion de 1 a 5
     * $this->attributes['comentario'] - string - texto de la resena, opcional
     * $this->attributes['compraVerificada'] - bool - si proviene de una compra validada
     * $this->attributes['fecha'] - string - fecha de publicacion, formato Y-m-d
     * $this->cliente - Cliente - autor de la resena
     * $this->producto - Producto - producto resenado
     */
    public $table = 'resenas';

    public $primaryKey = 'idResena';

    public $fillable = [
        'idCliente',
        'idProducto',
        'calificacion',
        'comentario',
        'compraVerificada',
        'fecha',
    ];

    public function getIdResena(): int
    {
        return $this->attributes['idResena'];
    }

    public function getIdCliente(): int
    {
        return $this->attributes['idCliente'];
    }

    public function setIdCliente(int $idCliente): void
    {
        $this->attributes['idCliente'] = $idCliente;
    }

    public function getIdProducto(): int
    {
        return $this->attributes['idProducto'];
    }

    public function setIdProducto(int $idProducto): void
    {
        $this->attributes['idProducto'] = $idProducto;
    }

    public function getCalificacion(): int
    {
        return $this->attributes['calificacion'];
    }

    // La tabla resenas tiene un CHECK de 1 a 5. La misma regla se valida aqui
    // para fallar con un mensaje claro y no con un error de SQL.
    public function setCalificacion(int $calificacion): void
    {
        if ($calificacion < 1 || $calificacion > 5) {
            throw new InvalidArgumentException('La calificacion debe estar entre 1 y 5.');
        }

        $this->attributes['calificacion'] = $calificacion;
    }

    public function getComentario(): ?string
    {
        return $this->attributes['comentario'];
    }

    public function setComentario(?string $comentario): void
    {
        $this->attributes['comentario'] = $comentario;
    }

    public function getCompraVerificada(): bool
    {
        return $this->attributes['compraVerificada'];
    }

    public function setCompraVerificada(bool $compraVerificada): void
    {
        $this->attributes['compraVerificada'] = $compraVerificada;
    }

    public function getFecha(): string
    {
        return $this->attributes['fecha'];
    }

    public function setFecha(string $fecha): void
    {
        $this->attributes['fecha'] = $fecha;
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'idCliente', 'idCliente');
    }

    public function getCliente(): Cliente
    {
        return $this->cliente;
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'idProducto', 'idProducto');
    }

    public function getProducto(): Producto
    {
        return $this->producto;
    }
}