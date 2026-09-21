<?php
// Requerir el AuthController para verificar la sesión
require_once __DIR__ . '/../../controllers/auth/AuthController.php';
require_once __DIR__ . '/../../models/Configuracion.php';

// Obtener sesión de usuario
$usuario = AuthController::usuarioActual();
if (!$usuario) {
    header('Location: ../../views/Auth/login.php');
    exit;
}

$id_rol_actual  = $usuario['id_rol'] ?? 2;
$nombre_rol     = ($id_rol_actual == '1') ? 'Administrador' : 'Empleado';
$usuario_nombre = $usuario['nombre'] ?? 'Usuario';
$usuario_inicial = strtoupper(substr($usuario_nombre, 0, 1));

// Guardar nombre en sesión para el sidebar
$_SESSION['usuario_nombre'] = $usuario_nombre;

$configuracion = (new Configuracion())->obtenerConfiguracion();
$color_secundario = $configuracion['tema_colores'] ?? '#B87983';
$color_secundario_rgb = sscanf(ltrim($color_secundario, '#'), '%02x%02x%02x');
$color_secundario_rgb = implode(', ', $color_secundario_rgb ?: [185, 111, 97]);

// Paleta configurable desde Administración > Configuración.
$tema = [
            'color_primario'   => '#503A33',
    'color_secundario' => $color_secundario,
    'color_fondo'      => '#FCF8F7'
];
$color_rol = ($id_rol_actual == '1') ? '#C98A3D' : $color_secundario;

// Lógica de Breadcrumbs
$c = $_GET['c'] ?? 'producto';
$a = $_GET['a'] ?? 'index';
$current_page_key = $c . '_' . $a;

$breadcrumb_map = [
    'dashboard_index'       => [['icono'=>'fa-chart-pie',           'label'=>'Dashboard']],
    'venta_index'           => [['icono'=>'fa-file-invoice-dollar', 'label'=>'Ventas'],         ['icono'=>'fa-cash-register', 'label'=>'Facturación POS']],
    'venta_historial'       => [['icono'=>'fa-file-invoice-dollar', 'label'=>'Ventas'],         ['icono'=>'fa-list',          'label'=>'Historial']],
    'cliente_index'         => [['icono'=>'fa-users',               'label'=>'Clientes']],
    'producto_index'        => [['icono'=>'fa-warehouse',           'label'=>'Inventario'],     ['icono'=>'fa-list-check',     'label'=>'Auditoría']],
    'inventario_index'      => [['icono'=>'fa-warehouse',           'label'=>'Inventario'],     ['icono'=>'fa-cubes',         'label'=>'Dashboard']],
    'producto_stock'        => [['icono'=>'fa-box-open',            'label'=>'Catálogo'],       ['icono'=>'fa-cubes',         'label'=>'Inventario']],
    'usuario_listar'        => [['icono'=>'fa-user-shield',         'label'=>'Administración'], ['icono'=>'fa-users',         'label'=>'Usuarios']],
    'configuracion_index'   => [['icono'=>'fa-building',            'label'=>'Administración'], ['icono'=>'fa-building',      'label'=>'Empresa']],
    'configuracion_colores' => [['icono'=>'fa-palette',             'label'=>'Administración'], ['icono'=>'fa-palette',       'label'=>'Colores']],
    'reporte_index'         => [['icono'=>'fa-chart-line',          'label'=>'Reportes']],
];

$breadcrumbs = $breadcrumb_map[$current_page_key] ?? [['icono'=>'fa-circle', 'label'=>ucfirst($c)]];
$directorio_actual = basename(dirname($_SERVER['PHP_SELF']));
$prefijo_admin = ($directorio_actual === 'admin') ? '' : '../admin/';
$ruta_inicio = ($directorio_actual === 'admin') ? 'gproductos.php' : 'mis_ventas.php';
$rutas_breadcrumb = [
    'Catálogo'          => $prefijo_admin . 'gproductos.php',
    'Productos'         => $prefijo_admin . 'gproductos.php',
    'Inventario'        => $prefijo_admin . 'inventario.php',
    'Dashboard'         => $prefijo_admin . 'inventario.php',
    'Ventas'            => ($directorio_actual === 'admin') ? 'ventas.php' : 'mis_ventas.php',
    'Facturación POS'   => ($directorio_actual === 'admin') ? 'ventas.php' : 'mis_ventas.php',
    'Historial'         => ($directorio_actual === 'seller') ? 'historial.php' : 'reportes.php',
    'Clientes'          => $prefijo_admin . 'clientes.php',
    'Administración'    => $prefijo_admin . 'usuarios.php',
    'Usuarios'          => $prefijo_admin . 'usuarios.php',
    'Empresa'           => $prefijo_admin . 'configuracion.php',
    'Colores'           => $prefijo_admin . 'configuracion.php',
    'Reportes'          => $prefijo_admin . 'reportes.php',
    'Auditoría'         => $prefijo_admin . 'auditoria.php',
    'Respaldo'          => $prefijo_admin . 'respaldo.php'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGIBET - Almacén de Telas</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6.4.0 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../../public/img/style.css" rel="stylesheet">
    <!-- Chart.js (siempre disponible) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --color-primario:   #4C3C38;
            --color-secundario: #C99297;
            --color-fondo:      #F7F3F1;
            --bs-primary:       #C99297;
            --bs-primary-rgb:   201, 146, 151;
            --bs-dark:          #4C3C38;
        }
        html, body {
            height: 100%;
            margin: 0;
            background: linear-gradient(180deg, #fdfaf8 0%, #f3efee 100%);
            font-family: 'Inter', 'Segoe UI', sans-serif;
        }
        .app-container { height: 100vh; width: 100vw; overflow: hidden; }
        .app-sidebar {
            width: 260px;
            height: 100vh;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            background: linear-gradient(180deg, #5C4743 0%, #433833 100%);
            transition: all 0.3s ease;
            z-index: 1040;
        }
        .sidebar-nav .nav-link {
            color: rgba(255,255,255,0.75);
            padding: 0.8rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .sidebar-nav .nav-link:hover {
            color: #fff;
            background: rgba(255,255,255,0.08);
            border-left: 4px solid #D9B6BB;
        }
        .sidebar-nav .nav-link.active {
            color: #fff;
            background: #C99297;
            border-left: 4px solid #F7E8EA;
            box-shadow: 0 4px 12px rgba(201, 146, 151, 0.26);
        }
        .app-main    { height: 100vh; overflow: hidden; }
        .app-content { overflow-y: auto; background-color: var(--color-fondo); }
        .stat-card   { border-left: 4px solid var(--color-secundario); }
        .btn-primary {
            background: linear-gradient(135deg, #D9B5BA 0%, #C89299 100%) !important;
            border-color: #D9B5BA !important;
            color: #fff !important;
            box-shadow: 0 5px 16px rgba(201, 146, 151, 0.22);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #C98B92 0%, #B77780 100%) !important;
            border-color: #C98B92 !important;
        }
        @media (max-width: 768px) {
            .app-sidebar {
                position: fixed;
                left: -260px;
                box-shadow: 5px 0 15px rgba(0,0,0,0.2) !important;
            }
            .app-sidebar.show { left: 0; }
        }
    </style>
</head>
<body>
<div class="app-container d-flex">

    <!-- Sidebar -->
    <?php
    if (AuthController::esAdmin()) {
        include __DIR__ . '/sidebaradmin.php';
    } else {
        include __DIR__ . '/sidebarseller.php';
    }
    ?>

    <!-- Main Wrapper -->
    <div class="app-main flex-grow-1 d-flex flex-column overflow-hidden">

        <!-- Topbar -->
        <header class="app-topbar d-flex justify-content-between align-items-center px-4 bg-white border-bottom shadow-sm"
                style="height:80px; min-height:80px; z-index:100;">
            <!-- Toggle móvil -->
            <button class="btn btn-light d-md-none me-2" id="toggleSidebar">
                <i class="fa-solid fa-bars"></i>
            </button>

            <!-- Breadcrumb funcional -->
            <nav aria-label="breadcrumb" class="d-none d-md-flex align-items-center">
                <ol class="breadcrumb mb-0 align-items-center">
                    <li class="breadcrumb-item">
                        <a href="<?= htmlspecialchars($ruta_inicio) ?>"
                           class="text-decoration-none d-flex align-items-center gap-1"
                           style="color: var(--color-secundario);">
                            <i class="fa-solid fa-house" style="font-size:.8rem;"></i>
                            <span style="font-size:.83rem;">SIGIBET</span>
                        </a>
                    </li>

                    <?php foreach ($breadcrumbs as $i => $crumb): ?>
                        <?php $is_last = ($i === count($breadcrumbs) - 1); $label = $crumb['label']; ?>
                        <li class="breadcrumb-item <?= $is_last ? 'active' : '' ?>"
                            <?= $is_last ? 'aria-current="page"' : '' ?>>
                            <?php if (isset($rutas_breadcrumb[$label]) && (!$is_last || $label === 'Auditoría')): ?>
                                <a href="<?= htmlspecialchars($rutas_breadcrumb[$label]) ?>"
                                class="text-decoration-none d-flex align-items-center gap-1 <?= $is_last ? 'fw-semibold' : '' ?>"
                                style="color:<?= $is_last ? 'var(--color-secundario)' : '#6c757d' ?>;font-size:<?= $is_last ? '.85rem' : '.83rem' ?>;">
                                    <i class="fa-solid <?= htmlspecialchars($crumb['icono']) ?>"
                                       style="font-size:.78rem;"></i>
                                    <?= htmlspecialchars($label) ?>
                                </a>
                            <?php else: ?>
                                <span class="d-flex align-items-center gap-1 fw-semibold"
                                      style="color:var(--color-secundario);font-size:.85rem;">
                                    <i class="fa-solid <?= htmlspecialchars($crumb['icono']) ?>"
                                       style="font-size:.78rem;"></i>
                                    <?= htmlspecialchars($label) ?>
                                </span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </nav>

            <!-- Perfil / menú del usuario -->
            <div class="dropdown ms-auto">
                <button class="btn border-0 p-0 d-flex align-items-center gap-3"
                        type="button"
                        id="userMenu"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                        style="background:transparent;">

                    <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm text-white fw-bold flex-shrink-0"
                         style="width:40px;height:40px;background:linear-gradient(135deg,#62483F,#B87983);font-size:1rem;">
                        <?= $usuario_inicial ?>
                    </div>

                    <div class="d-none d-md-block lh-sm text-start">
                        <div class="fw-semibold text-dark" style="font-size:.9rem;">
                            <?= htmlspecialchars($usuario_nombre) ?>
                        </div>
                        <span class="badge rounded-pill px-2"
                              style="background-color:<?= $color_rol ?>;font-size:.68rem;">
                            <?= $nombre_rol ?>
                        </span>
                    </div>

                    <i class="fa-solid fa-chevron-down text-muted d-none d-md-inline"
                       style="font-size:.7rem;"></i>
                </button>

                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="userMenu">
                    <li>
                        <h6 class="dropdown-header">
                            <i class="fa-solid fa-user-shield me-1"></i>
                            <?= htmlspecialchars($usuario_nombre) ?>
                        </h6>
                    </li>

                    <?php if (AuthController::esAdmin()): ?>
                        <li>
                            <a class="dropdown-item" href="usuarios.php">
                                <i class="fa-solid fa-users-gear me-2 text-muted"></i>
                                Gestión de Usuarios
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="configuracion.php">
                                <i class="fa-solid fa-gear me-2 text-muted"></i>
                                Configuración
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                    <?php endif; ?>

                    <li>
                        <a class="dropdown-item text-danger"
                           href="../../controllers/auth/AuthController.php?accion=logout">
                            <i class="fa-solid fa-right-from-bracket me-2"></i>
                            Cerrar sesión
                        </a>
                    </li>
                </ul>
            </div>
        </header>

        <!-- Main Content Area (Scrollable) — las vistas insertan su contenido aquí -->
        <main class="app-content p-4 flex-grow-1 overflow-auto"
              style="background-color: var(--color-fondo);">
