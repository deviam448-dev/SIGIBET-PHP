<?php
require_once __DIR__ . '/../config/database.php';

/**
 * MODELO DE MOVIMIENTOS DE INVENTARIO
 * 
 * Gestiona el historial de entradas, salidas y ajustes de stock en la tabla `movimientos`.
 */
class Movimiento
{
    private \mysqli $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    /**
     * Registra un nuevo movimiento de inventario.
     * 
     * @param int $producto_id
     * @param int $usuario_id
     * @param string $tipo ('ENTRADA', 'SALIDA_VENTA', 'AJUSTE', 'CREACION')
     * @param int $cantidad (Valor absoluto de la cantidad movida)
     * @param int $stock_anterior
     * @param int $stock_nuevo
     * @param string $motivo
     * @return bool
     */
    public function registrar(int $producto_id, int $usuario_id, string $tipo, int $cantidad, int $stock_anterior, int $stock_nuevo, string $motivo)
    {
        $query = 'INSERT INTO movimientos (producto_id, usuario_id, tipo, cantidad, stock_anterior, stock_nuevo, motivo) VALUES (?, ?, ?, ?, ?, ?, ?)';
        
        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param('iisiiss', $producto_id, $usuario_id, $tipo, $cantidad, $stock_anterior, $stock_nuevo, $motivo);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }
        return false;
    }

    /**
     * Obtiene el historial completo de movimientos, con detalles del producto y usuario.
     * 
     * @param int $limite Límite de registros a traer (opcional)
     * @return array
     */
    public function obtenerHistorial(int $limite = 100)
    {
        $query = 'SELECT m.id, m.tipo, m.cantidad, m.stock_anterior, m.stock_nuevo, m.motivo, m.fecha, 
                         p.referencia, p.nombre as producto_nombre, 
                         u.nombre as usuario_nombre 
                  FROM movimientos m
                  JOIN productos p ON m.producto_id = p.id
                  JOIN usuarios u ON m.usuario_id = u.id
                  ORDER BY m.fecha DESC LIMIT ?';
                  
        $stmt = $this->conn->prepare($query);
        $movimientos = [];
        
        if ($stmt) {
            $stmt->bind_param('i', $limite);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $movimientos[] = $row;
            }
            $stmt->close();
        }
        return $movimientos;
    }

    /**
     * Obtiene un resumen de los productos más vendidos (basado en SALIDA_VENTA).
     */
    public function obtenerMasVendidos(int $limite = 5)
    {
        $query = "SELECT p.nombre, p.referencia, SUM(m.cantidad) as total_vendido 
                  FROM movimientos m
                  JOIN productos p ON m.producto_id = p.id
                  WHERE m.tipo = 'SALIDA_VENTA'
                  GROUP BY m.producto_id
                  ORDER BY total_vendido DESC LIMIT ?";
                  
        $stmt = $this->conn->prepare($query);
        $mas_vendidos = [];
        
        if ($stmt) {
            $stmt->bind_param('i', $limite);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $mas_vendidos[] = $row;
            }
            $stmt->close();
        }
        return $mas_vendidos;
    }
}
?>