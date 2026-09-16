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
        Schema::create('compras', function (Blueprint $table) {
            $table->increments('idCompra');
            $table->unsignedInteger('idCliente');
            $table->date('fecha');
            $table->string('estado', 20)->default('pendiente');
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamps();

            $table->foreign('idCliente')
                ->references('idCliente')->on('clientes')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};
