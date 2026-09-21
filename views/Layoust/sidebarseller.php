<aside class="app-sidebar text-white shadow-lg" id="sidebar">
    <div class="sidebar-brand d-flex align-items-center justify-content-center border-bottom border-light border-opacity-10" style="height: 80px; min-height: 80px; padding: 0 15px;">
        <a href="mis_ventas.php" class="text-decoration-none d-flex align-items-center justify-content-start gap-3" style="width: 100%; height: 100%;">
            <img src="../../public/img/logo_bella.png" alt="Logo Bella Tela" class="brand-logo-anim" style="height: 55px; width: auto; flex-shrink: 0;">
            <div class="d-flex flex-column justify-content-center text-center mt-1" style="line-height: 1;">
                <span style="color: #FFFFFF; font-family: 'Inter', sans-serif; font-size: 1.8rem; font-weight: 700; letter-spacing: -1px; margin-bottom: 4px;">Bella</span>
                <span style="color: #FFFFFF; font-family: 'Inter', sans-serif; font-size: 0.8rem; font-weight: 300; letter-spacing: 8px; margin-left: 8px;">TELA</span>
            </div>
        </a>
    </div>

    <?php
        $currentPage = basename($_SERVER['PHP_SELF']);
        $c = $_GET['c'] ?? '';
        if ($currentPage === 'mis_ventas.php') $c = 'venta';
        if ($currentPage === 'historial.php') $c = 'historial';
        if ($currentPage === 'clientes.php')  $c = 'cliente';
        if ($currentPage === 'gproductos.php') $c = 'producto';
        if ($currentPage === 'inventario.php') $c = 'inventario';
    ?>
    <ul class="nav flex-column sidebar-nav mt-3 overflow-auto pb-5">

        <li class="nav-item px-3 mt-4 mb-2 text-white-50 small fw-bold text-uppercase" style="letter-spacing: 1px;">Módulos</li>

        <li class="nav-item">
            <a href="mis_ventas.php" class="nav-link <?php echo ($c == 'venta') ? 'active' : ''; ?>">
                <i class="fa-solid fa-file-invoice-dollar"></i> Punto de Venta
            </a>
        </li>
        <li class="nav-item">
            <a href="historial.php" class="nav-link <?php echo ($c == 'historial') ? 'active' : ''; ?>">
                <i class="fa-solid fa-clock-rotate-left"></i> Mi historial
            </a>
        </li>
        <li class="nav-item">
            <a href="../admin/clientes.php" class="nav-link <?php echo ($c == 'cliente') ? 'active' : ''; ?>">
                <i class="fa-solid fa-users"></i> Clientes
            </a>
        </li>
        <li class="nav-item">
            <a href="../admin/gproductos.php" class="nav-link <?php echo ($c == 'producto') ? 'active' : ''; ?>">
                <i class="fa-solid fa-box-open"></i> Catálogo de Productos
            </a>
        </li>
        <li class="nav-item">
            <a href="../admin/inventario.php" class="nav-link <?php echo ($c == 'inventario') ? 'active' : ''; ?>">
                <i class="fa-solid fa-warehouse"></i> Inventario
            </a>
        </li>
    </ul>

    <div class="p-3 border-top border-light border-opacity-10 mt-auto">
        <div class="d-flex align-items-center gap-2 p-2 rounded-3" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
            <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold fs-6 shadow-sm" style="width: 36px; height: 36px; min-width: 36px; background: rgba(255,255,255,0.2);">
                <?php echo strtoupper(substr($_SESSION['usuario_nombre'] ?? 'E', 0, 1)); ?>
            </div>
            <div class="overflow-hidden flex-grow-1">
                <h6 class="text-white mb-0 fw-bold text-truncate" style="font-size: 0.85rem;"><?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Empleado'); ?></h6>
                <span class="text-white-50 d-block text-truncate" style="font-size: 0.7rem;"><i class="fa-solid fa-circle text-success" style="font-size: 0.5rem; vertical-align: middle; margin-right: 3px;"></i>En línea</span>
            </div>
            <a href="../../controllers/auth/AuthController.php?accion=logout" class="btn btn-sm border-0 p-1 rounded-circle d-flex align-items-center justify-content-center text-white-50" title="Cerrar Sesión" style="width: 28px; height: 28px; transition: color 0.2s;"
               onmouseover="this.classList.replace('text-white-50','text-danger')"
               onmouseout="this.classList.replace('text-danger','text-white-50')">
                <i class="fa-solid fa-power-off"></i>
            </a>
        </div>
    </div>
</aside>
