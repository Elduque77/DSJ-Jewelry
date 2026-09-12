<?php

/**
 * Autor: Diego (Arquitecto)
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Un cliente solo puede dejar una resena por producto. La regla se valida
    // tambien en el controlador, esto es la ultima defensa en base de datos.
    public function up(): void
    {
        Schema::table('resenas', function (Blueprint $table): void {
            $table->unique(['idCliente', 'idProducto'], 'resenas_cliente_producto_unique');
        });
    }

    public function down(): void
    {
        Schema::table('resenas', function (Blueprint $table): void {
            $table->dropUnique('resenas_cliente_producto_unique');
        });
    }
};
