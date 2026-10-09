CREATE DATABASE IF NOT EXISTS sigibet_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sigibet_db;

CREATE TABLE IF NOT EXISTS roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL UNIQUE,
  descripcion VARCHAR(255) NULL,
  estado TINYINT DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  usuario VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(100) NULL,
  telefono VARCHAR(30) NULL,
  password VARCHAR(255) NOT NULL,
  rol_id INT NOT NULL DEFAULT 2,
  intentos_fallidos INT DEFAULT 0,
  bloqueado_hasta DATETIME NULL,
  estado TINYINT DEFAULT 1,
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS productos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  referencia VARCHAR(50) NOT NULL UNIQUE,
  nombre VARCHAR(120) NOT NULL,
  color VARCHAR(50) NOT NULL DEFAULT 'Palo Rosa',
  categoria VARCHAR(80) NOT NULL DEFAULT 'Moda Femenina',
  descripcion TEXT NULL,
  imagen VARCHAR(255) NULL,
  precio DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  precio_compra DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  stock INT NOT NULL DEFAULT 0,
  stock_minimo INT NOT NULL DEFAULT 5,
  estado TINYINT DEFAULT 1,
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS clientes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NULL,
  documento VARCHAR(30) NULL UNIQUE,
  telefono VARCHAR(30) NULL,
  email VARCHAR(100) NULL,
  direccion VARCHAR(150) NULL,
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ventas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  codigo_factura VARCHAR(30) NOT NULL UNIQUE,
  usuario_id INT NOT NULL,
  cliente_id INT NULL,
  subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  total DECIMAL(10,2) NOT NULL,
  metodo_pago VARCHAR(50) DEFAULT 'Efectivo',
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS detalle_ventas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  venta_id INT NOT NULL,
  producto_id INT NOT NULL,
  cantidad INT NOT NULL,
  precio_unitario DECIMAL(10,2) NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE,
  FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS movimientos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  producto_id INT NOT NULL,
  usuario_id INT NOT NULL,
  tipo ENUM('ENTRADA', 'SALIDA_VENTA', 'AJUSTE', 'CREACION') NOT NULL,
  cantidad INT NOT NULL,
  stock_anterior INT NOT NULL,
  stock_nuevo INT NOT NULL,
  motivo VARCHAR(255) NOT NULL,
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS notificaciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  producto_id INT NOT NULL,
  usuario_id INT NOT NULL,
  mensaje VARCHAR(255) NOT NULL,
  leida TINYINT DEFAULT 0,
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS configuracion (
  id INT PRIMARY KEY DEFAULT 1,
  nombre_empresa VARCHAR(100) DEFAULT 'SIGIBET Boutique',
  nit VARCHAR(30) DEFAULT '901.458.789-1',
  telefono VARCHAR(30) DEFAULT '+57 321 408 8690',
  email VARCHAR(100) DEFAULT 'contacto@sigibet.com',
  direccion VARCHAR(150) DEFAULT 'Algeciras, Huila',
  impuesto DECIMAL(5,2) DEFAULT 19.00,
  tipo_facturacion VARCHAR(50) DEFAULT 'Régimen Simplificado',
  tema_colores TEXT NULL,
  color_botones VARCHAR(7) NOT NULL DEFAULT '#A85F6D',
  color_menu VARCHAR(7) NOT NULL DEFAULT '#1C191B',
  color_acento VARCHAR(7) NOT NULL DEFAULT '#B9BF66',
  tipografia VARCHAR(30) NOT NULL DEFAULT 'Inter'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS recuperacion_password (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  token_hash CHAR(64) NOT NULL UNIQUE,
  expira_en DATETIME NOT NULL,
  usado TINYINT NOT NULL DEFAULT 0,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS auditoria (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  modulo VARCHAR(50) NOT NULL,
  accion VARCHAR(50) NOT NULL,
  detalles TEXT NULL,
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- INSERCIÓN DE DATOS INICIALES (SEMILLA)
-- --------------------------------------------------------

-- 1. Insertar roles (id 1 = Administrador, id 2 = Empleado)
INSERT IGNORE INTO roles (id, nombre, descripcion) VALUES 
(1, 'Administrador', 'Acceso total al sistema'),
(2, 'Empleado', 'Acceso limitado a facturación y caja');

-- 2. Insertar usuario Administrador
-- Credenciales -> Email: admin@gmail.com / Password: admin123 / Usuario: admin
INSERT IGNORE INTO usuarios (nombre, usuario, email, telefono, password, rol_id, estado) VALUES 
('Administrador General', 'admin', 'admin@gmail.com', '3000000000', '$2y$10$LrpiIW3iCGHoeWXn8R4axuONwll/8gEq.kYLgfbEVflhftoqwvcXG', 1, 1);

-- 3. Insertar usuario Empleado de prueba
-- Credenciales -> Usuario: vendedora1 / Password: admin123
INSERT IGNORE INTO usuarios (nombre, usuario, email, telefono, password, rol_id, estado) VALUES 
('María Camila Ríos', 'vendedora1', 'camila@bellatela.com', '3115550101', '$2y$10$LrpiIW3iCGHoeWXn8R4axuONwll/8gEq.kYLgfbEVflhftoqwvcXG', 2, 1);

-- 4. Configuración inicial de la empresa
INSERT IGNORE INTO configuracion (id, nombre_empresa, nit, telefono, email, direccion, impuesto, tipo_facturacion) VALUES
(1, 'Bella Tela', '900.123.456-7', '+57 321 408 8690', 'info@bellatela.com', 'Algeciras, Huila', 19.00, 'Régimen Simplificado');

-- --------------------------------------------------------
-- PRODUCTOS: CATÁLOGO DE TELAS (30 referencias reales)
-- --------------------------------------------------------
-- Categorías: Moda Femenina, Deportivo, Hogar, Lencería, Industrial
-- Colores variados propios de almacén de telas
-- Precio venta (precio) y precio de costo (precio_compra) en COP por metro
-- --------------------------------------------------------
INSERT IGNORE INTO productos (referencia, nombre, color, categoria, descripcion, precio, precio_compra, stock, stock_minimo, estado) VALUES

-- === MODA FEMENINA ===
('TL-001', 'Tela Seda Italiana', 'Marfil',       'Moda Femenina', 'Seda italiana de alta gama, suave al tacto, ideal para blusas y vestidos de noche.',         45000.00, 28000.00, 80,  10, 1),
('TL-002', 'Tela Seda Italiana', 'Negro',        'Moda Femenina', 'Seda italiana de alta gama, suave al tacto, ideal para blusas y vestidos de noche.',         45000.00, 28000.00, 60,  10, 1),
('TL-003', 'Tela Encaje Bordado', 'Blanco Novia','Moda Femenina', 'Encaje bordado con flores, perfecto para vestidos de novia y quinceañeras.',                  38000.00, 22000.00, 45,   8, 1),
('TL-004', 'Tela Encaje Bordado', 'Nude',        'Moda Femenina', 'Encaje bordado con flores, perfecto para vestidos de novia y quinceañeras.',                  38000.00, 22000.00, 40,   8, 1),
('TL-005', 'Tela Gasa Plisada',  'Rosado Palo',  'Moda Femenina', 'Gasa liviana plisada, ideal para faldas voluminosas y vestidos de gala.',                     22000.00, 12000.00, 120, 15, 1),
('TL-006', 'Tela Gasa Plisada',  'Azul Cielo',   'Moda Femenina', 'Gasa liviana plisada, ideal para faldas voluminosas y vestidos de gala.',                     22000.00, 12000.00, 95,  15, 1),
('TL-007', 'Tela Chifón Fluido', 'Coral',        'Moda Femenina', 'Chifón de caída suave, traslúcido, ideal para blusas y sobrevestidos.',                       18000.00, 10000.00, 110, 15, 1),
('TL-008', 'Tela Chifón Fluido', 'Esmeralda',    'Moda Femenina', 'Chifón de caída suave, traslúcido, ideal para blusas y sobrevestidos.',                       18000.00, 10000.00, 85,  15, 1),
('TL-009', 'Tela Popelina Lisa', 'Blanco',       'Moda Femenina', 'Popelina 100% algodón, textura fina, ideal para camisas y vestidos casuales.',                 9500.00,  5500.00, 200, 20, 1),
('TL-010', 'Tela Popelina Lisa', 'Azul Marino',  'Moda Femenina', 'Popelina 100% algodón, textura fina, ideal para camisas y vestidos casuales.',                 9500.00,  5500.00, 175, 20, 1),
('TL-011', 'Tela Lino Natural',  'Beige',        'Moda Femenina', 'Lino natural transpirable, ideal para conjuntos de verano y ropa casual chic.',               14000.00,  8000.00, 90,  12, 1),
('TL-012', 'Tela Lino Natural',  'Verde Oliva',  'Moda Femenina', 'Lino natural transpirable, ideal para conjuntos de verano y ropa casual chic.',               14000.00,  8000.00, 70,  12, 1),

-- === DEPORTIVO ===
('DP-001', 'Tela Licra Deportiva 4 Vías', 'Negro Carbón', 'Deportivo', 'Licra de alta elasticidad en 4 direcciones, resistente al cloro, para ropa deportiva y trajes de baño.',  28000.00, 16000.00, 150, 20, 1),
('DP-002', 'Tela Licra Deportiva 4 Vías', 'Fucsia',       'Deportivo', 'Licra de alta elasticidad en 4 direcciones, resistente al cloro, para ropa deportiva y trajes de baño.',  28000.00, 16000.00, 120, 20, 1),
('DP-003', 'Tela Dry-Fit Técnica',        'Gris Melange', 'Deportivo', 'Tela técnica de secado rápido, anti-transpirante, ideal para camisetas y shorts deportivos.',            19000.00, 11000.00, 100, 15, 1),
('DP-004', 'Tela Dry-Fit Técnica',        'Azul Royal',   'Deportivo', 'Tela técnica de secado rápido, anti-transpirante, ideal para camisetas y shorts deportivos.',            19000.00, 11000.00, 80,  15, 1),
('DP-005', 'Tela Neopreno 3mm',           'Negro',        'Deportivo', 'Neopreno de 3mm de grosor, ideal para trajes de neopreno, bolsos y accesorios deportivos acuáticos.',    35000.00, 20000.00, 40,   5, 1),

-- === HOGAR ===
('HG-001', 'Tela Cortinería Blackout',  'Gris Plata',    'Hogar', 'Tela blackout de alta densidad, bloquea el 99% de la luz, ideal para cortinas de dormitorio.',     32000.00, 19000.00, 60,  8, 1),
('HG-002', 'Tela Cortinería Blackout',  'Beige Arena',   'Hogar', 'Tela blackout de alta densidad, bloquea el 99% de la luz, ideal para cortinas de dormitorio.',     32000.00, 19000.00, 55,  8, 1),
('HG-003', 'Tela Cretona Estampada',    'Flores Pastel', 'Hogar', 'Cretona 100% algodón con estampado floral, ideal para tapicería ligera, cojines y manteles.',      12000.00,  6500.00, 130, 15, 1),
('HG-004', 'Tela Velvet Tapicería',     'Terracota',     'Hogar', 'Terciopelo corto para tapicería de muebles y cabeceros de cama, resistente al desgaste.',          42000.00, 25000.00, 35,   5, 1),
('HG-005', 'Tela Velvet Tapicería',     'Azul Petróleo', 'Hogar', 'Terciopelo corto para tapicería de muebles y cabeceros de cama, resistente al desgaste.',          42000.00, 25000.00, 30,   5, 1),
('HG-006', 'Tela Impermeable Oxford',   'Negro',         'Hogar', 'Tela Oxford 600D impermeable, ideal para carpas, toldos, bolsos y manteles exteriores.',           16000.00,  9000.00, 75,  10, 1),

-- === LENCERÍA ===
('LC-001', 'Tela Satén Lencero',   'Blanco',        'Lencería', 'Satén de acabado brillante, suave y deslizante, ideal para lencería fina, pijamas y ropa íntima.',  25000.00, 14000.00, 90,  12, 1),
('LC-002', 'Tela Satén Lencero',   'Negro',         'Lencería', 'Satén de acabado brillante, suave y deslizante, ideal para lencería fina, pijamas y ropa íntima.',  25000.00, 14000.00, 80,  12, 1),
('LC-003', 'Tela Satén Lencero',   'Rojo Pasión',   'Lencería', 'Satén de acabado brillante, suave y deslizante, ideal para lencería fina, pijamas y ropa íntima.',  25000.00, 14000.00, 60,  12, 1),
('LC-004', 'Tela Microfibra Suave','Rosado',        'Lencería', 'Microfibra ultra suave tipo seda, ideal para ropa interior femenina y pijamas.',                    20000.00, 11000.00, 100, 15, 1),
('LC-005', 'Tela Microfibra Suave','Lila',          'Lencería', 'Microfibra ultra suave tipo seda, ideal para ropa interior femenina y pijamas.',                    20000.00, 11000.00, 85,  15, 1),

-- === INDUSTRIAL ===
('IN-001', 'Tela Denim 10oz',      'Azul Índigo',   'Industrial', 'Denim 100% algodón de 10 onzas, resistente, ideal para jeans, chaquetas y overoles.',            18000.00, 10500.00, 200, 25, 1),
('IN-002', 'Tela Drill Uniformes', 'Kaki',          'Industrial', 'Drill de algodón resistente al desgaste, ideal para uniformes industriales y pantalones de trabajo.', 11000.00, 6000.00, 250, 30, 1),
('IN-003', 'Tela Drill Uniformes', 'Verde Militar', 'Industrial', 'Drill de algodón resistente al desgaste, ideal para uniformes industriales y pantalones de trabajo.', 11000.00, 6000.00, 180, 30, 1);
