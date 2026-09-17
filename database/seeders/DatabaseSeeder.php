<?php

/**
 * Autor: Diego (Arquitecto)
 */

namespace Database\Seeders;

use App\Models\Administrador;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Resena;
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

        $this->sembrarCatalogo();
    }

    // Catalogo minimo de demostracion para poder probar las resenas. Todo va
    // con updateOrCreate para que el seeder se pueda ejecutar varias veces.
    private function sembrarCatalogo(): void
    {
        $categoria = Categoria::updateOrCreate(
            ['nombre' => 'Anillos'],
            ['descripcion' => 'Anillos artesanales en plata y oro.'],
        );

        $productos = [
            [
                'nombre' => 'Anillo Luna',
                'descripcion' => 'Anillo de plata con acabado satinado.',
                'material' => 'Plata 925',
                'precio' => 180000.00,
                'stock' => 12,
            ],
            [
                'nombre' => 'Anillo Solsticio',
                'descripcion' => 'Anillo de oro rosa con circon central.',
                'material' => 'Oro rosa 18k',
                'precio' => 940000.00,
                'stock' => 4,
            ],
        ];

        foreach ($productos as $datos) {
            Producto::updateOrCreate(
                ['nombre' => $datos['nombre']],
                $datos + ['idCategoria' => $categoria->getIdCategoria()],
            );
        }

        $clientes = [
            ['nombre' => 'Laura', 'apellido' => 'Gomez', 'correo' => 'laura@gmail.com'],
            ['nombre' => 'Andres', 'apellido' => 'Rojas', 'correo' => 'andres@gmail.com'],
        ];

        foreach ($clientes as $datos) {
            Cliente::updateOrCreate(
                ['correo' => $datos['correo']],
                $datos + ['contrasena' => Hash::make('12345678')],
            );
        }

        $anillo = Producto::where('nombre', 'Anillo Luna')->firstOrFail();

        $resenas = [
            ['correo' => 'laura@gmail.com', 'calificacion' => 5, 'comentario' => 'Hermoso acabado, llego muy bien empacado.'],
            ['correo' => 'andres@gmail.com', 'calificacion' => 4, 'comentario' => 'Muy buena calidad, aunque tardo unos dias.'],
        ];

        foreach ($resenas as $datos) {
            $cliente = Cliente::where('correo', $datos['correo'])->firstOrFail();

            // La clave de busqueda es (idCliente, idProducto), que es el indice
            // unico de la tabla, asi el seeder no choca al repetirse.
            Resena::updateOrCreate(
                [
                    'idCliente' => $cliente->getIdCliente(),
                    'idProducto' => $anillo->getIdProducto(),
                ],
                [
                    'calificacion' => $datos['calificacion'],
                    'comentario' => $datos['comentario'],
                    'compraVerificada' => false,
                    'fecha' => now()->toDateString(),
                ],
            );
        }
    }
}
