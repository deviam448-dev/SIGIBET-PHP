<?php
require_once __DIR__ . '/../config/database.php';

/**
 * MODELO DE REPORTES
 * 
 * Gestiona las operaciones de lectura y agrupación para el historial de ventas y reportes.
 */
class Reporte
{
    private \mysqli $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    /**
     * HU-019: Obtiene una venta específica por su ID incluyendo sus detalles de productos.
     * Se usa para generar la factura individual desde el historial de transacciones.
     */
    public function obtenerVentaPorId(int $ventaId): ?array
    {
        /* ── Query principal: cabecera de la venta + nombre del vendedor ── */
        $sql = "SELECT v.id, v.codigo_factura, v.usuario_id, v.subtotal, v.total, v.metodo_pago, v.fecha,
                       u.nombre AS usuario_nombre,
                       CASE
                           WHEN c.id IS NULL THEN 'Consumidor Final'
                           WHEN c.nombre IS NULL THEN CONCAT('Cliente #', c.id)
                           ELSE c.nombre
                       END AS cliente_nombre,
                       c.id AS cliente_id,
                       (SELECT SUM(cantidad) FROM detalle_ventas WHERE venta_id = v.id) AS total_articulos
                FROM ventas v
                LEFT JOIN usuarios u ON v.usuario_id = u.id
                LEFT JOIN clientes c ON v.cliente_id = c.id
                WHERE v.id = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $ventaId);
        $stmt->execute();
        $venta = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$venta) {
            return null; // Venta no existe
        }

        /* ── Query de detalle: productos vendidos en esa venta ── */
        $sqlDetalle = "SELECT dv.cantidad, dv.precio_unitario, dv.subtotal,
                              p.nombre, p.referencia, p.color, p.categoria
                       FROM detalle_ventas dv
                       INNER JOIN productos p ON dv.producto_id = p.id
                       WHERE dv.venta_id = ?
                       ORDER BY p.nombre ASC";

        $stmtD = $this->conn->prepare($sqlDetalle);
        $stmtD->bind_param("i", $ventaId);
        $stmtD->execute();
        $venta['detalles'] = $stmtD->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmtD->close();

        return $venta;
    }
    public function obtenerHistorialVentas(array $filtros = []): array
    {
        $sql = "SELECT v.id, v.codigo_factura, v.subtotal, v.total, v.metodo_pago, v.fecha, 
                       u.nombre AS usuario_nombre, 
                       (SELECT SUM(cantidad) FROM detalle_ventas WHERE venta_id = v.id) as total_articulos
                FROM ventas v
                LEFT JOIN usuarios u ON v.usuario_id = u.id
                WHERE 1=1";
        
        $params = [];
        $types = "";

        if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])) {
            $sql .= " AND DATE(v.fecha) BETWEEN ? AND ?";
            $params[] = $filtros['fecha_inicio'];
            $params[] = $filtros['fecha_fin'];
            $types .= "ss";
        }

        if (!empty($filtros['usuario_id'])) {
            $sql .= " AND v.usuario_id = ?";
            $params[] = $filtros['usuario_id'];
            $types .= "i";
        }

        $sql .= " ORDER BY v.fecha DESC";

        $stmt = $this->conn->prepare($sql);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $resultado = $stmt->get_result();
        $ventas = $resultado->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Obtener detalles para cada venta
        foreach ($ventas as &$venta) {
            $sqlDetalle = "SELECT dv.cantidad, dv.precio_unitario, dv.subtotal, p.nombre, p.referencia 
                           FROM detalle_ventas dv
                           INNER JOIN productos p ON dv.producto_id = p.id
                           WHERE dv.venta_id = ?";
            $stmtDetalle = $this->conn->prepare($sqlDetalle);
            $stmtDetalle->bind_param("i", $venta['id']);
            $stmtDetalle->execute();
            $venta['detalles'] = $stmtDetalle->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmtDetalle->close();
        }

        return $ventas;
    }

    /**
     * Obtiene los productos más vendidos.
     */
    public function obtenerTopProductos(int $limite = 5, array $filtros = []): array
    {
        $sql = "SELECT p.id, p.referencia, p.nombre, p.color, SUM(dv.cantidad) as total_vendido
                FROM detalle_ventas dv
                INNER JOIN productos p ON dv.producto_id = p.id
                INNER JOIN ventas v ON dv.venta_id = v.id
                WHERE 1=1";
        
        $params = [];
        $types = "";

        if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])) {
            $sql .= " AND DATE(v.fecha) BETWEEN ? AND ?";
            $params[] = $filtros['fecha_inicio'];
            $params[] = $filtros['fecha_fin'];
            $types .= "ss";
        }

        $sql .= " GROUP BY p.id ORDER BY total_vendido DESC LIMIT ?";
        $params[] = $limite;
        $types .= "i";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $resultado = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $resultado;
    }

    /**
     * Obtiene las ventas agrupadas por periodo (diario).
     */
    public function obtenerVentasPorPeriodo(array $filtros = []): array
    {
        $sql = "SELECT DATE(fecha) as fecha_corta, SUM(total) as ingresos, COUNT(id) as cantidad_ventas
                FROM ventas
                WHERE 1=1";
        
        $params = [];
        $types = "";

        if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])) {
            $sql .= " AND DATE(fecha) BETWEEN ? AND ?";
            $params[] = $filtros['fecha_inicio'];
            $params[] = $filtros['fecha_fin'];
            $types .= "ss";
        }

        $sql .= " GROUP BY DATE(fecha) ORDER BY DATE(fecha) ASC LIMIT 30";

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $resultado = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $resultado;
    }

    /**
     * Obtiene el resumen general.
     */
    public function obtenerResumenGeneral(array $filtros = []): array
    {
        // Valor total del inventario
        $resInventario = $this->conn->query("SELECT SUM(stock * precio_compra) as valor_total_inventario FROM productos WHERE estado = 1");
        $valorInventario = $resInventario->fetch_assoc()['valor_total_inventario'] ?? 0;

        // Ingresos y cantidad de ventas
        $sqlVentas = "SELECT SUM(total) as ingresos_totales, COUNT(id) as total_ventas FROM ventas WHERE 1=1";
        $params = [];
        $types = "";

        if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])) {
            $sqlVentas .= " AND DATE(fecha) BETWEEN ? AND ?";
            $params[] = $filtros['fecha_inicio'];
            $params[] = $filtros['fecha_fin'];
            $types .= "ss";
        }

        $stmt = $this->conn->prepare($sqlVentas);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $resVentas = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return [
            'valor_inventario' => (float)$valorInventario,
            'ingresos_totales' => (float)($resVentas['ingresos_totales'] ?? 0),
            'total_ventas' => (int)($resVentas['total_ventas'] ?? 0)
        ];
    }
}