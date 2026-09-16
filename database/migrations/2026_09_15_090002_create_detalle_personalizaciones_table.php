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
        Schema::create('detalle_personalizaciones', function (Blueprint $table) {
            $table->increments('idDetallePersonalizacion');
            $table->unsignedInteger('idDetalle');
            $table->unsignedInteger('idPersonalizacion');
            $table->string('descripcion');
            $table->decimal('precioAplicado', 12, 2);
            $table->timestamps();

            $table->foreign('idDetalle')
                ->references('idDetalle')->on('detalle_compras')
                ->onDelete('cascade');

            // Igual que con productos: no se borra en cascada para no perder
            // el detalle de personalizaciones ya vendidas.
            $table->foreign('idPersonalizacion')
                ->references('idPersonalizacion')->on('personalizaciones')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_personalizaciones');
    }
};
