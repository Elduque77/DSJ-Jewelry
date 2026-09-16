<?php

/**
 * Autor: Juan Fernando Duque (Desarrollador)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DetalleCompra extends Model
{
    /**
     * ATRIBUTOS DE DETALLE_COMPRA
     * $this->attributes['idDetalle'] - int - clave primaria de la linea de compra
     * $this->attributes['idCompra'] - int - compra a la que pertenece esta linea
     * $this->attributes['idProducto'] - int - producto comprado en esta linea
     * $this->attributes['cantidad'] - int - unidades compradas (siempre 1 si tiene personalizacion)
     * $this->attributes['precioUnitario'] - float - precio del producto congelado al momento de la compra
     * $this->compra - Compra - compra a la que pertenece esta linea
     * $this->producto - Producto - producto comprado
     * $this->detallePersonalizaciones - Collection - personalizaciones elegidas para esta linea
     */
    public $table = 'detalle_compras';

    public $primaryKey = 'idDetalle';

    public $fillable = [
        'idCompra',
        'idProducto',
        'cantidad',
        'precioUnitario',
    ];

    public function getIdDetalle(): int
    {
        return $this->attributes['idDetalle'];
    }

    public function getIdCompra(): int
    {
        return $this->attributes['idCompra'];
    }

    public function setIdCompra(int $idCompra): void
    {
        $this->attributes['idCompra'] = $idCompra;
    }

    public function getIdProducto(): int
    {
        return $this->attributes['idProducto'];
    }

    public function setIdProducto(int $idProducto): void
    {
        $this->attributes['idProducto'] = $idProducto;
    }

    public function getCantidad(): int
    {
        return $this->attributes['cantidad'];
    }

    public function setCantidad(int $cantidad): void
    {
        $this->attributes['cantidad'] = $cantidad;
    }

    public function getPrecioUnitario(): float
    {
        return $this->attributes['precioUnitario'];
    }

    public function setPrecioUnitario(float $precioUnitario): void
    {
        $this->attributes['precioUnitario'] = $precioUnitario;
    }

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'idCompra', 'idCompra');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'idProducto', 'idProducto');
    }

    public function getProducto(): Producto
    {
        return $this->producto;
    }

    public function detallePersonalizaciones(): HasMany
    {
        return $this->hasMany(DetallePersonalizacion::class, 'idDetalle', 'idDetalle');
    }

    public function getDetallePersonalizaciones(): Collection
    {
        return $this->detallePersonalizaciones;
    }

    // Regla del dominio: una linea con personalizacion asociada es siempre
    // unitaria. Si el cliente quiere varias unidades personalizadas, se crean
    // varias lineas de detalle, no se sube la cantidad de esta.
    public function agregarPersonalizacion(DetallePersonalizacion $detallePersonalizacion): void
    {
        $this->attributes['cantidad'] = 1;
        $this->save();

        $detallePersonalizacion->setIdDetalle($this->attributes['idDetalle']);
        $detallePersonalizacion->save();
    }

    // Precio del producto por la cantidad, mas el costo de cada
    // personalizacion aplicada a esta linea.
    public function calcularSubtotal(): float
    {
        $costoPersonalizaciones = $this->detallePersonalizaciones()
            ->get()
            ->sum(fn (DetallePersonalizacion $detalle) => $detalle->getPrecioAplicado());

        return ($this->attributes['precioUnitario'] * $this->attributes['cantidad']) + $costoPersonalizaciones;
    }
}
