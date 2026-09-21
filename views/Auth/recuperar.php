<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña - Bella Tela</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { min-height:100vh; display:grid; place-items:center; margin:0; font-family:'Inter',sans-serif; background:linear-gradient(135deg,rgba(57,40,35,.84),rgba(80,58,51,.58)),url('../../public/img/Fondo.png') center/cover fixed; }
        .card-recovery { width:min(92vw,460px); background:rgba(255,255,255,.97); border-radius:20px; padding:2.2rem; box-shadow:0 24px 60px rgba(34,18,15,.35); }
        .brand { max-width:190px; display:block; margin:0 auto 1.5rem; }
        .btn-main { background:#B87983; border-color:#B87983; color:#fff; }
        .btn-main:hover { background:#9E626D; border-color:#9E626D; color:#fff; }
        .back-link { color:#9E626D; text-decoration:none; }
    </style>
</head>
<body>
    <main class="card-recovery">
        <img src="../../public/img/logoprincipal.png" alt="Bella Tela" class="brand">
        <h1 class="h4 fw-bold text-center mb-2">Recuperar contraseña</h1>
        <p class="text-muted text-center mb-4">Escribe tu usuario o correo y recibirás un enlace de recuperación.</p>
        <form action="../../controllers/auth/AuthController.php?accion=solicitarRecuperacion" method="POST">
            <label for="identificador" class="form-label fw-semibold">Usuario o correo electrónico</label>
            <input type="text" class="form-control form-control-lg mb-3" id="identificador" name="identificador" required autocomplete="username">
            <button class="btn btn-main btn-lg w-100" type="submit">Enviar instrucciones</button>
        </form>
        <div class="text-center mt-4"><a class="back-link" href="login.php">Volver al inicio de sesión</a></div>
    </main>
</body>
</html>
