<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    /**
     * ATRIBUTOS DE CATEGORIA
     * $this->attributes['idCategoria'] - int - clave primaria de la categoria
     * $this->attributes['nombre'] - string - nombre de la categoria
     * $this->attributes['descripcion'] - string - descripcion de la categoria
<<<<<<< HEAD
     * $this->productos - Collection - productos pertenecientes a la categoria
=======
     * $this->productos - Collection - productos que pertenecen a la categoria
>>>>>>> bf2db36 (Se actualizaron los modelos para cumplir con las reglas planteadas por el arquitecto)
     */
    public $primaryKey = 'idCategoria';

    public $fillable = [
        'nombre',
        'descripcion',
    ];

    public function getIdCategoria(): int
    {
        return $this->attributes['idCategoria'];
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

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'idCategoria', 'idCategoria');
    }

    public function getProductos(): Collection
    {
        return $this->productos;
    }

    public function listarProductos(): Collection
    {
        return $this->productos()->get();
    }
}