<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../models/Usuario.php';
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../../models/Auditoria.php';

/**
 * CONTROLADOR DE USUARIOS
 */
class UsuarioController
{
    private Usuario $modelo;

    public function __construct()
    {
        // Seguridad: SOLO Administradores pueden entrar aquí
        if (basename($_SERVER['PHP_SELF']) !== 'UsuarioController.php') {
            // Si es vista, redirigir
            if (!AuthController::esAdmin()) {
                header('Location: ../../views/admin/gproductos.php');
                exit;
            }
        } else {
            // Si es AJAX, devolver 403 json
            AuthController::requerirRol([1]); // 1 = Admin
        }
        
        $this->modelo = new Usuario();
    }

    public function index()
    {
        require_once __DIR__ . '/../../views/admin/usuarios.php';
    }

    public function listarAjax()
    {
        header('Content-Type: application/json');
        $usuarios = $this->modelo->obtenerTodos();
        echo json_encode($usuarios);
        exit;
    }

    public function guardarAjax()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'mensaje' => 'Método no permitido']);
            exit;
        }

        $id = !empty($_POST['id']) ? (int)$_POST['id'] : 0;
        
        $datos = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'usuario' => trim($_POST['usuario'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'rol_id' => (int)($_POST['rol_id'] ?? 2),
            'estado' => (int)($_POST['estado'] ?? 1),
            'password' => trim($_POST['password'] ?? '')
        ];

        if (empty($datos['nombre']) || empty($datos['usuario'])) {
            echo json_encode(['success' => false, 'mensaje' => 'Nombre y usuario son obligatorios.']);
            exit;
        }

        if ($id > 0) {
            $resultado = $this->modelo->actualizar($id, $datos);
            if ($resultado['success']) Auditoria::registrar('Usuarios', 'Editar Usuario', "Username: " . $datos['usuario']);
        } else {
            if (empty($datos['password'])) {
                echo json_encode(['success' => false, 'mensaje' => 'La contraseña es obligatoria para un usuario nuevo.']);
                exit;
            }
            $resultado = $this->modelo->registrar($datos['nombre'], $datos['usuario'], $datos['email'], $datos['telefono'], $datos['password'], $datos['rol_id'], $datos['estado']);
            if ($resultado['success']) Auditoria::registrar('Usuarios', 'Crear Usuario', "Username: " . $datos['usuario']);
        }

        echo json_encode($resultado);
        exit;
    }

    public function cambiarEstadoAjax()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'mensaje' => 'Método no permitido']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $estado = (int)($_POST['estado'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'mensaje' => 'ID inválido.']);
            exit;
        }

        // Prevenir desactivarse a sí mismo por error
        if ($id == $_SESSION['usuario']['id_usuario'] && $estado == 0) {
            echo json_encode(['success' => false, 'mensaje' => 'No puedes desactivar tu propia cuenta activa.']);
            exit;
        }

        $resultado = $this->modelo->cambiarEstado($id, $estado);
        if ($resultado['success']) {
            $txtEstado = $estado == 1 ? 'Activado' : 'Desactivado';
            Auditoria::registrar('Usuarios', 'Cambiar Estado', "ID Usuario: $id -> $txtEstado");
        }
        echo json_encode($resultado);
        exit;
    }
}

// Router Simple (Front Controller Embedded)
if (basename($_SERVER['PHP_SELF']) === 'UsuarioController.php') {
    $controller = new UsuarioController();
    $accion = $_GET['action'] ?? '';

    switch ($accion) {
        case 'listarAjax':
            $controller->listarAjax();
            break;
        case 'guardarAjax':
            $controller->guardarAjax();
            break;
        case 'cambiarEstadoAjax':
            $controller->cambiarEstadoAjax();
            break;
        default:
            echo json_encode(['success' => false, 'mensaje' => 'Acción no válida.']);
            exit;
    }
}
