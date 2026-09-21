<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../models/Cliente.php';
require_once __DIR__ . '/../../models/Auditoria.php';

/**
 * CONTROLADOR DE CLIENTES
 */
class ClienteController
{
    private Cliente $modelo;

    public function __construct()
    {
        $this->modelo = new Cliente();
    }

    /**
     * Muestra la vista de clientes.
     */
    public function index()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ../../auth/login.php');
            exit;
        }
        require_once __DIR__ . '/../../views/admin/clientes.php';
    }

    /**
     * Obtiene los clientes (JSON).
     */
    public function listarAjax()
    {
        header('Content-Type: application/json');
        $busqueda = $_GET['q'] ?? '';
        $clientes = $this->modelo->obtenerTodos($busqueda);
        echo json_encode($clientes);
        exit;
    }

    /**
     * Obtiene el perfil del cliente (JSON) con su historial de ventas.
     */
    public function obtenerPerfilAjax()
    {
        header('Content-Type: application/json');
        $id = (int)($_GET['id'] ?? 0);
        
        $cliente = $this->modelo->obtenerPorId($id);
        if (!$cliente) {
            echo json_encode(['success' => false, 'mensaje' => 'Cliente no encontrado']);
            exit;
        }

        $historial = $this->modelo->obtenerHistorialVentas($id);
        $cliente['historial'] = $historial;

        echo json_encode(['success' => true, 'cliente' => $cliente]);
        exit;
    }

    /**
     * Guarda o actualiza un cliente.
     */
    public function guardarAjax()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'mensaje' => 'Método no permitido']);
            exit;
        }

        $id = !empty($_POST['id']) ? (int)$_POST['id'] : 0;
        $nombre = trim($_POST['nombre'] ?? '');
        $documento = trim($_POST['documento'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $observaciones = trim($_POST['observaciones'] ?? '');

        if (empty($nombre) || empty($documento)) {
            echo json_encode(['success' => false, 'mensaje' => 'El nombre y documento son obligatorios.']);
            exit;
        }

        if ($id > 0) {
            $resultado = $this->modelo->actualizar($id, $nombre, $documento, $telefono, $email, $direccion, $observaciones);
            if ($resultado['success']) Auditoria::registrar('Clientes', 'Editar Cliente', "Doc: $documento");
        } else {
            $resultado = $this->modelo->registrar($nombre, $documento, $telefono, $email, $direccion, $observaciones);
            if ($resultado['success']) Auditoria::registrar('Clientes', 'Crear Cliente', "Doc: $documento");
        }

        echo json_encode($resultado);
        exit;
    }

    /**
     * Elimina un cliente.
     */
    public function eliminarAjax()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'mensaje' => 'Método no permitido']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'mensaje' => 'ID inválido.']);
            exit;
        }

        $resultado = $this->modelo->eliminar($id);
        if ($resultado['success']) {
            Auditoria::registrar('Clientes', 'Eliminar Cliente', "ID Cliente: $id");
        }
        echo json_encode($resultado);
        exit;
    }
}

// Router Simple (Front Controller Embedded)
if (basename($_SERVER['PHP_SELF']) === 'ClienteController.php') {
    $controller = new ClienteController();
    $accion = $_GET['action'] ?? '';

    switch ($accion) {
        case 'listarAjax':
            $controller->listarAjax();
            break;
        case 'obtenerPerfilAjax':
            $controller->obtenerPerfilAjax();
            break;
        case 'guardarAjax':
            $controller->guardarAjax();
            break;
        case 'eliminarAjax':
            $controller->eliminarAjax();
            break;
        default:
            echo json_encode(['success' => false, 'mensaje' => 'Acción no válida.']);
            exit;
    }
}
