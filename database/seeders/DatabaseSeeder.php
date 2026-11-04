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
        $categorias = [
            'Anillos' => 'Anillos artesanales en plata y oro.',
            'Collares' => 'Collares elegantes para cualquier ocasión.',
            'Pulseras' => 'Pulseras artesanales con acabados delicados.',
            'Aretes' => 'Aretes contemporáneos para complementar tu estilo.',
        ];

        $categoriasCreadas = [];
        foreach ($categorias as $nombre => $descripcion) {
            $categoriasCreadas[$nombre] = Categoria::updateOrCreate(
                ['nombre' => $nombre],
                ['descripcion' => $descripcion],
            );
        }

        $productos = [
            [
                'nombre' => 'Anillo Luna',
                'descripcion' => 'Anillo de plata con acabado satinado.',
                'material' => 'Plata 925',
                'precio' => 180000.00,
                'stock' => 12,
                'imagen' => 'products/anillo-luna.png',
                'categoria' => 'Anillos',
            ],
            [
                'nombre' => 'Anillo Solsticio',
                'descripcion' => 'Anillo de oro rosa con circon central.',
                'material' => 'Oro rosa 18k',
                'precio' => 940000.00,
                'stock' => 4,
                'imagen' => 'products/anillo-solsticio.png',
                'categoria' => 'Anillos',
            ],
            [
                'nombre' => 'Collar Aurora',
                'descripcion' => 'Collar delicado con dije circular minimalista.',
                'material' => 'Plata 925',
                'precio' => 265000.00,
                'stock' => 8,
                'imagen' => 'products/collar-aurora.png',
                'categoria' => 'Collares',
            ],
            [
                'nombre' => 'Collar Constelación',
                'descripcion' => 'Collar con detalles de estrellas y acabado brillante.',
                'material' => 'Oro laminado 18k',
                'precio' => 520000.00,
                'stock' => 6,
                'imagen' => 'products/collar-constelacion.png',
                'categoria' => 'Collares',
            ],
            [
                'nombre' => 'Pulsera Esencia',
                'descripcion' => 'Pulsera de eslabones finos con cierre seguro.',
                'material' => 'Acero inoxidable dorado',
                'precio' => 155000.00,
                'stock' => 10,
                'imagen' => 'products/pulsera-esencia.png',
                'categoria' => 'Pulseras',
            ],
            [
                'nombre' => 'Pulsera Alba',
                'descripcion' => 'Pulsera ajustable con pequeñas piedras decorativas.',
                'material' => 'Plata 925',
                'precio' => 210000.00,
                'stock' => 7,
                'imagen' => 'products/pulsera-alba.png',
                'categoria' => 'Pulseras',
            ],
            [
                'nombre' => 'Pulsera Nébula',
                'descripcion' => 'Pulsera de cadena fina con dije de estrella.',
                'material' => 'Oro laminado 18k',
                'precio' => 285000.00,
                'stock' => 6,
                'imagen' => 'products/pulsera-nebula.png',
                'categoria' => 'Pulseras',
            ],
            [
                'nombre' => 'Aretes Brisa',
                'descripcion' => 'Aretes ligeros con diseño geométrico.',
                'material' => 'Oro laminado 18k',
                'precio' => 230000.00,
                'stock' => 9,
                'imagen' => 'products/aretes-brisa.png',
                'categoria' => 'Aretes',
            ],
            [
                'nombre' => 'Aretes Eclipse',
                'descripcion' => 'Aretes de diseño circular con acabado pulido.',
                'material' => 'Plata 925',
                'precio' => 195000.00,
                'stock' => 5,
                'imagen' => 'products/aretes-eclipse.png',
                'categoria' => 'Aretes',
            ],
            [
                'nombre' => 'Dije Renacer',
                'descripcion' => 'Dije independiente con símbolo de renovación.',
                'material' => 'Plata 925',
                'precio' => 125000.00,
                'stock' => 11,
                'imagen' => 'products/dije-renacer.png',
                'categoria' => 'Collares',
            ],
        ];

        foreach ($productos as $datos) {
            $categoria = $datos['categoria'];
            unset($datos['categoria']);

            Producto::updateOrCreate(
                ['nombre' => $datos['nombre']],
                $datos + [
                    'idCategoria' => $categoriasCreadas[$categoria]->getIdCategoria(),
                    'imagen' => $datos['imagen'] ?? Producto::IMAGEN_PREDETERMINADA,
                ],
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
