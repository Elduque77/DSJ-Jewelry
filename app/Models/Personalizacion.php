<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Personalizacion extends Model
{
    /**
     * ATRIBUTOS DE PERSONALIZACION
     * $this->attributes['idPersonalizacion'] - int - clave primaria de la personalizacion
     * $this->attributes['idProducto'] - int - producto al que pertenece la opcion
     * $this->attributes['nombreOpcion'] - string - nombre de la opcion de personalizacion
     * $this->attributes['precioAdicional'] - float - costo adicional de esta opcion
     * $this->producto - Producto - producto al que pertenece esta personalizacion
     */
    public $table = 'personalizaciones';

    public $primaryKey = 'idPersonalizacion';

    public $fillable = [
        'idProducto',
        'nombreOpcion',
        'precioAdicional',
    ];

    public function getIdPersonalizacion(): int
    {
        return $this->attributes['idPersonalizacion'];
    }

    public function getNombreOpcion(): string
    {
        return $this->attributes['nombreOpcion'];
    }

    public function setNombreOpcion(string $nombreOpcion): void
    {
        $this->attributes['nombreOpcion'] = $nombreOpcion;
    }

    public function getPrecioAdicional(): float
    {
        return $this->attributes['precioAdicional'];
    }

    public function setPrecioAdicional(float $precioAdicional): void
    {
        $this->attributes['precioAdicional'] = $precioAdicional;
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'idProducto', 'idProducto');
    }

    public function getProducto(): Producto
    {
        return $this->producto;
    }

    // Devuelve el precio base del producto sumado al costo de esta personalizacion.
    public function calcularPrecioTotal(): float
    {
        return $this->producto->getPrecio() + $this->attributes['precioAdicional'];
    }
}
