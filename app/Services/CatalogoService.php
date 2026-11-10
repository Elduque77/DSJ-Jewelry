<?php

namespace App\Services;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Collection;

class CatalogoService
{
    public function obtenerProductos(bool $soloDisponibles = false): Collection
    {
        return Producto::with(['categoria', 'resenas'])
            ->when($soloDisponibles, fn ($query) => $query->where('stock', '>', 0))
            ->get();
    }
}
