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
        $columnas = $this->conn->query("SHOW COLUMNS FROM productos LIKE 'imagen'");
        if ($columnas && $columnas->num_rows === 0) {
            $this->conn->query("ALTER TABLE productos ADD COLUMN imagen VARCHAR(255) NULL AFTER descripcion");
        }
    }

    /**
     * Registra un nuevo producto (tela) en la base de datos.
     */
    public function registrar(string $referencia, string $nombre, string $color, string $categoria, string $descripcion, float $precio, float $precio_compra, int $stock, int $stock_minimo, int $estado = 1, ?string $imagen = null)
    {
        $query = 'INSERT INTO productos (referencia, nombre, color, categoria, descripcion, imagen, precio, precio_compra, stock, stock_minimo, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        
        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param('ssssssddiii', $referencia, $nombre, $color, $categoria, $descripcion, $imagen, $precio, $precio_compra, $stock, $stock_minimo, $estado);
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
        $query = 'SELECT id, referencia, nombre, color, categoria, descripcion, imagen, precio, precio_compra, stock, stock_minimo, estado, fecha_creacion FROM productos ORDER BY id DESC';
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
        $query = 'SELECT id, referencia, nombre, color, categoria, descripcion, imagen, precio, precio_compra, stock, stock_minimo, estado, fecha_creacion FROM productos WHERE id = ?';
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
        $actualizarImagen = array_key_exists('imagen', $datos);
        $query = 'UPDATE productos SET referencia = ?, nombre = ?, color = ?, categoria = ?, descripcion = ?, precio = ?, precio_compra = ?, stock_minimo = ?, estado = ?';
        if ($actualizarImagen) $query .= ', imagen = ?';
        $query .= ' WHERE id = ?';
        
        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            if ($actualizarImagen) {
                $stmt->bind_param('sssssddiisi', $datos['referencia'], $datos['nombre'], $datos['color'], $datos['categoria'], $datos['descripcion'], $datos['precio'], $datos['precio_compra'], $datos['stock_minimo'], $datos['estado'], $datos['imagen'], $id);
            } else {
                $stmt->bind_param('sssssddiii', $datos['referencia'], $datos['nombre'], $datos['color'], $datos['categoria'], $datos['descripcion'], $datos['precio'], $datos['precio_compra'], $datos['stock_minimo'], $datos['estado'], $id);
            }
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
     * Archiva un producto sin modificar sus existencias ni su historial.
     */
    public function archivar(int $id): bool
    {
        $stmt = $this->conn->prepare('UPDATE productos SET estado = 0 WHERE id = ? AND estado = 1');
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('i', $id);
        $resultado = $stmt->execute() && $stmt->affected_rows === 1;
        $stmt->close();
        return $resultado;
    }
}
?>