<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    /**
     * ATRIBUTOS DE PRODUCTO
     * $this->attributes['idProducto'] - int - clave primaria del producto
     * $this->attributes['idCategoria'] - int - categoria a la que pertenece
     * $this->attributes['nombre'] - string - nombre del producto
     * $this->attributes['descripcion'] - string - descripcion del producto
<<<<<<< HEAD
     * $this->attributes['material'] - string - material principal del producto
     * $this->attributes['precio'] - float - precio unitario sin personalizaciones
     * $this->attributes['stock'] - int - unidades disponibles
     * $this->categoria - Categoria - categoria del producto
=======
     * $this->attributes['material'] - string - material del producto
     * $this->attributes['precio'] - decimal - precio unitario sin personalizaciones
     * $this->attributes['stock'] - int - unidades disponibles
>>>>>>> bf2db36 (Se actualizaron los modelos para cumplir con las reglas planteadas por el arquitecto)
     * $this->personalizaciones - Collection - opciones de personalizacion asociadas
     */
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
        return $this->attributes['idProducto'];
    }

    public function getNombre(): string
    {
        return $this->attributes['nombre'];
    }

    public function setNombre(string $nombre): void
    {
        $this->attributes['nombre'] = $nombre;
    }

    public function getDescripcion(): string
    {
        return $this->attributes['descripcion'];
    }

    public function setDescripcion(string $descripcion): void
    {
        $this->attributes['descripcion'] = $descripcion;
    }

    public function getMaterial(): string
    {
        return $this->attributes['material'];
    }

    public function setMaterial(string $material): void
    {
        $this->attributes['material'] = $material;
    }

    public function getPrecio(): float
    {
        return $this->attributes['precio'];
    }

    public function setPrecio(float $precio): void
    {
        $this->attributes['precio'] = $precio;
    }

    public function getStock(): int
    {
        return $this->attributes['stock'];
    }

    public function setStock(int $stock): void
    {
        $this->attributes['stock'] = $stock;
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'idCategoria', 'idCategoria');
    }

    public function getCategoria(): Categoria
    {
        return $this->categoria;
    }

    public function personalizaciones(): HasMany
    {
        return $this->hasMany(Personalizacion::class, 'idProducto', 'idProducto');
    }

    public function getPersonalizaciones(): Collection
    {
        return $this->personalizaciones;
    }

    public function consultarDisponibilidad(): bool
    {
        return $this->attributes['stock'] > 0;
    }

    public function actualizarStock(int $cantidad): void
    {
        $this->attributes['stock'] -= $cantidad;
        $this->save();
    }
}