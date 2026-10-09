<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../models/Configuracion.php';
$tipografia = htmlspecialchars((new Configuracion())->obtenerConfiguracion()['tipografia'], ENT_QUOTES, 'UTF-8');

$token = trim($_GET['token'] ?? '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva contraseña - Bella Tela</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 24px 16px;
            color: #292528;
            font-family: <?= $tipografia ?>;
            background: linear-gradient(90deg, rgba(28,25,27,.42), rgba(28,25,27,.2)),
                        url('../../public/img/Fondo.png') center / cover no-repeat fixed;
        }
        .login-card {
            width: 100%;
            max-width: 520px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,.74);
            border-radius: 18px;
            background: rgba(255,255,255,.97);
            box-shadow: 0 24px 70px rgba(28,25,27,.28);
        }
        .login-header {
            padding: 1.35rem 1.5rem 1.55rem;
            border-bottom: 3px solid #B9974E;
            color: #fff;
            text-align: center;
            background: linear-gradient(135deg, #1C191B, #39272E);
        }
        .brand-box {
            display: flex;
            width: 108px;
            height: 116px;
            align-items: center;
            justify-content: center;
            margin: 0 auto .45rem;
        }
        .brand-box img {
            width: 100%;
            max-height: 116px;
            object-fit: contain;
            filter: drop-shadow(0 2px 8px rgba(0,0,0,.2));
        }
        .login-header h1 {
            margin: 0;
            color: rgba(255,255,255,.94);
            font-size: 1.16rem;
            font-weight: 600;
        }
        .login-subtitle {
            margin-top: .35rem;
            color: rgba(255,255,255,.68);
            font-size: .82rem;
        }
        .login-body {
            padding: 1.8rem 1.6rem 1.45rem;
            background: rgba(255,255,255,.98);
        }
        .form-label {
            color: #70696D;
            font-size: .9rem;
            font-weight: 500;
        }
        .form-control {
            min-height: 58px;
            border: 1px solid rgba(41,37,40,.16);
            border-radius: 10px;
            color: #292528;
            box-shadow: none;
        }
        .form-control:focus {
            border-color: #A85F6D;
            box-shadow: 0 0 0 .22rem rgba(168,95,109,.14);
        }
        .btn-login {
            padding: .92rem 1rem;
            border: 0;
            border-radius: 9px;
            background: #A85F6D;
            color: #fff;
            font-size: .98rem;
            font-weight: 600;
            transition: background .2s ease, transform .2s ease;
        }
        .btn-login:hover {
            transform: translateY(-1px);
            background: #854653;
            color: #fff;
        }
        .back-link {
            color: #854653;
            font-size: .9rem;
            font-weight: 600;
            text-decoration: none;
        }
        .back-link:hover {
            color: #683944;
            text-decoration: underline;
        }
        @media (max-width: 576px) {
            body { padding: 14px 10px; }
            .login-card { max-width: 92vw; }
            .login-body { padding: 1.2rem 1rem; }
        }
    </style>
</head>
<body>
    <main class="login-card">
        <header class="login-header">
            <div class="brand-box">
                <img src="../../public/img/logoprincipal-transparente.png?v=20261008-2" alt="Bella Tela">
            </div>
            <h1>Nueva contraseña</h1>
            <div class="login-subtitle">Bella Tela · SIGIBET</div>
        </header>
        <div class="login-body">
            <p class="text-center mb-4">Crea una contraseña nueva de mínimo 8 caracteres para volver a ingresar al sistema.</p>
            <?php if ($token === ''): ?>
                <div class="alert alert-warning" role="alert">El enlace de recuperación no es válido. Solicita uno nuevo.</div>
                <div class="text-center"><a class="back-link" href="recuperar.php">Solicitar otro enlace</a></div>
            <?php else: ?>
                <form action="../../controllers/auth/AuthController.php?accion=restablecerPassword" method="POST">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="mb-3">
                        <label for="password" class="form-label">Nueva contraseña</label>
                        <input type="password" class="form-control form-control-lg" id="password" name="password"
                               minlength="8" required autocomplete="new-password">
                    </div>
                    <div class="mb-4">
                        <label for="password_confirmacion" class="form-label">Confirmar contraseña</label>
                        <input type="password" class="form-control form-control-lg" id="password_confirmacion"
                               name="password_confirmacion" minlength="8" required autocomplete="new-password">
                    </div>
                    <div class="d-grid mb-4">
                        <button class="btn btn-login btn-lg" type="submit">Guardar contraseña</button>
                    </div>
                </form>
                <div class="text-center"><a class="back-link" href="login.php">Volver al inicio de sesión</a></div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
