# SIGIBET

Sistema de gestión para almacén de telas e insumos de costura, desarrollado con PHP, MySQL y Bootstrap.

## Descripción

SIGIBET permite gestionar:

- productos por categoría y referencia
- inventario y stock mínimo
- ventas con un ID anónimo de cliente autoincremental por compra
- usuarios con roles
- reportes y auditoría
- configuración del negocio
- colores independientes para botones, menús y acentos, y selector de tipografía
- autenticación y recuperación de acceso
- cálculo configurable del IVA (19% predeterminado) en ventas y cotizaciones

## Tecnologías

- PHP
- MySQL
- Bootstrap 5
- JavaScript
- CSS personalizado
- Laragon (entorno local recomendado)

## Estructura del proyecto

```text
SIGIBET/
├── README.md
├── .gitignore
├── config/
│   └── database.php
├── controllers/
│   └── auth/
├── docs/
│   └── HISTORIAS_USUARIO.md
├── models/
├── public/
│   ├── index.php
│   └── img/
├── sql/
│   ├── db.sql
│   ├── datos_prueba.sql
│   └── migraciones/
│       ├── 20261009_clientes_por_compra.sql
│       ├── 20261009_iva_general_19.sql
│       ├── 20261009_opciones_apariencia.sql
│       └── 20261009_acento_verde_oliva.sql
├── views/
│   ├── admin/
│   ├── Auth/
│   ├── Layoust/
│   └── seller/
└── .gitignore
```

## Requisitos previos

- Windows con Laragon
- PHP 8+
- MySQL
- Apache habilitado
- acceso a localhost

## Instalación paso a paso

### 1. Ubicar el proyecto

Coloca la carpeta en:

```text
c:\laragon\www\SIGIBET
```

### 2. Crear la base de datos

Importa el archivo:

```text
sql/db.sql
```

Esto crea la base de datos `sigibet_db` y carga la semilla inicial.

Si ya tienes la base de datos instalada, ejecuta una sola vez
`sql/migraciones/20261009_clientes_por_compra.sql` para permitir los registros
anónimos de clientes y `sql/migraciones/20261009_iva_general_19.sql` para
establecer el IVA general en 19%. Ejecuta también
`sql/migraciones/20261009_opciones_apariencia.sql` para agregar la configuración
separada de colores y tipografía y `sql/migraciones/20261009_acento_verde_oliva.sql`
para establecer el nuevo color de acento. Las ventas nuevas crearán un registro de
cliente por compra y usarán su ID autoincremental; los datos históricos no se
borran.

### 3. Configurar conexión

Edita el archivo:

```text
config/database.php
```

Con la configuración de tu entorno local:

```php
<?php
date_default_timezone_set('America/Bogota');

$host = "127.0.0.1";
$user = "root";
$password = "";
$bd = "sigibet_db";
$port = 3306;

try {
    $conn = new mysqli($host, $user, $password, $bd, $port);
} catch (mysqli_sql_exception $e) {
    die("Error de Conexion: " . $e->getMessage());
}
?>
```

### 4. Ejecutar la aplicación

Abre en el navegador:

```text
http://localhost/SIGIBET/public/index.php
```

## Credenciales por defecto

### Administrador

- usuario: `admin`
- email: `admin@gmail.com`
- contraseña: `admin123`

### Empleado

- usuario: `vendedora1`
- contraseña: `admin123`

## Flujos principales

- Login y autenticación
- gestión de productos
- administración de clientes
- registro de ventas
- control de stock
- reportes e historial
- configuración del negocio

## Documentación

Documentación del negocio y requisitos funcionales:

```text
docs/HISTORIAS_USUARIO.md
```

## Subir a GitHub paso a paso

### Commit 1: inicializar proyecto

```bash
git init
git add .
git commit -m "Inicializa proyecto SIGIBET"
```

### Commit 2: base de datos y configuración

```bash
git add config sql
git commit -m "Configura conexión a base de datos y estructura SQL"
```

### Commit 3: autenticación y usuarios

```bash
git add controllers models views/Auth
git commit -m "Implementa login, roles y gestión de usuarios"
```

### Commit 4: productos e inventario

```bash
git add controllers models views/admin/gproductos.php
git commit -m "Agrega catálogo de productos e inventario"
```

### Commit 5: ventas y clientes

```bash
git add views/admin/ventas.php views/admin/clientes.php controllers models
git commit -m "Agrega ventas, clientes y flujo de facturación"
```

### Commit 6: reportes y auditoría

```bash
git add views/admin/reportes.php views/admin/auditoria.php controllers models
git commit -m "Agrega reportes, historial y auditoría"
```

### Commit 7: diseño y frontend

```bash
git add public/img views/Layoust public/index.php
git commit -m "Mejora interfaz visual y branding del sistema"
```

### Commit 8: documentación final

```bash
git add README.md docs/HISTORIAS_USUARIO.md
git commit -m "Documenta proyecto y requisitos funcionales"
```

### Conectar con GitHub

```bash
git branch -M main
git remote add origin https://github.com/TU_USUARIO/SIGIBET.git
git push -u origin main
```

## Recomendación

Si quieres mantener un historial limpio y ordenado, usa estos nombres de commits como referencia y avanza por etapas funcionales.

## Nota

El proyecto está orientado a entorno local con Laragon. Para producción se recomienda revisar permisos, seguridad, variables de entorno y configuración del servidor.
