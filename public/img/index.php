<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bella Tela - Software de Gestión para Almacenes de Telas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- CSS en la misma carpeta img/ -->
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        .hero-section {
            min-height: 100vh;
            padding-top: 90px;
            position: relative;
            overflow: hidden;
            background:
                linear-gradient(135deg, rgba(52,29,24,0.78) 0%, rgba(74,47,42,0.5) 100%),
                url('Fondo.png') center/cover no-repeat fixed;
        }
        .hero-logo-wrap { display: flex; justify-content: center; align-items: center; }
        .hero-logo {
            max-height: 420px; width: auto; max-width: 100%;
            mix-blend-mode: multiply;
            filter: drop-shadow(0 12px 32px rgba(0,0,0,0.35));
            animation: floatLogo 4s ease-in-out infinite;
        }
        @keyframes floatLogo {
            0%,100% { transform: translateY(0); }
            50%      { transform: translateY(-16px); }
        }
        @media (max-width: 991px) { .hero-logo { max-height: 260px; animation: none; } }

        .beneficios-section { background: linear-gradient(135deg, #503A33 0%, #805B61 100%); }
        .beneficio-card {
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 1.25rem;
            transition: transform 0.3s ease, background 0.3s ease;
        }
        .beneficio-card:hover { transform: translateY(-6px); background: rgba(240,160,139,0.18); }
        .beneficio-icon {
            width:60px; height:60px;
            background:rgba(240,160,139,0.2); border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            font-size:1.5rem; color:#F0A08B; margin-bottom:1rem;
        }
        html { scroll-behavior: smooth; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark-custom fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="#">
                <img src="logoprincipal.png" alt="Bella Tela"
                     style="height:42px;width:auto;mix-blend-mode:multiply;filter:brightness(0) invert(1);">
            </a>
            <button class="navbar-toggler" type="button"
                    data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item"><a class="nav-link px-3" href="#caracteristicas">Características</a></li>
                    <li class="nav-item"><a class="nav-link px-3" href="#beneficios">Beneficios</a></li>
                    <li class="nav-item ms-lg-3 mt-3 mt-lg-0">
                        <!-- Desde public/img/ subimos 3 niveles para llegar a views/Auth/ -->
                        <a class="btn btn-primary px-4 py-2 fw-semibold"
                           href="../../views/Auth/login.php">Iniciar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- HERO -->
    <section class="hero-section d-flex align-items-center">
        <div class="container text-center text-lg-start">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <h1 class="display-4 fw-bold text-white mb-4" style="letter-spacing:-1px;line-height:1.15;">
                        La solución definitiva para tu
                        <span class="text-primary-custom"> Almacén de Telas</span>
                    </h1>
                    <p class="lead mb-4 pb-2" style="color:rgba(255,255,255,0.88);font-size:1.15rem;">
                        Gestiona tu inventario con precisión, automatiza tu facturación POS
                        y lleva el control total de tu negocio desde una única plataforma profesional e intuitiva.
                    </p>
                    <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-lg-start gap-3">
                        <a href="#caracteristicas" class="btn btn-outline-light px-4 py-2 fw-semibold">
                            <i class="fa-solid fa-circle-info me-2"></i>Características
                        </a>
                        <a href="#beneficios" class="btn btn-primary px-4 py-2 fw-semibold">
                            <i class="fa-solid fa-star me-2"></i>Beneficios
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 hero-logo-wrap mt-4 mt-lg-0">
                    <img src="logoprincipal.png" alt="Bella Tela" class="hero-logo">
                </div>
            </div>
        </div>
    </section>

    <!-- CARACTERÍSTICAS -->
    <section id="caracteristicas" class="features-section py-5" style="background:#FBF7F5;">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="fw-bold mb-3" style="color:#503A33;">Diseñado específicamente para el sector textil</h2>
                <p class="w-75 mx-auto" style="color:#6B4A40;">
                    Herramientas potentes y fáciles de usar para optimizar las operaciones diarias de tu almacén de telas.
                </p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card h-100 border-0 shadow-sm p-4">
                        <div class="feature-icon bg-primary-light text-primary-custom mb-4 rounded-3 d-inline-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-warehouse fa-lg"></i>
                        </div>
                        <h4 class="fw-bold mb-3" style="color:#503A33;">Gestión de Inventario</h4>
                        <p style="color:#6B4A40;" class="mb-0">Control exacto de metrajes, colores y tipos de tela. Alertas de stock bajo y reportes detallados en tiempo real.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card h-100 border-0 shadow-sm p-4">
                        <div class="feature-icon bg-primary-light text-primary-custom mb-4 rounded-3 d-inline-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-receipt fa-lg"></i>
                        </div>
                        <h4 class="fw-bold mb-3" style="color:#503A33;">Facturación POS</h4>
                        <p style="color:#6B4A40;" class="mb-0">Sistema de punto de venta ágil y eficiente. Generación de facturas de forma rápida para reducir tiempos de espera en caja.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card h-100 border-0 shadow-sm p-4">
                        <div class="feature-icon bg-primary-light text-primary-custom mb-4 rounded-3 d-inline-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-chart-line fa-lg"></i>
                        </div>
                        <h4 class="fw-bold mb-3" style="color:#503A33;">Reportes Estratégicos</h4>
                        <p style="color:#6B4A40;" class="mb-0">Visualiza el rendimiento de tu negocio. Análisis de ventas, productos más vendidos y métricas clave para la toma de decisiones.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- BENEFICIOS -->
    <section id="beneficios" class="beneficios-section py-5">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="fw-bold text-white mb-3">¿Por qué elegir Bella Tela?</h2>
                <p style="color:rgba(255,255,255,0.75);" class="w-75 mx-auto">
                    Una plataforma pensada desde cero para las necesidades reales de un almacén de telas en Colombia.
                </p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="beneficio-card p-4 h-100">
                        <div class="beneficio-icon"><i class="fa-solid fa-bolt"></i></div>
                        <h5 class="fw-bold text-white mb-2">Rapidez en caja</h5>
                        <p style="color:rgba(255,255,255,0.72);" class="mb-0 small">El POS busca productos por nombre, referencia o color en milisegundos. Cobra más rápido y reduce las filas de espera.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="beneficio-card p-4 h-100">
                        <div class="beneficio-icon"><i class="fa-solid fa-shield-halved"></i></div>
                        <h5 class="fw-bold text-white mb-2">Control de acceso por roles</h5>
                        <p style="color:rgba(255,255,255,0.72);" class="mb-0 small">Administrador y Empleado con permisos diferenciados. El inventario y los reportes solo los ve quien debe verlos.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="beneficio-card p-4 h-100">
                        <div class="beneficio-icon"><i class="fa-solid fa-bell"></i></div>
                        <h5 class="fw-bold text-white mb-2">Alertas de bajo stock</h5>
                        <p style="color:rgba(255,255,255,0.72);" class="mb-0 small">El sistema notifica automáticamente cuando una referencia de tela cae por debajo del stock mínimo configurado.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="beneficio-card p-4 h-100">
                        <div class="beneficio-icon"><i class="fa-solid fa-chart-pie"></i></div>
                        <h5 class="fw-bold text-white mb-2">Reportes en tiempo real</h5>
                        <p style="color:rgba(255,255,255,0.72);" class="mb-0 small">Historial de ventas, productos más vendidos y valor total del inventario disponibles con un solo clic.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="beneficio-card p-4 h-100">
                        <div class="beneficio-icon"><i class="fa-solid fa-floppy-disk"></i></div>
                        <h5 class="fw-bold text-white mb-2">Copias de seguridad</h5>
                        <p style="color:rgba(255,255,255,0.72);" class="mb-0 small">Genera y restaura backups de la base de datos con un solo clic desde el panel de administración.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="beneficio-card p-4 h-100">
                        <div class="beneficio-icon"><i class="fa-solid fa-palette"></i></div>
                        <h5 class="fw-bold text-white mb-2">Personalización total</h5>
                        <p style="color:rgba(255,255,255,0.72);" class="mb-0 small">Configura colores, datos de empresa, impuestos y tipo de facturación para adaptar el sistema a tu negocio.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer style="background:#36211E;" class="text-white py-4">
        <div class="container text-center">
            <p class="mb-0" style="color:rgba(255,255,255,0.6);">
                &copy; <?php echo date('Y'); ?> Bella Tela &mdash; SIGIBET. Todos los derechos reservados.
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
