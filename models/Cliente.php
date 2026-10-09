<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Consulta los clientes anónimos creados automáticamente por cada compra.
 */
class Cliente
{
    private \mysqli $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    public function obtenerTodos(string $busqueda = ''): array
    {
        $sql = "
            SELECT
                c.id,
                c.fecha_creacion,
                v.id AS venta_id,
                v.codigo_factura,
                v.fecha AS fecha_venta,
                v.total,
                v.metodo_pago
            FROM clientes c
            INNER JOIN ventas v ON v.cliente_id = c.id
            WHERE c.nombre IS NULL
              AND c.documento IS NULL
        ";

        if ($busqueda !== '') {
            $sql .= " AND (CAST(c.id AS CHAR) LIKE ? OR v.codigo_factura LIKE ?)";
            $busqueda = '%' . $busqueda . '%';
            $stmt = $this->conn->prepare($sql . ' ORDER BY c.id DESC');
            if (!$stmt) {
                throw new RuntimeException('No se pudo preparar la consulta de clientes: ' . $this->conn->error);
            }
            $stmt->bind_param('ss', $busqueda, $busqueda);
        } else {
            $stmt = $this->conn->prepare($sql . ' ORDER BY c.id DESC');
            if (!$stmt) {
                throw new RuntimeException('No se pudo preparar la consulta de clientes: ' . $this->conn->error);
            }
        }

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new RuntimeException('No se pudieron consultar las compras: ' . $error);
        }

        $clientes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $clientes;
    }
}
