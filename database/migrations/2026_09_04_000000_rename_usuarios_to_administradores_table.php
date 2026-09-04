<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('usuarios', 'administradores');

        Schema::table('administradores', function (Blueprint $table): void {
            $table->renameColumn('idUsuario', 'idAdministrador');
        });
    }

    public function down(): void
    {
        Schema::table('administradores', function (Blueprint $table): void {
            $table->renameColumn('idAdministrador', 'idUsuario');
        });

        Schema::rename('administradores', 'usuarios');
    }
};
