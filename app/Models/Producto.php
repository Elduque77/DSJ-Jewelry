<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    public $primaryKey = 'idProducto';

    public $fillable = [
        'idCategoria',
        'nombre',
        'descripcion',
        'material',
        'precio',
        'stock',
    ];

    public function getIdProducto(): int
    {
        return $this->idProducto;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): void
    {
        $this->nombre = $nombre;
    }

    public function getDescripcion(): string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): void
    {
        $this->descripcion = $descripcion;
    }

    public function getMaterial(): string
    {
        return $this->material;
    }

    public function setMaterial(string $material): void
    {
        $this->material = $material;
    }

    public function getPrecio(): float
    {
        return $this->precio;
    }

    public function setPrecio(float $precio): void
    {
        $this->precio = $precio;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function setStock(int $stock): void
    {
        $this->stock = $stock;
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'idCategoria', 'idCategoria');
    }

    public function personalizaciones(): HasMany
    {
        return $this->hasMany(Personalizacion::class, 'idProducto', 'idProducto');
    }

    public function consultarDisponibilidad(): bool
    {
        return $this->stock > 0;
    }

    public function actualizarStock(int $cantidad): void
    {
        $this->stock -= $cantidad;
        $this->save();
    }
}