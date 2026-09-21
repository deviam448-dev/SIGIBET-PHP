<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../models/Venta.php';
require_once __DIR__ . '/../../models/Producto.php';
require_once __DIR__ . '/../../models/Cliente.php';
require_once __DIR__ . '/../../models/Configuracion.php';

/**
 * CONTROLADOR DE VENTAS (POS)
 */
class VentaController
{
    private Venta $modeloVenta;
    private Producto $modeloProducto;

    public function __construct()
    {
        $this->modeloVenta = new Venta();
        $this->modeloProducto = new Producto();
    }

    /**
     * Muestra la vista principal del Punto de Venta
     */
    public function index()
    {
        if (!isset($_SESSION['usuario'])) {
            header('Location: ../../views/Auth/login.php');
            exit;
        }
        require_once __DIR__ . '/../../views/admin/ventas.php';
    }

    /**
     * Busca productos en formato JSON para el frontend
     */
    public function buscarAjax()
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['usuario'])) {
            echo json_encode(['success' => false, 'mensaje' => 'Sesión expirada. Inicie sesión nuevamente.']);
            exit;
        }
        
        $busqueda = $_GET['q'] ?? '';
        $todos = $this->modeloProducto->obtenerTodos();
        
        // Filtrar productos que tengan stock > 0
        $disponibles = array_filter($todos, function($p) {
            return (int)$p['stock'] > 0;
        });

        if (!empty(trim($busqueda))) {
            $busqueda = strtolower(trim($busqueda));
            $disponibles = array_filter($disponibles, function($p) use ($busqueda) {
                return str_contains(strtolower($p['nombre']), $busqueda) || 
                       str_contains(strtolower($p['referencia']), $busqueda) || 
                       str_contains(strtolower($p['color']), $busqueda);
            });
        }

        echo json_encode(array_values($disponibles));
        exit;
    }

    /**
     * Procesa la venta recibida por AJAX (POST JSON)
     */
    public function procesarAjax()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'mensaje' => 'Método no permitido.']);
            exit;
        }

        if (!isset($_SESSION['usuario'])) {
            echo json_encode(['success' => false, 'mensaje' => 'Sesión expirada. Inicie sesión nuevamente.']);
            exit;
        }

        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON, true);

        if (!$input || empty($input['carrito'])) {
            echo json_encode(['success' => false, 'mensaje' => 'El carrito está vacío o los datos son inválidos.']);
            exit;
        }

        $carrito = $input['carrito'];
        $subtotal = 0;
        
        foreach ($carrito as $item) {
            if (!isset($item['id'], $item['cantidad'], $item['precio']) || (int)$item['cantidad'] <= 0) {
                echo json_encode(['success' => false, 'mensaje' => 'Datos de producto inválidos en el carrito.']);
                exit;
            }
            $subtotal += ((int)$item['cantidad'] * (float)$item['precio']);
        }

        // Calcular impuesto real desde BD para evitar manipulación
        $modeloConfig = new Configuracion();
        $config = $modeloConfig->obtenerConfiguracion();
        $impuestoConf = (float)($config['impuesto'] ?? 0);
        
        $iva = $subtotal * ($impuestoConf / 100);
        $total = $subtotal + $iva;
        
        $metodoPago = $input['metodo_pago'] ?? 'Efectivo';
        $clienteId = !empty($input['cliente_id']) ? (int)$input['cliente_id'] : null;
        $usuarioId = (int)$_SESSION['usuario']['id_usuario'];

        $resultado = $this->modeloVenta->registrarVentaCompleta($usuarioId, $carrito, $subtotal, $total, $metodoPago, $clienteId);

        echo json_encode($resultado);
        exit;
    }
}

// Router Simple (Front Controller Embedded)
if (basename($_SERVER['PHP_SELF']) === 'VentaController.php') {
    $controller = new VentaController();
    $accion = $_GET['action'] ?? '';

    switch ($accion) {
        case 'buscarAjax':
            $controller->buscarAjax();
            break;
        case 'procesarAjax':
            $controller->procesarAjax();
            break;
        default:
            echo json_encode(['success' => false, 'mensaje' => 'Acción no válida.']);
            exit;
    }
}
