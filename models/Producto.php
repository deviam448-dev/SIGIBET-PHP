<?php
require_once __DIR__ . '/../config/database.php';

/**
 * MODELO DE PRODUCTOS
 * 
 * Gestiona las operaciones de base de datos para la tabla `productos` (Catálogo de Telas).
 */
class Producto
{
    private \mysqli $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    /**
     * Registra un nuevo producto (tela) en la base de datos.
     */
    public function registrar(string $referencia, string $nombre, string $color, string $categoria, string $descripcion, float $precio, float $precio_compra, int $stock, int $stock_minimo, int $estado = 1)
    {
        $query = 'INSERT INTO productos (referencia, nombre, color, categoria, descripcion, precio, precio_compra, stock, stock_minimo, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        
        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param('sssssddiii', $referencia, $nombre, $color, $categoria, $descripcion, $precio, $precio_compra, $stock, $stock_minimo, $estado);
            $result = $stmt->execute();
            $stmt->close();
            return $result ? $this->conn->insert_id : false;
        }
        return false;
    }

    /**
     * Verifica si una referencia (código) ya existe para evitar duplicados.
     */
    public function referenciaExiste(string $referencia)
    {
        $query = 'SELECT id FROM productos WHERE referencia = ?';
        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param('s', $referencia);
            $stmt->execute();
            $stmt->store_result();
            $num_rows = $stmt->num_rows;
            $stmt->close();
            return $num_rows > 0;
        }
        return false;
    }

    /**
     * Obtiene todos los productos registrados.
     */
    public function obtenerTodos()
    {
        $query = 'SELECT id, referencia, nombre, color, categoria, descripcion, precio, precio_compra, stock, stock_minimo, estado, fecha_creacion FROM productos ORDER BY id DESC';
        $result = $this->conn->query($query);
        
        $productos = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $productos[] = $row;
            }
        }
        return $productos;
    }

    /**
     * Obtiene un producto específico por su ID.
     */
    public function obtenerPorId(int $id)
    {
        $query = 'SELECT id, referencia, nombre, color, categoria, descripcion, precio, precio_compra, stock, stock_minimo, estado, fecha_creacion FROM productos WHERE id = ?';
        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $producto = $result->fetch_assoc();
            $stmt->close();
            return $producto;
        }
        return null;
    }

    /**
     * Actualiza la información principal de un producto.
     */
    public function actualizar(int $id, array $datos)
    {
        $query = 'UPDATE productos SET referencia = ?, nombre = ?, color = ?, categoria = ?, descripcion = ?, precio = ?, precio_compra = ?, stock_minimo = ?, estado = ? WHERE id = ?';
        
        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param('sssssddiii', 
                $datos['referencia'], 
                $datos['nombre'], 
                $datos['color'], 
                $datos['categoria'], 
                $datos['descripcion'], 
                $datos['precio'], 
                $datos['precio_compra'], 
                $datos['stock_minimo'], 
                $datos['estado'], 
                $id
            );
            $result = $stmt->execute();
            if (!$result) {
                $error = $stmt->error;
                $stmt->close();
                return 'Error en la base de datos: ' . $error;
            }
            $stmt->close();
            return true;
        }
        return 'Error al preparar la consulta de actualización.';
    }

    /**
     * Actualiza el stock de un producto sumando o restando cantidad.
     */
    public function actualizarStock(int $id, int $cantidad, string $operacion = 'sumar')
    {
        $query = 'UPDATE productos SET stock = stock ';
        $query .= ($operacion === 'sumar') ? '+ ?' : '- ?';
        $query .= ' WHERE id = ?';

        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param('ii', $cantidad, $id);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }
        return false;
    }

    /**
     * Elimina un producto. Nota: Puede fallar si existen ventas ligadas a él por la restricción RESTRICT en MySQL.
     */
    public function eliminar(int $id)
    {
        $query = 'DELETE FROM productos WHERE id = ?';
        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param('i', $id);
            $result = $stmt->execute();
            if (!$result) {
                $error = $stmt->error;
                $stmt->close();
                return 'Error en la BD al eliminar (verifique que no tenga ventas): ' . $error;
            }
            $stmt->close();
            return true;
        }
        return 'Error al preparar la consulta.';
    }
}
?>