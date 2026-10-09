<?php
session_start();
require_once __DIR__ . '/../../models/Configuracion.php';

$configuracion = (new Configuracion())->obtenerConfiguracion();
$colorPrimario = $configuracion['color_botones'];
$colorPrimarioRgb = sscanf(ltrim($colorPrimario, '#'), '%02x%02x%02x');
$colorPrimarioRgb = implode(', ', $colorPrimarioRgb ?: [185, 111, 97]);
$tipografia = htmlspecialchars($configuracion['tipografia'], ENT_QUOTES, 'UTF-8');

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
            --primary-hover: <?php echo htmlspecialchars($colorPrimario, ENT_QUOTES, 'UTF-8'); ?>;
            --primary-cta: #B9974E;
            --dark-bg: #1C191B;
            --text-main: #292528;
            --text-soft: #70696D;
            --surface: #FAF8F8;
            --surface-strong: #F5E9EB;
            --primary-rgb: <?php echo htmlspecialchars($colorPrimarioRgb, ENT_QUOTES, 'UTF-8'); ?>;
        }
        body {
            font-family: <?php echo $tipografia; ?>;
            background: linear-gradient(90deg, rgba(28, 25, 27, 0.42) 0%, rgba(28, 25, 27, 0.2) 100%),
                        url('../../public/img/Fondo.png') center/cover no-repeat fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 24px 16px;
            color: var(--text-main);
        }
        button:hover, .btn:hover { filter: brightness(0.88); }
        .login-card {
            background: rgba(255, 255, 255, 0.97);
            border-radius: 18px;
            box-shadow: 0 24px 70px rgba(28, 25, 27, 0.28);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.74);
            width: 100%;
            max-width: 520px;
        }
        .login-header {
            position: relative;
            background: linear-gradient(135deg, #1C191B 0%, #39272E 100%);
            color: white;
            padding: 1.35rem 1.5rem 1.55rem;
            text-align: center;
            border-bottom: 3px solid var(--primary-cta);
        }
        .login-header .brand-wrap {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 0.45rem;
        }
        .login-header .brand-box {
            width: 108px;
            height: 116px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-header img {
            width: 100%;
            max-height: 116px;
            object-fit: contain;
            filter: drop-shadow(0 2px 8px rgba(0,0,0,0.2));
        }
        .login-header p {
            font-size: 1.16rem;
            font-weight: 600;
            letter-spacing: 0.01em;
            margin: 0;
            color: rgba(255,255,255,0.92);
        }
        .login-subtitle {
            margin: 0.35rem 0 0;
            color: rgba(255,255,255,0.68);
            font-size: 0.82rem;
            font-weight: 400;
            letter-spacing: 0.02em;
        }
        .login-body {
            padding: 1.8rem 1.6rem 1.45rem;
            background: rgba(255,255,255,0.98);
        }
        .form-floating {
            margin-bottom: 1rem;
        }
        .form-floating > .form-control {
            background: #FFFFFF;
            border: 1px solid rgba(41, 37, 40, 0.16);
            border-radius: 10px;
            height: 68px;
            padding: 1.45rem 0.95rem 0.55rem;
            color: var(--text-main);
            font-size: 0.98rem;
            font-weight: 500;
            box-shadow: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .form-floating > label {
            color: var(--text-soft);
            padding: 1rem 0.95rem;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .form-floating > .form-control:focus,
        .form-floating > .form-control:not(:placeholder-shown) {
            padding-top: 1.55rem;
            padding-bottom: 0.45rem;
        }
        .form-control:focus {
            border-color: #A85F6D;
            box-shadow: 0 0 0 0.22rem rgba(168, 95, 109, 0.14);
            background: #FFFFFF;
        }
        .form-floating > .form-control:-webkit-autofill,
        .form-floating > .form-control:-webkit-autofill:hover,
        .form-floating > .form-control:-webkit-autofill:focus {
            -webkit-text-fill-color: #292528;
            caret-color: #292528;
            -webkit-box-shadow: 0 0 0 1000px #FFFFFF inset;
            box-shadow: 0 0 0 1000px #FFFFFF inset;
            transition: background-color 9999s ease-out 0s;
        }
        .btn-login {
            background: #A85F6D;
            border: none;
            color: #FFFFFF;
            padding: 0.92rem 1rem;
            font-weight: 600;
            border-radius: 9px;
            font-size: 0.98rem;
            letter-spacing: 0.01em;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        }
        .btn-login:hover {
            background: #854653;
            transform: translateY(-1px);
            box-shadow: 0 6px 22px rgba(0, 0, 0, 0.1);
        }
        .back-link {
            text-decoration: none;
            color: #854653;
            font-size: 0.9rem;
            font-weight: 600;
            transition: color 0.2s ease;
        }
        .back-link:hover {
            color: #683944;
            text-decoration: underline;
        }
        @media (max-width: 576px) {
            .login-card {
                max-width: 92vw;
            }
            .login-body {
                padding: 1.2rem 1rem 1.2rem;
            }
            body {
                padding: 14px 10px;
            }
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <div class="brand-wrap">
                <div class="brand-box">
                    <img src="../../public/img/logoprincipal-transparente.png?v=20261008-2" alt="Bella Tela">
                </div>
            </div>
            <p class="mb-0">Acceso al Sistema</p>
            <div class="login-subtitle">Bella Tela · SIGIBET</div>
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