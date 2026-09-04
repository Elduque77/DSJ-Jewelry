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
<<<<<<< HEAD
     * $this->attributes['precioAdicional'] - float - costo adicional de esta opcion
     * $this->producto - Producto - producto al que pertenece esta personalizacion
=======
     * $this->attributes['precioAdicional'] - decimal - costo adicional de la opcion
     * $this->producto - Producto - producto al que pertenece la opcion
>>>>>>> bf2db36 (Se actualizaron los modelos para cumplir con las reglas planteadas por el arquitecto)
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

<<<<<<< HEAD
    public function getProducto(): Producto
    {
        return $this->producto;
    }

    // Devuelve el precio base del producto sumado al costo de esta personalizacion.
    public function calcularPrecioTotal(): float
    {
=======
    public function calcularPrecioTotal(): float
    {
>>>>>>> bf2db36 (Se actualizaron los modelos para cumplir con las reglas planteadas por el arquitecto)
        return $this->producto->getPrecio() + $this->attributes['precioAdicional'];
    }
}