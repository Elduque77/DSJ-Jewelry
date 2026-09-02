<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    public $primaryKey = 'idCategoria';

    public $fillable = [
        'nombre',
        'descripcion',
    ];
    public function getIdCategoria(): int
    {
        return $this->idCategoria;
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

    // --- Definir cómo se conecta con Producto

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'idCategoria', 'idCategoria');
    }

    // --- Exponer la operación de negocio 

    public function listarProductos(): Collection
    {
        return $this->productos()->get();
    }
}