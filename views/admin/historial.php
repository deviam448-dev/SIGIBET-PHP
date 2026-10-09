<?php
require_once __DIR__ . '/../../controllers/auth/AuthController.php';

if (!AuthController::usuarioActual()) {
    header('Location: ../Auth/login.php');
    exit;
}

if (AuthController::esAdmin()) {
    require_once __DIR__ . '/reportes.php';
    exit;
}

header('Location: ../seller/historial.php');
exit;
