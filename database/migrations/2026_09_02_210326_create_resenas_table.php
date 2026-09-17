<?php

/**
 * Autor: Samuel Correa Velasquez (Desarrollador)
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resenas', function (Blueprint $table) {
            $table->increments('idResena');
            $table->unsignedInteger('idCliente');
            $table->unsignedInteger('idProducto');
            $table->unsignedTinyInteger('calificacion');
            $table->text('comentario')->nullable();
            $table->boolean('compraVerificada')->default(false);
            $table->date('fecha');
            $table->timestamps();

            $table->foreign('idCliente')
                ->references('idCliente')->on('clientes')
                ->onDelete('cascade');

            $table->foreign('idProducto')
                ->references('idProducto')->on('productos')
                ->onDelete('cascade');
        });

        // SQLite no admite ALTER TABLE ... ADD CONSTRAINT: solo acepta CHECK al
        // crear la tabla. En ese motor la cota 1-5 queda a cargo de la
        // validacion de la aplicacion (ResenaController: min:1, max:5).
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE resenas ADD CONSTRAINT chk_calificacion CHECK (calificacion BETWEEN 1 AND 5)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('resenas');
    }
};
