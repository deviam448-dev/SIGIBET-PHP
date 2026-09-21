<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../models/Reporte.php';

/**
 * CONTROLADOR DE REPORTES E HISTORIAL
 * Acceso exclusivo para administradores (rol_id = 1)
 */
class ReporteController
{
    private Reporte $modelo;

    public function __construct()
    {
        $this->modelo = new Reporte();
    }

    /**
     * Verifica que el usuario esté autenticado y sea administrador.
     * Retorna true si está autorizado; de lo contrario envía respuesta y termina.
     */
    private function verificarAdmin(bool $esAjax = false): void
    {
        $usuario = $_SESSION['usuario'] ?? null;

        if (!$usuario) {
            if ($esAjax) {
                echo json_encode(['success' => false, 'mensaje' => 'Sesión expirada. Inicie sesión nuevamente.']);
            } else {
                header('Location: ../../views/Auth/login.php');
            }
            exit;
        }

        if ((int)($usuario['id_rol'] ?? 0) !== 1) {
            if ($esAjax) {
                echo json_encode(['success' => false, 'mensaje' => 'Acceso denegado. Solo los administradores pueden ver los reportes.']);
            } else {
                // Redirigir al POS del empleado si intenta acceder directamente
                header('Location: ../../views/seller/mis_ventas.php');
            }
            exit;
        }
    }

    /**
     * Muestra la vista principal de Reportes e Historial
     */
    public function index()
    {
        $this->verificarAdmin(false);
        require_once __DIR__ . '/../../views/admin/reportes.php';
    }

    /**
     * HU-019: Devuelve JSON con los datos de UNA venta específica para generar la factura.
     * Usa obtenerVentaPorId() — query directa por ID, eficiente.
     */
    public function facturaAjax()
    {
        header('Content-Type: application/json');
        $this->verificarAdmin(true);

        $ventaId = (int)($_GET['venta_id'] ?? 0);
        if ($ventaId <= 0) {
            echo json_encode(['success' => false, 'mensaje' => 'ID de venta inválido.']);
            exit;
        }

        try {
            /* Obtener la venta por ID directo (sin cargar todo el historial) */
            $venta = $this->modelo->obtenerVentaPorId($ventaId);

            if (!$venta) {
                echo json_encode(['success' => false, 'mensaje' => 'Venta no encontrada.']);
                exit;
            }

            /* Obtener datos de la empresa para el encabezado de la factura */
            require_once __DIR__ . '/../../models/Configuracion.php';
            $modeloConf = new Configuracion();
            $config     = $modeloConf->obtenerConfiguracion();

            echo json_encode(['success' => true, 'venta' => $venta, 'config' => $config]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'mensaje' => $e->getMessage()]);
        }
        exit;
    }
    public function obtenerDatosAjax()
    {
        header('Content-Type: application/json');
        $this->verificarAdmin(true);

        $filtros = [];
        if (!empty($_GET['fecha_inicio'])) $filtros['fecha_inicio'] = $_GET['fecha_inicio'];
        if (!empty($_GET['fecha_fin']))    $filtros['fecha_fin']    = $_GET['fecha_fin'];
        if (!empty($_GET['usuario_id']))   $filtros['usuario_id']   = (int)$_GET['usuario_id'];

        try {
            $resumen          = $this->modelo->obtenerResumenGeneral($filtros);
            $ventasPorPeriodo = $this->modelo->obtenerVentasPorPeriodo($filtros);
            $topProductos     = $this->modelo->obtenerTopProductos(5, $filtros);
            $historial        = $this->modelo->obtenerHistorialVentas($filtros);

            echo json_encode([
                'success'         => true,
                'resumen'         => $resumen,
                'grafico_ingresos'=> $ventasPorPeriodo,
                'grafico_top'     => $topProductos,
                'historial'       => $historial
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'mensaje' => $e->getMessage()]);
        }
        exit;
    }
}

// Router Simple (Front Controller Embedded)
if (basename($_SERVER['PHP_SELF']) === 'ReporteController.php') {
    $controller = new ReporteController();
    $accion = $_GET['action'] ?? '';

    switch ($accion) {
        case 'obtenerDatosAjax':
            $controller->obtenerDatosAjax();
            break;
        case 'facturaAjax':
            $controller->facturaAjax();
            break;
        default:
            echo json_encode(['success' => false, 'mensaje' => 'Acción no válida.']);
            exit;
    }
}
