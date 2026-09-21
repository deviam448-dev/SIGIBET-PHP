<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../models/Auditoria.php';
require_once __DIR__ . '/AuthController.php';

/**
 * CONTROLADOR DE AUDITORÍA
 */
class AuditoriaController
{
    private Auditoria $modelo;

    public function __construct()
    {
        // Seguridad: SOLO Administradores pueden entrar aquí
        if (basename($_SERVER['PHP_SELF']) !== 'AuditoriaController.php') {
            if (!AuthController::esAdmin()) {
                header('Location: ../../views/admin/gproductos.php');
                exit;
            }
        } else {
            AuthController::requerirRol([1]);
        }
        
        $this->modelo = new Auditoria();
    }

    public function index()
    {
        $modulos = $this->modelo->obtenerModulosUnicos();
        require_once __DIR__ . '/../../views/admin/auditoria.php';
    }

    public function listarAjax()
    {
        header('Content-Type: application/json');
        
        $modulo = $_GET['modulo'] ?? '';
        $usuario = $_GET['usuario'] ?? '';
        $fechaInicio = $_GET['fechaInicio'] ?? '';
        $fechaFin = $_GET['fechaFin'] ?? '';
        
        $logs = $this->modelo->obtenerLogs($modulo, $usuario, $fechaInicio, $fechaFin);
        echo json_encode($logs);
        exit;
    }
}

// Router Simple (Front Controller Embedded)
if (basename($_SERVER['PHP_SELF']) === 'AuditoriaController.php') {
    $controller = new AuditoriaController();
    $accion = $_GET['action'] ?? '';

    switch ($accion) {
        case 'listarAjax':
            $controller->listarAjax();
            break;
        default:
            echo json_encode(['success' => false, 'mensaje' => 'Acción no válida.']);
            exit;
    }
}
