<?php

/**
 * Autor: Juan Fernando Duque (Desarrollador)
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_compras', function (Blueprint $table) {
            $table->increments('idDetalle');
            $table->unsignedInteger('idCompra');
            $table->unsignedInteger('idProducto');
            $table->unsignedInteger('cantidad');
            $table->decimal('precioUnitario', 12, 2);
            $table->timestamps();

            $table->foreign('idCompra')
                ->references('idCompra')->on('compras')
                ->onDelete('cascade');

            // A diferencia de otras relaciones del proyecto, aqui NO se usa cascade:
            // borrar un producto no debe borrar el historial de compras ya realizadas.
            $table->foreign('idProducto')
                ->references('idProducto')->on('productos')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_compras');
    }
};
