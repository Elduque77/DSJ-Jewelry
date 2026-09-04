<?php

/**
 * Autor: Samuel Correa Velasquez (Desarrollador)
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personalizaciones', function (Blueprint $table) {
            $table->increments('idPersonalizacion');
            $table->unsignedInteger('idProducto');
            $table->string('nombreOpcion');
            $table->decimal('precioAdicional', 8, 2);
            $table->timestamps();

            $table->foreign('idProducto')
                ->references('idProducto')->on('productos')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personalizaciones');
    }
};
