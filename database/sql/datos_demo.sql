-- ---------------------------------------------------------------------------
-- DSJ Jewelry - Datos de demostracion (catalogo + clientes + resenas)
--
-- Se puede ejecutar varias veces sin duplicar nada: cada INSERT lleva un
-- NOT EXISTS que descarta las filas que ya estan en la base.
--
-- Como ejecutarlo:
--   phpMyAdmin  -> pestana "Importar" (o pegar en "SQL") sobre la base dsj
--   Terminal    -> docker exec -i dsj-mysql-1 mysql -usail -ppassword dsj < database/sql/datos_demo.sql
--
-- La contrasena de todos los clientes de ejemplo es: 12345678
-- ---------------------------------------------------------------------------

SET NAMES utf8mb4;

START TRANSACTION;

-- ---------------------------------------------------------------------------
-- 1. Categorias
--    categorias.nombre no tiene indice unico, por eso el control es NOT EXISTS
--    y no ON DUPLICATE KEY.
-- ---------------------------------------------------------------------------
INSERT INTO categorias (nombre, descripcion, created_at, updated_at)
SELECT d.nombre, d.descripcion, NOW(), NOW()
FROM (
    SELECT 'Anillos'  AS nombre, 'Anillos artesanales en plata y oro.'            AS descripcion
    UNION ALL SELECT 'Collares', 'Collares y cadenas tejidas a mano.'
    UNION ALL SELECT 'Aretes',   'Aretes y topos para uso diario y ocasion.'
    UNION ALL SELECT 'Pulseras', 'Pulseras en plata, oro y tejido andino.'
    UNION ALL SELECT 'Dijes',    'Dijes y charms intercambiables.'
) AS d
WHERE NOT EXISTS (
    SELECT 1 FROM categorias c WHERE c.nombre = d.nombre
);

-- ---------------------------------------------------------------------------
-- 2. Productos
--    La categoria se resuelve por nombre, asi el script no depende de los ids
--    que tenga cada quien en su base local.
-- ---------------------------------------------------------------------------
INSERT INTO productos (idCategoria, nombre, descripcion, material, precio, stock, created_at, updated_at)
SELECT c.idCategoria, d.nombre, d.descripcion, d.material, d.precio, d.stock, NOW(), NOW()
FROM (
    SELECT 'Anillos'  AS categoria, 'Anillo Luna'            AS nombre, 'Anillo de plata con acabado satinado.'              AS descripcion, 'Plata 925'        AS material,  180000.00 AS precio, 12 AS stock
    UNION ALL SELECT 'Anillos',     'Anillo Solsticio',      'Anillo de oro rosa con circon central.',                       'Oro rosa 18k',      940000.00,  4
    UNION ALL SELECT 'Anillos',     'Anillo Eclipse',        'Anillo de banda ancha con grabado en relieve.',                'Plata 925',         215000.00,  9
    UNION ALL SELECT 'Anillos',     'Anillo Aurora',         'Anillo solitario con piedra natural engastada.',               'Oro amarillo 18k', 1120000.00,  3
    UNION ALL SELECT 'Collares',    'Collar Via Lactea',     'Collar con cadena fina y dije de constelacion.',               'Plata 925',         240000.00, 15
    UNION ALL SELECT 'Collares',    'Collar Cascada',        'Collar de eslabones escalonados en oro rosa.',                 'Oro rosa 18k',      880000.00,  5
    UNION ALL SELECT 'Collares',    'Collar Perla Nordica',  'Collar corto con perla de agua dulce.',                        'Plata 925',         195000.00, 10
    UNION ALL SELECT 'Aretes',      'Aretes Gota de Rocio',  'Aretes colgantes en forma de gota pulida.',                    'Plata 925',         135000.00, 20
    UNION ALL SELECT 'Aretes',      'Aretes Sol Naciente',   'Topos circulares con acabado martillado.',                     'Oro amarillo 18k',  760000.00,  6
    UNION ALL SELECT 'Aretes',      'Aretes Hoja de Olivo',  'Aretes largos con hojas texturizadas.',                        'Plata 925',         158000.00, 14
    UNION ALL SELECT 'Pulseras',    'Pulsera Trenza Andina', 'Pulsera tejida a mano con cierre ajustable.',                  'Plata 925',         172000.00, 11
    UNION ALL SELECT 'Pulseras',    'Pulsera Eslabon Real',  'Pulsera de eslabon grueso con broche de seguridad.',           'Oro blanco 18k',   1350000.00,  2
    UNION ALL SELECT 'Dijes',       'Dije Corazon Sereno',   'Dije en forma de corazon con borde pulido.',                   'Plata 925',          89000.00, 25
    UNION ALL SELECT 'Dijes',       'Dije Ancla',            'Dije de ancla resistente al agua.',                            'Acero quirurgico',   65000.00, 30
) AS d
JOIN categorias c ON c.nombre = d.categoria
WHERE NOT EXISTS (
    SELECT 1 FROM productos p WHERE p.nombre = d.nombre
);

-- ---------------------------------------------------------------------------
-- 3. Clientes
--    El correo si es unico en la tabla, pero se usa el mismo patron para no
--    gastar ids del AUTO_INCREMENT en intentos fallidos.
--    Hash bcrypt de la contrasena 12345678.
-- ---------------------------------------------------------------------------
INSERT INTO clientes (nombre, apellido, correo, telefono, direccion, contrasena, created_at, updated_at)
SELECT d.nombre, d.apellido, d.correo, d.telefono, d.direccion,
       '$2y$12$rgEZpI8f8j4GlkH/bN3bEev7p.GbqGcj3opUtrWCTuiLK2GuwuT9a', NOW(), NOW()
FROM (
    SELECT 'Laura'     AS nombre, 'Gomez'   AS apellido, 'laura@gmail.com'     AS correo, '3001112233' AS telefono, 'Calle 12 #4-56, Bogota'     AS direccion
    UNION ALL SELECT 'Andres',    'Rojas',   'andres@gmail.com',    '3012223344', 'Carrera 30 #45-12, Bogota'
    UNION ALL SELECT 'Camila',    'Herrera', 'camila@gmail.com',    '3023334455', 'Calle 80 #20-10, Medellin'
    UNION ALL SELECT 'Julian',    'Mora',    'julian@gmail.com',    '3034445566', 'Avenida 6 #15-40, Cali'
    UNION ALL SELECT 'Valentina', 'Rios',    'valentina@gmail.com', '3045556677', 'Carrera 7 #72-30, Bogota'
    UNION ALL SELECT 'Mateo',     'Castro',  'mateo@gmail.com',     '3056667788', 'Calle 50 #33-21, Barranquilla'
) AS d
WHERE NOT EXISTS (
    SELECT 1 FROM clientes cl WHERE cl.correo = d.correo
);

-- ---------------------------------------------------------------------------
-- 4. Resenas
--    La tabla tiene un indice unico (idCliente, idProducto): un cliente solo
--    puede resenar una vez cada producto. calificacion va de 1 a 5 por el
--    CHECK chk_calificacion.
-- ---------------------------------------------------------------------------
INSERT INTO resenas (idCliente, idProducto, calificacion, comentario, compraVerificada, fecha, created_at, updated_at)
SELECT cl.idCliente, p.idProducto, d.calificacion, d.comentario, d.compraVerificada, d.fecha, NOW(), NOW()
FROM (
    SELECT 'laura@gmail.com'     AS correo, 'Collar Via Lactea'     AS producto, 5 AS calificacion, 'Hermoso acabado, llego muy bien empacado.'                      AS comentario, 1 AS compraVerificada, '2026-09-02' AS fecha
    UNION ALL SELECT 'laura@gmail.com',     'Aretes Gota de Rocio',  4, 'Livianos y comodos para todo el dia.',                          1, '2026-09-04'
    UNION ALL SELECT 'andres@gmail.com',    'Anillo Eclipse',        5, 'El grabado se ve mucho mejor en persona.',                      1, '2026-09-05'
    UNION ALL SELECT 'andres@gmail.com',    'Pulsera Trenza Andina', 3, 'Bonita, pero el cierre queda un poco flojo.',                   0, '2026-09-06'
    UNION ALL SELECT 'camila@gmail.com',    'Collar Cascada',        5, 'Vale cada peso, el oro rosa es precioso.',                      1, '2026-09-07'
    UNION ALL SELECT 'camila@gmail.com',    'Anillo Aurora',         4, 'La piedra es espectacular, la talla quedo justa.',              1, '2026-09-08'
    UNION ALL SELECT 'julian@gmail.com',    'Dije Corazon Sereno',   4, 'Sencillo y bien terminado, buen regalo.',                       1, '2026-09-09'
    UNION ALL SELECT 'julian@gmail.com',    'Aretes Sol Naciente',   2, 'Se ven bien pero llegaron con una marca.',                      0, '2026-09-10'
    UNION ALL SELECT 'valentina@gmail.com', 'Collar Perla Nordica',  5, 'La perla tiene un brillo increible.',                           1, '2026-09-11'
    UNION ALL SELECT 'valentina@gmail.com', 'Pulsera Eslabon Real',  4, 'Pesada y solida, se siente de calidad.',                        0, '2026-09-12'
    UNION ALL SELECT 'mateo@gmail.com',     'Aretes Hoja de Olivo',  3, 'Cumplen, aunque esperaba mas tamano.',                          0, '2026-09-13'
    UNION ALL SELECT 'mateo@gmail.com',     'Dije Ancla',            5, 'Lo uso a diario y no se ha opacado.',                           1, '2026-09-14'
) AS d
JOIN clientes  cl ON cl.correo = d.correo
JOIN productos p  ON p.nombre  = d.producto
WHERE NOT EXISTS (
    SELECT 1 FROM resenas r
    WHERE r.idCliente = cl.idCliente AND r.idProducto = p.idProducto
);

COMMIT;

-- ---------------------------------------------------------------------------
-- 5. Verificacion
-- ---------------------------------------------------------------------------
SELECT 'categorias' AS tabla, COUNT(*) AS filas FROM categorias
UNION ALL SELECT 'productos', COUNT(*) FROM productos
UNION ALL SELECT 'clientes',  COUNT(*) FROM clientes
UNION ALL SELECT 'resenas',   COUNT(*) FROM resenas;
