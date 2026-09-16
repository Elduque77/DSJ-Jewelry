<?php

/**
 * Autor: Juan Fernando Duque (Desarrollador)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;

class Compra extends Model
{
    /**
     * ATRIBUTOS DE COMPRA
     * $this->attributes['idCompra'] - int - clave primaria de la compra
     * $this->attributes['idCliente'] - int - cliente que realizo la compra
     * $this->attributes['fecha'] - string - fecha de la compra, formato Y-m-d
     * $this->attributes['estado'] - string - una de ESTADOS_VALIDOS
     * $this->attributes['total'] - float - suma de los subtotales de cada detalle
     * $this->cliente - Cliente - cliente que realizo la compra
     * $this->detalleCompras - Collection - lineas de producto de esta compra
     */
    public $table = 'compras';

    public $primaryKey = 'idCompra';

    public $fillable = [
        'idCliente',
        'fecha',
        'estado',
        'total',
    ];

    // Flujo de estados que puede tener una compra. cambiarEstado() no deja
    // guardar nada fuera de esta lista.
    public const ESTADOS_VALIDOS = ['pendiente', 'pagada', 'enviada', 'cancelada'];

    public function getIdCompra(): int
    {
        return $this->attributes['idCompra'];
    }

    public function getIdCliente(): int
    {
        return $this->attributes['idCliente'];
    }

    public function getFecha(): string
    {
        return $this->attributes['fecha'];
    }

    public function getEstado(): string
    {
        return $this->attributes['estado'];
    }

    public function getTotal(): float
    {
        return $this->attributes['total'];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'idCliente', 'idCliente');
    }

    public function getCliente(): Cliente
    {
        return $this->cliente;
    }

    public function detalleCompras(): HasMany
    {
        return $this->hasMany(DetalleCompra::class, 'idCompra', 'idCompra');
    }

    public function getDetalleCompras(): Collection
    {
        return $this->detalleCompras;
    }

    // Punto de entrada para iniciar una compra: la deja en "pendiente" y en
    // $0, lista para que se le agreguen lineas con agregarDetalle().
    public static function crearCompra(int $idCliente): self
    {
        return self::create([
            'idCliente' => $idCliente,
            'fecha' => now()->toDateString(),
            'estado' => 'pendiente',
            'total' => 0,
        ]);
    }

    // Asocia una linea de detalle (ya con producto, cantidad y precio
    // definidos) a esta compra y deja el total sincronizado.
    public function agregarDetalle(DetalleCompra $detalle): void
    {
        $detalle->setIdCompra($this->attributes['idCompra']);
        $detalle->save();

        $this->calcularTotal();
    }

    // Recorre las lineas ya guardadas, suma sus subtotales y persiste el
    // resultado. Se llama automaticamente desde agregarDetalle().
    public function calcularTotal(): float
    {
        $total = $this->detalleCompras()
            ->get()
            ->sum(fn (DetalleCompra $detalle) => $detalle->calcularSubtotal());

        $this->attributes['total'] = $total;
        $this->save();

        return $total;
    }

    // Cambia el estado validando que sea uno de los permitidos por el flujo
    // de compra, para no dejar guardar un estado inventado por error.
    public function cambiarEstado(string $nuevoEstado): void
    {
        if (! in_array($nuevoEstado, self::ESTADOS_VALIDOS, true)) {
            throw new InvalidArgumentException("Estado de compra invalido: {$nuevoEstado}");
        }

        $this->attributes['estado'] = $nuevoEstado;
        $this->save();
    }
}
