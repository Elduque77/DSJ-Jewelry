<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Personalizacion extends Model
{
    public $table = 'personalizaciones';

    public $primaryKey = 'idPersonalizacion';

    public $fillable = [
        'idProducto',
        'nombreOpcion',
        'precioAdicional',
    ];

    public function getIdPersonalizacion(): int
    {
        return $this->idPersonalizacion;
    }

    public function getNombreOpcion(): string
    {
        return $this->nombreOpcion;
    }

    public function setNombreOpcion(string $nombreOpcion): void
    {
        $this->nombreOpcion = $nombreOpcion;
    }

    public function getPrecioAdicional(): float
    {
        return $this->precioAdicional;
    }

    public function setPrecioAdicional(float $precioAdicional): void
    {
        $this->precioAdicional = $precioAdicional;
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'idProducto', 'idProducto');
    }

    public function calcularPrecioAdicional(): float
    {
        return $this->producto->getPrecio() + $this->precioAdicional;
    }
}
