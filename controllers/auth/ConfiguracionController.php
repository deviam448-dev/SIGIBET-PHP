<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../models/Configuracion.php';
require_once __DIR__ . '/../../models/Auditoria.php';
require_once __DIR__ . '/AuthController.php';

/**
 * CONTROLADOR DE CONFIGURACIÓN
 */
class ConfiguracionController
{
    private Configuracion $modelo;

    public function __construct()
    {
        if (basename($_SERVER['PHP_SELF']) !== 'ConfiguracionController.php') {
            if (!AuthController::esAdmin()) {
                header('Location: ../../views/admin/gproductos.php');
                exit;
            }
        } else {
            AuthController::requerirRol([1]);
        }
        
        $this->modelo = new Configuracion();
    }

    public function index()
    {
        $config = $this->modelo->obtenerConfiguracion();
        require_once __DIR__ . '/../../views/admin/configuracion.php';
    }

    public function obtenerAjax()
    {
        header('Content-Type: application/json');
        echo json_encode($this->modelo->obtenerConfiguracion());
        exit;
    }

    public function guardarAjax()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'mensaje' => 'Método no permitido']);
            exit;
        }

        $datos = [
            'nombre_empresa' => trim($_POST['nombre_empresa'] ?? ''),
            'nit' => trim($_POST['nit'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'direccion' => trim($_POST['direccion'] ?? ''),
            'impuesto' => (float)($_POST['impuesto'] ?? 0),
            'tipo_facturacion' => trim($_POST['tipo_facturacion'] ?? 'Régimen Simplificado'),
            'color_botones' => trim($_POST['color_botones'] ?? '#A85F6D'),
            'color_menu' => trim($_POST['color_menu'] ?? '#1C191B'),
            'color_acento' => trim($_POST['color_acento'] ?? '#B9BF66'),
            'tipografia' => trim($_POST['tipografia'] ?? 'Inter')
        ];

        if (empty($datos['nombre_empresa'])) {
            echo json_encode(['success' => false, 'mensaje' => 'El nombre de la empresa es obligatorio.']);
            exit;
        }

        $resultado = $this->modelo->actualizar($datos);
        
        if ($resultado['success']) {
            Auditoria::registrar(
                'Configuración',
                'Actualizar Ajustes',
                "Empresa: " . $datos['nombre_empresa'] . " | IVA: " . $datos['impuesto'] .
                "% | Tipografía: " . $datos['tipografia']
            );
            $_SESSION['tema_colores'] = $datos['color_botones'];
        }

        echo json_encode($resultado);
        exit;
    }

    public function guardarAparienciaAjax()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'mensaje' => 'Método no permitido.']);
            exit;
        }

        $resultado = $this->modelo->actualizarApariencia([
            'color_botones' => $_POST['color_botones'] ?? '',
            'color_menu' => $_POST['color_menu'] ?? '',
            'color_acento' => $_POST['color_acento'] ?? '',
            'tipografia' => $_POST['tipografia'] ?? ''
        ]);

        if ($resultado['success']) {
            $_SESSION['tema_colores'] = strtoupper(trim($_POST['color_botones']));
            Auditoria::registrar('Configuración', 'Actualizar Apariencia', 'Colores y tipografía');
        } else {
            http_response_code(400);
        }

        echo json_encode($resultado);
        exit;
    }
}

// Router Simple
if (basename($_SERVER['PHP_SELF']) === 'ConfiguracionController.php') {
    $controller = new ConfiguracionController();
    $accion = $_GET['action'] ?? '';

    switch ($accion) {
        case 'obtenerAjax':
            $controller->obtenerAjax();
            break;
        case 'guardarAjax':
            $controller->guardarAjax();
            break;
        case 'guardarAparienciaAjax':
            $controller->guardarAparienciaAjax();
            break;
        default:
            echo json_encode(['success' => false, 'mensaje' => 'Acción no válida.']);
            exit;
    }
}
