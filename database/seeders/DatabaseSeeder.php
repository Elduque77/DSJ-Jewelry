<?php

/**
 * Autor: Diego (Arquitecto)
 */

namespace Database\Seeders;

use App\Models\Administrador;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Administrador::updateOrCreate(
            ['correo' => 'admin1@gmail.com'],
            [
                'nombre' => 'Administrador principal',
                'contrasena' => Hash::make('12345678'),
            ],
        );
    }
}
