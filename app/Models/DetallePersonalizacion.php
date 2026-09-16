<?php

/**
 * Autor: Juan Fernando Duque (Desarrollador)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetallePersonalizacion extends Model
{
    /**
     * ATRIBUTOS DE DETALLE_PERSONALIZACION
     * $this->attributes['idDetallePersonalizacion'] - int - clave primaria
     * $this->attributes['idDetalle'] - int - linea de compra a la que pertenece
     * $this->attributes['idPersonalizacion'] - int - opcion de personalizacion elegida
     * $this->attributes['descripcion'] - string - detalle especifico elegido por el cliente (ej. texto a grabar)
     * $this->attributes['precioAplicado'] - float - costo adicional congelado al momento de la compra
     * $this->detalleCompra - DetalleCompra - linea de compra a la que pertenece
     * $this->personalizacion - Personalizacion - opcion de personalizacion elegida
     */
    public $table = 'detalle_personalizaciones';

    public $primaryKey = 'idDetallePersonalizacion';

    public $fillable = [
        'idDetalle',
        'idPersonalizacion',
        'descripcion',
        'precioAplicado',
    ];

    public function getIdDetallePersonalizacion(): int
    {
        return $this->attributes['idDetallePersonalizacion'];
    }

    public function getIdDetalle(): int
    {
        return $this->attributes['idDetalle'];
    }

    public function setIdDetalle(int $idDetalle): void
    {
        $this->attributes['idDetalle'] = $idDetalle;
    }

    public function getIdPersonalizacion(): int
    {
        return $this->attributes['idPersonalizacion'];
    }

    public function setIdPersonalizacion(int $idPersonalizacion): void
    {
        $this->attributes['idPersonalizacion'] = $idPersonalizacion;
    }

    public function getPrecioAplicado(): float
    {
        return $this->attributes['precioAplicado'];
    }

    public function setPrecioAplicado(float $precioAplicado): void
    {
        $this->attributes['precioAplicado'] = $precioAplicado;
    }

    public function detalleCompra(): BelongsTo
    {
        return $this->belongsTo(DetalleCompra::class, 'idDetalle', 'idDetalle');
    }

    public function personalizacion(): BelongsTo
    {
        return $this->belongsTo(Personalizacion::class, 'idPersonalizacion', 'idPersonalizacion');
    }

    public function getPersonalizacion(): Personalizacion
    {
        return $this->personalizacion;
    }

    // Combina el nombre de la opcion elegida con el detalle especifico que
    // escribio el cliente, para mostrar una sola linea legible en el resumen.
    public function obtenerDescripcion(): string
    {
        return $this->personalizacion->getNombreOpcion().': '.$this->attributes['descripcion'];
    }
}
