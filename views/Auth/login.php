<?php
session_start();
require_once __DIR__ . '/../../models/Configuracion.php';

$configuracion = (new Configuracion())->obtenerConfiguracion();
    $colorPrimario = $configuracion['tema_colores'] ?? '#B87983';
$colorPrimarioRgb = sscanf(ltrim($colorPrimario, '#'), '%02x%02x%02x');
$colorPrimarioRgb = implode(', ', $colorPrimarioRgb ?: [185, 111, 97]);

// Si el usuario ya tiene sesión iniciada, redirigir al dashboard/panel correspondiente
if (isset($_SESSION['usuario'])) {
    $destino = ($_SESSION['usuario']['id_rol'] == 1) ? '../admin/gproductos.php' : '../seller/mis_ventas.php';
    header('Location: ' . $destino);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGIBET - Iniciar Sesión</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- SweetAlert2 para Alertas -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Custom CSS (Tema Bella Tela) -->
    <style>
        :root {
            --primary-color: <?php echo htmlspecialchars($colorPrimario, ENT_QUOTES, 'UTF-8'); ?>;
            --primary-hover: #9E626D;
            --dark-bg: #503A33;
            --text-main: #3D2B28;
            --primary-rgb: <?php echo htmlspecialchars($colorPrimarioRgb, ENT_QUOTES, 'UTF-8'); ?>;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, rgba(57, 40, 35, 0.78) 0%, rgba(80, 58, 51, 0.48) 100%),
                        url('../../public/img/Fondo.png') center/cover no-repeat fixed;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            color: var(--text-main);
        }
        .login-card {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(10px);
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.3);
            width: 100%;
            max-width: 450px;
        }
        .login-header {
            background: var(--dark-bg);
            color: white;
            padding: 2rem 1.5rem;
            text-align: center;
        }
        .login-header img {
            max-height: 90px;
            object-fit: contain;
        }
        .login-header h2 {
            font-weight: 700;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }
        .login-header h2 span {
            color: #E1A1AA;
        }
        .login-body {
            padding: 2.5rem;
        }
        .form-floating > label {
            color: #6c757d;
        }
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(201, 130, 141, 0.25);
        }
        .btn-login {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            padding: 0.8rem;
            font-weight: 600;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(var(--primary-rgb), 0.4);
        }
        .back-link {
            text-decoration: none;
            color: var(--primary-color);
            font-size: 0.9rem;
            font-weight: 500;
        }
        .back-link:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <img src="../../public/img/logoprincipal.png" alt="Bella Tela" class="mb-2">
            <p class="mb-0 fw-light text-white-50">Acceso al Sistema</p>
        </div>
        <div class="login-body">
            <form action="../../controllers/auth/AuthController.php?accion=login" method="POST">
                
                <div class="form-floating mb-4">
                    <input type="text" class="form-control" id="usuario" name="usuario" placeholder="usuario_o_email" required autocomplete="username">
                    <label for="usuario">Usuario o Correo Electrónico</label>
                </div>
                
                <div class="form-floating mb-4">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" required autocomplete="current-password">
                    <label for="password">Contraseña</label>
                </div>
                
                <div class="d-grid mb-4">
                    <button type="submit" class="btn btn-login btn-lg">Ingresar</button>
                </div>

                <div class="text-center mb-3">
                    <a href="recuperar.php" class="back-link">¿Olvidaste tu contraseña?</a>
                </div>
                
                <div class="text-center mt-3">
                    <a href="../../public/index.php" class="back-link">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left me-1" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
                        </svg>
                        Volver al inicio
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Mostrar Alertas desde PHP a través de SweetAlert2 -->
    <?php if (isset($_SESSION['alert'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: '<?php echo $_SESSION['alert']['icon']; ?>',
                    title: '<?php echo $_SESSION['alert']['title']; ?>',
                    text: '<?php echo $_SESSION['alert']['text']; ?>',
                    confirmButtonColor: '#b56576'
                });
            });
        </script>
        <?php unset($_SESSION['alert']); // Limpiar la alerta después de mostrarla ?>
    <?php endif; ?>

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>