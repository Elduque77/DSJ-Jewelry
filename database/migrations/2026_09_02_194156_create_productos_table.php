<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->increments('idProducto');
            $table->unsignedInteger('idCategoria');
            $table->string('nombre');
            $table->string('descripcion');
            $table->string('material');
            $table->decimal('precio', 8, 2);
            $table->integer('stock');
            $table->timestamps();

            $table->foreign('idCategoria')
                ->references('idCategoria')->on('categorias')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
