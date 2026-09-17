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
        Schema::table('productos', function (Blueprint $table): void {
            $table->decimal('precio', 12, 2)->change();
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table): void {
            $table->decimal('precio', 8, 2)->change();
        });
    }
};
