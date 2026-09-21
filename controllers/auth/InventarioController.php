<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../models/Producto.php';
require_once __DIR__ . '/../../models/Movimiento.php';

/**
 * CONTROLADOR DE INVENTARIO
 * 
 * Se encarga de consolidar los datos de Productos y Movimientos para el dashboard de Inventario.
 */
class InventarioController
{
    private Producto $modeloProducto;
    private Movimiento $modeloMovimiento;

    public function __construct()
    {
        $this->modeloProducto = new Producto();
        $this->modeloMovimiento = new Movimiento();
    }

    /**
     * Obtiene todos los datos necesarios para renderizar el Dashboard de Inventario
     * 
     * @return array [productos, movimientos, valor_total, bajo_stock_count]
     */
    public function obtenerDatosDashboard()
    {
        $productos = $this->modeloProducto->obtenerTodos();
        $movimientos = $this->modeloMovimiento->obtenerHistorial(50); // Últimos 50 movimientos
        
        $valor_total = 0;
        $bajo_stock_count = 0;

        foreach ($productos as $p) {
            $valor_total += ($p['precio_compra'] * $p['stock']);
            if ($p['stock'] <= $p['stock_minimo']) {
                $bajo_stock_count++;
            }
        }

        return [
            'productos' => $productos,
            'movimientos' => $movimientos,
            'valor_total' => $valor_total,
            'bajo_stock_count' => $bajo_stock_count
        ];
    }
}
?>
