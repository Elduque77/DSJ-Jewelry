<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->increments('idCliente');
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('correo')->unique();
            $table->string('telefono', 20)->nullable();
            $table->string('direccion')->nullable();
            $table->string('contrasena');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
