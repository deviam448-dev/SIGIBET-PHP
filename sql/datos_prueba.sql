-- ============================================================
-- DATOS DE PRUEBA - BELLA TELA
-- Ejecutar este script sobre la BD sigibet_db ya instalada
-- para poblar el catálogo sin reinstalar el sistema.
-- ============================================================
USE sigibet_db;

-- --------------------------------------------------------
-- USUARIOS
-- --------------------------------------------------------
-- Empleado de prueba: usuario=vendedora1 / password=admin123
INSERT IGNORE INTO usuarios (nombre, usuario, email, telefono, password, rol_id, estado) VALUES
('María Camila Ríos', 'vendedora1', 'camila@bellatela.com', '3115550101',
 '$2y$10$LrpiIW3iCGHoeWXn8R4axuONwll/8gEq.kYLgfbEVflhftoqwvcXG', 2, 1);

-- --------------------------------------------------------
-- CONFIGURACIÓN DE LA EMPRESA
-- --------------------------------------------------------
INSERT INTO configuracion (id, nombre_empresa, nit, telefono, email, direccion, impuesto, tipo_facturacion)
VALUES (1, 'Bella Tela', '900.123.456-7', '+57 321 408 8690', 'info@bellatela.com',
        'Algeciras, Huila', 19.00, 'Régimen Simplificado')
ON DUPLICATE KEY UPDATE
    nombre_empresa   = VALUES(nombre_empresa),
    nit              = VALUES(nit),
    telefono         = VALUES(telefono),
    email            = VALUES(email),
    direccion        = VALUES(direccion),
    tipo_facturacion = VALUES(tipo_facturacion);

-- --------------------------------------------------------
-- PRODUCTOS: CATÁLOGO DE TELAS (30 referencias)
-- Precio en COP por metro
-- --------------------------------------------------------
INSERT IGNORE INTO productos (referencia, nombre, color, categoria, descripcion, precio, precio_compra, stock, stock_minimo, estado) VALUES

-- MODA FEMENINA
('TL-001', 'Tela Seda Italiana',      'Marfil',        'Moda Femenina', 'Seda italiana de alta gama, suave al tacto, ideal para blusas y vestidos de noche.',        45000.00, 28000.00,  80, 10, 1),
('TL-002', 'Tela Seda Italiana',      'Negro',         'Moda Femenina', 'Seda italiana de alta gama, suave al tacto, ideal para blusas y vestidos de noche.',        45000.00, 28000.00,  60, 10, 1),
('TL-003', 'Tela Encaje Bordado',     'Blanco Novia',  'Moda Femenina', 'Encaje bordado con flores, perfecto para vestidos de novia y quinceañeras.',                38000.00, 22000.00,  45,  8, 1),
('TL-004', 'Tela Encaje Bordado',     'Nude',          'Moda Femenina', 'Encaje bordado con flores, perfecto para vestidos de novia y quinceañeras.',                38000.00, 22000.00,  40,  8, 1),
('TL-005', 'Tela Gasa Plisada',       'Rosado Palo',   'Moda Femenina', 'Gasa liviana plisada, ideal para faldas voluminosas y vestidos de gala.',                   22000.00, 12000.00, 120, 15, 1),
('TL-006', 'Tela Gasa Plisada',       'Azul Cielo',    'Moda Femenina', 'Gasa liviana plisada, ideal para faldas voluminosas y vestidos de gala.',                   22000.00, 12000.00,  95, 15, 1),
('TL-007', 'Tela Chifón Fluido',      'Coral',         'Moda Femenina', 'Chifón de caída suave, traslúcido, ideal para blusas y sobrevestidos.',                     18000.00, 10000.00, 110, 15, 1),
('TL-008', 'Tela Chifón Fluido',      'Esmeralda',     'Moda Femenina', 'Chifón de caída suave, traslúcido, ideal para blusas y sobrevestidos.',                     18000.00, 10000.00,  85, 15, 1),
('TL-009', 'Tela Popelina Lisa',      'Blanco',        'Moda Femenina', 'Popelina 100% algodón, textura fina, ideal para camisas y vestidos casuales.',               9500.00,  5500.00, 200, 20, 1),
('TL-010', 'Tela Popelina Lisa',      'Azul Marino',   'Moda Femenina', 'Popelina 100% algodón, textura fina, ideal para camisas y vestidos casuales.',               9500.00,  5500.00, 175, 20, 1),
('TL-011', 'Tela Lino Natural',       'Beige',         'Moda Femenina', 'Lino natural transpirable, ideal para conjuntos de verano y ropa casual chic.',             14000.00,  8000.00,  90, 12, 1),
('TL-012', 'Tela Lino Natural',       'Verde Oliva',   'Moda Femenina', 'Lino natural transpirable, ideal para conjuntos de verano y ropa casual chic.',             14000.00,  8000.00,  70, 12, 1),

-- DEPORTIVO
('DP-001', 'Tela Licra Deportiva 4 Vías', 'Negro Carbón', 'Deportivo', 'Licra de alta elasticidad en 4 direcciones, resistente al cloro, para ropa deportiva y trajes de baño.', 28000.00, 16000.00, 150, 20, 1),
('DP-002', 'Tela Licra Deportiva 4 Vías', 'Fucsia',       'Deportivo', 'Licra de alta elasticidad en 4 direcciones, resistente al cloro, para ropa deportiva y trajes de baño.', 28000.00, 16000.00, 120, 20, 1),
('DP-003', 'Tela Dry-Fit Técnica',        'Gris Melange', 'Deportivo', 'Tela técnica de secado rápido, anti-transpirante, ideal para camisetas y shorts deportivos.',           19000.00, 11000.00, 100, 15, 1),
('DP-004', 'Tela Dry-Fit Técnica',        'Azul Royal',   'Deportivo', 'Tela técnica de secado rápido, anti-transpirante, ideal para camisetas y shorts deportivos.',           19000.00, 11000.00,  80, 15, 1),
('DP-005', 'Tela Neopreno 3mm',           'Negro',        'Deportivo', 'Neopreno de 3mm de grosor, ideal para trajes de neopreno, bolsos y accesorios deportivos acuáticos.',   35000.00, 20000.00,  40,  5, 1),

-- HOGAR
('HG-001', 'Tela Cortinería Blackout', 'Gris Plata',    'Hogar', 'Tela blackout de alta densidad, bloquea el 99% de la luz, ideal para cortinas de dormitorio.',  32000.00, 19000.00,  60,  8, 1),
('HG-002', 'Tela Cortinería Blackout', 'Beige Arena',   'Hogar', 'Tela blackout de alta densidad, bloquea el 99% de la luz, ideal para cortinas de dormitorio.',  32000.00, 19000.00,  55,  8, 1),
('HG-003', 'Tela Cretona Estampada',   'Flores Pastel', 'Hogar', 'Cretona 100% algodón con estampado floral, ideal para tapicería ligera, cojines y manteles.',   12000.00,  6500.00, 130, 15, 1),
('HG-004', 'Tela Velvet Tapicería',    'Terracota',     'Hogar', 'Terciopelo corto para tapicería de muebles y cabeceros de cama, resistente al desgaste.',       42000.00, 25000.00,  35,  5, 1),
('HG-005', 'Tela Velvet Tapicería',    'Azul Petróleo', 'Hogar', 'Terciopelo corto para tapicería de muebles y cabeceros de cama, resistente al desgaste.',       42000.00, 25000.00,  30,  5, 1),
('HG-006', 'Tela Impermeable Oxford',  'Negro',         'Hogar', 'Tela Oxford 600D impermeable, ideal para carpas, toldos, bolsos y manteles exteriores.',        16000.00,  9000.00,  75, 10, 1),

-- LENCERÍA
('LC-001', 'Tela Satén Lencero',   'Blanco',      'Lencería', 'Satén de acabado brillante, suave y deslizante, ideal para lencería fina, pijamas y ropa íntima.', 25000.00, 14000.00, 90, 12, 1),
('LC-002', 'Tela Satén Lencero',   'Negro',       'Lencería', 'Satén de acabado brillante, suave y deslizante, ideal para lencería fina, pijamas y ropa íntima.', 25000.00, 14000.00, 80, 12, 1),
('LC-003', 'Tela Satén Lencero',   'Rojo Pasión', 'Lencería', 'Satén de acabado brillante, suave y deslizante, ideal para lencería fina, pijamas y ropa íntima.', 25000.00, 14000.00, 60, 12, 1),
('LC-004', 'Tela Microfibra Suave','Rosado',      'Lencería', 'Microfibra ultra suave tipo seda, ideal para ropa interior femenina y pijamas.',                   20000.00, 11000.00,100, 15, 1),
('LC-005', 'Tela Microfibra Suave','Lila',        'Lencería', 'Microfibra ultra suave tipo seda, ideal para ropa interior femenina y pijamas.',                   20000.00, 11000.00, 85, 15, 1),

-- INDUSTRIAL
('IN-001', 'Tela Denim 10oz',      'Azul Índigo',   'Industrial', 'Denim 100% algodón de 10 onzas, resistente, ideal para jeans, chaquetas y overoles.',               18000.00, 10500.00, 200, 25, 1),
('IN-002', 'Tela Drill Uniformes', 'Kaki',          'Industrial', 'Drill de algodón resistente al desgaste, ideal para uniformes industriales y pantalones de trabajo.', 11000.00,  6000.00, 250, 30, 1),
('IN-003', 'Tela Drill Uniformes', 'Verde Militar', 'Industrial', 'Drill de algodón resistente al desgaste, ideal para uniformes industriales y pantalones de trabajo.', 11000.00,  6000.00, 180, 30, 1);

-- --------------------------------------------------------
-- 100 CLIENTES Y COMPRAS DE PRUEBA
-- Cada compra recibe un cliente anónimo con ID autoincremental.
-- Cada compra contiene una tela y un insumo de costura.
-- --------------------------------------------------------

-- Insumos de costura utilizados por las compras de prueba.
INSERT IGNORE INTO productos
    (referencia, nombre, color, categoria, descripcion, precio, precio_compra, stock, stock_minimo, estado)
VALUES
    ('IC-001', 'Hilo de Coser Poliester', 'Blanco', 'Insumos de Costura', 'Hilo de poliester para confección general.', 4500.00, 2500.00, 300, 30, 1),
    ('IC-002', 'Hilo de Coser Poliester', 'Negro', 'Insumos de Costura', 'Hilo de poliester para confección general.', 4500.00, 2500.00, 300, 30, 1),
    ('IC-003', 'Hilo de Coser Poliester', 'Rojo', 'Insumos de Costura', 'Hilo de poliester para confección general.', 4500.00, 2500.00, 240, 30, 1),
    ('IC-004', 'Cierre Metalico', 'Negro', 'Insumos de Costura', 'Cierre metalico para prendas y bolsos.', 3500.00, 1800.00, 250, 25, 1),
    ('IC-005', 'Cierre Invisible', 'Beige', 'Insumos de Costura', 'Cierre invisible para vestidos y faldas.', 2800.00, 1500.00, 250, 25, 1),
    ('IC-006', 'Boton Plastico Decorativo', 'Rosado', 'Insumos de Costura', 'Boton plastico para prendas y accesorios.', 900.00, 400.00, 500, 50, 1),
    ('IC-007', 'Cinta Elastica', 'Blanco', 'Insumos de Costura', 'Cinta elastica para ropa interior y deportiva.', 2200.00, 1100.00, 200, 20, 1),
    ('IC-008', 'Aguja para Maquina', 'Metal', 'Insumos de Costura', 'Aguja universal para maquina de coser.', 1800.00, 900.00, 180, 20, 1);

-- Cada compra de prueba recibe un cliente anónimo con ID autoincremental.
INSERT INTO clientes (nombre, documento, fecha_creacion)
WITH RECURSIVE numeros AS (
    SELECT 1 AS numero
    UNION ALL
    SELECT numero + 1 FROM numeros WHERE numero < 100
)
SELECT
    NULL,
    NULL,
    DATE_SUB(CURRENT_TIMESTAMP(), INTERVAL (numero - 1) DAY)
FROM numeros;

SET @primer_cliente_prueba = LAST_INSERT_ID();

DROP TEMPORARY TABLE IF EXISTS tmp_clientes_prueba;
CREATE TEMPORARY TABLE tmp_clientes_prueba (
    numero INT PRIMARY KEY,
    cliente_id INT NOT NULL
);

INSERT INTO tmp_clientes_prueba (numero, cliente_id)
WITH RECURSIVE numeros AS (
    SELECT 1 AS numero
    UNION ALL
    SELECT numero + 1 FROM numeros WHERE numero < 100
)
SELECT numero, @primer_cliente_prueba + numero - 1
FROM numeros;

SET @usuario_prueba = (SELECT id FROM usuarios WHERE usuario = 'vendedora1' LIMIT 1);

DROP TEMPORARY TABLE IF EXISTS tmp_compras_prueba;
CREATE TEMPORARY TABLE tmp_compras_prueba AS
SELECT
    c.cliente_id,
    n.numero,
    DATE_SUB(CURRENT_DATE(), INTERVAL (n.numero - 1) DAY)
        + INTERVAL (10 + MOD(n.numero, 8)) HOUR AS fecha,
    CASE MOD(n.numero - 1, 10)
        WHEN 0 THEN 'TL-001'
        WHEN 1 THEN 'TL-003'
        WHEN 2 THEN 'TL-005'
        WHEN 3 THEN 'TL-007'
        WHEN 4 THEN 'TL-009'
        WHEN 5 THEN 'TL-011'
        WHEN 6 THEN 'DP-001'
        WHEN 7 THEN 'HG-003'
        WHEN 8 THEN 'LC-001'
        ELSE 'IN-002'
    END AS tela_ref,
    CONCAT('IC-', LPAD(MOD(n.numero - 1, 8) + 1, 3, '0')) AS insumo_ref,
    2 + MOD(n.numero, 4) AS cantidad_tela,
    1 + MOD(n.numero, 3) AS cantidad_insumo,
    CASE MOD(n.numero, 3)
        WHEN 0 THEN 'Efectivo'
        WHEN 1 THEN 'Transferencia'
        ELSE 'Tarjeta'
    END AS metodo_pago
FROM (
    SELECT 1 AS numero
    UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
    UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10
    UNION ALL SELECT 11 UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL SELECT 15
    UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18 UNION ALL SELECT 19 UNION ALL SELECT 20
    UNION ALL SELECT 21 UNION ALL SELECT 22 UNION ALL SELECT 23 UNION ALL SELECT 24 UNION ALL SELECT 25
    UNION ALL SELECT 26 UNION ALL SELECT 27 UNION ALL SELECT 28 UNION ALL SELECT 29 UNION ALL SELECT 30
    UNION ALL SELECT 31 UNION ALL SELECT 32 UNION ALL SELECT 33 UNION ALL SELECT 34 UNION ALL SELECT 35
    UNION ALL SELECT 36 UNION ALL SELECT 37 UNION ALL SELECT 38 UNION ALL SELECT 39 UNION ALL SELECT 40
    UNION ALL SELECT 41 UNION ALL SELECT 42 UNION ALL SELECT 43 UNION ALL SELECT 44 UNION ALL SELECT 45
    UNION ALL SELECT 46 UNION ALL SELECT 47 UNION ALL SELECT 48 UNION ALL SELECT 49 UNION ALL SELECT 50
    UNION ALL SELECT 51 UNION ALL SELECT 52 UNION ALL SELECT 53 UNION ALL SELECT 54 UNION ALL SELECT 55
    UNION ALL SELECT 56 UNION ALL SELECT 57 UNION ALL SELECT 58 UNION ALL SELECT 59 UNION ALL SELECT 60
    UNION ALL SELECT 61 UNION ALL SELECT 62 UNION ALL SELECT 63 UNION ALL SELECT 64 UNION ALL SELECT 65
    UNION ALL SELECT 66 UNION ALL SELECT 67 UNION ALL SELECT 68 UNION ALL SELECT 69 UNION ALL SELECT 70
    UNION ALL SELECT 71 UNION ALL SELECT 72 UNION ALL SELECT 73 UNION ALL SELECT 74 UNION ALL SELECT 75
    UNION ALL SELECT 76 UNION ALL SELECT 77 UNION ALL SELECT 78 UNION ALL SELECT 79 UNION ALL SELECT 80
    UNION ALL SELECT 81 UNION ALL SELECT 82 UNION ALL SELECT 83 UNION ALL SELECT 84 UNION ALL SELECT 85
    UNION ALL SELECT 86 UNION ALL SELECT 87 UNION ALL SELECT 88 UNION ALL SELECT 89 UNION ALL SELECT 90
    UNION ALL SELECT 91 UNION ALL SELECT 92 UNION ALL SELECT 93 UNION ALL SELECT 94 UNION ALL SELECT 95
    UNION ALL SELECT 96 UNION ALL SELECT 97 UNION ALL SELECT 98 UNION ALL SELECT 99 UNION ALL SELECT 100
) n
JOIN tmp_clientes_prueba c ON c.numero = n.numero;

INSERT IGNORE INTO ventas
    (codigo_factura, usuario_id, cliente_id, subtotal, total, metodo_pago, fecha)
SELECT
    CONCAT('PRUEBA-', LPAD(t.numero, 3, '0')),
    @usuario_prueba,
    t.cliente_id,
    (t.cantidad_tela * tela.precio) + (t.cantidad_insumo * insumo.precio),
    (t.cantidad_tela * tela.precio) + (t.cantidad_insumo * insumo.precio),
    t.metodo_pago,
    t.fecha
FROM tmp_compras_prueba t
JOIN productos tela ON tela.referencia = t.tela_ref
JOIN productos insumo ON insumo.referencia = t.insumo_ref
WHERE @usuario_prueba IS NOT NULL;

INSERT IGNORE INTO detalle_ventas
    (venta_id, producto_id, cantidad, precio_unitario, subtotal)
SELECT
    v.id,
    p.id,
    t.cantidad_tela,
    p.precio,
    t.cantidad_tela * p.precio
FROM tmp_compras_prueba t
JOIN ventas v ON v.codigo_factura = CONCAT('PRUEBA-', LPAD(t.numero, 3, '0'))
JOIN productos p ON p.referencia = t.tela_ref;

INSERT IGNORE INTO detalle_ventas
    (venta_id, producto_id, cantidad, precio_unitario, subtotal)
SELECT
    v.id,
    p.id,
    t.cantidad_insumo,
    p.precio,
    t.cantidad_insumo * p.precio
FROM tmp_compras_prueba t
JOIN ventas v ON v.codigo_factura = CONCAT('PRUEBA-', LPAD(t.numero, 3, '0'))
JOIN productos p ON p.referencia = t.insumo_ref;

DROP TEMPORARY TABLE IF EXISTS tmp_compras_prueba;
DROP TEMPORARY TABLE IF EXISTS tmp_clientes_prueba;
