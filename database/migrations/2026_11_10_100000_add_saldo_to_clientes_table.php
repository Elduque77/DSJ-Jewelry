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
        Schema::table('clientes', function (Blueprint $table) {
            // Dinero simulado disponible para pagar. Cada cliente arranca con 5.000.000.
            $table->decimal('saldo', 12, 2)->default(5000000);
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn('saldo');
        });
    }
};
