<?php
require_once __DIR__ . '/../config/database.php';

/**
 * MODELO DE CLIENTES
 * 
 * Gestiona las operaciones CRUD para la tabla `clientes`.
 */
class Cliente
{
    private \mysqli $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    /**
     * Obtiene todos los clientes, opcionalmente filtrados.
     */
    public function obtenerTodos(string $busqueda = ''): array
    {
        $sql = "SELECT * FROM clientes WHERE 1=1";
        $params = [];
        $types = "";

        if (!empty($busqueda)) {
            $sql .= " AND (nombre LIKE ? OR documento LIKE ?)";
            $busquedaStr = "%" . $busqueda . "%";
            $params = [$busquedaStr, $busquedaStr];
            $types = "ss";
        }

        $sql .= " ORDER BY nombre ASC";

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
     * Obtiene un cliente por su ID.
     */
    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM clientes WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        return $resultado ?: null;
    }

    /**
     * Verifica si una cédula/documento ya existe.
     */
    public function existeDocumento(string $documento, ?int $excluirId = null): bool
    {
        $sql = "SELECT id FROM clientes WHERE documento = ?";
        $params = [$documento];
        $types = "s";

        if ($excluirId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excluirId;
            $types .= "i";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $existe = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        return $existe;
    }

    /**
     * Registra un nuevo cliente.
     */
    public function registrar(string $nombre, string $documento, string $telefono, string $email, string $direccion, string $observaciones): array
    {
        if ($this->existeDocumento($documento)) {
            return ['success' => false, 'mensaje' => 'El número de documento ya se encuentra registrado.'];
        }

        $stmt = $this->conn->prepare("INSERT INTO clientes (nombre, documento, telefono, email, direccion, observaciones) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $nombre, $documento, $telefono, $email, $direccion, $observaciones);
        
        if ($stmt->execute()) {
            $id = $this->conn->insert_id;
            $stmt->close();
            return ['success' => true, 'mensaje' => 'Cliente registrado exitosamente.', 'id' => $id];
        } else {
            $error = $stmt->error;
            $stmt->close();
            return ['success' => false, 'mensaje' => 'Error al registrar cliente: ' . $error];
        }
    }

    /**
     * Actualiza un cliente existente.
     */
    public function actualizar(int $id, string $nombre, string $documento, string $telefono, string $email, string $direccion, string $observaciones): array
    {
        if ($this->existeDocumento($documento, $id)) {
            return ['success' => false, 'mensaje' => 'El número de documento ya está en uso por otro cliente.'];
        }

        $stmt = $this->conn->prepare("UPDATE clientes SET nombre = ?, documento = ?, telefono = ?, email = ?, direccion = ?, observaciones = ? WHERE id = ?");
        $stmt->bind_param("ssssssi", $nombre, $documento, $telefono, $email, $direccion, $observaciones, $id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'mensaje' => 'Cliente actualizado exitosamente.'];
        } else {
            $error = $stmt->error;
            $stmt->close();
            return ['success' => false, 'mensaje' => 'Error al actualizar cliente: ' . $error];
        }
    }

    /**
     * Elimina un cliente.
     */
    public function eliminar(int $id): array
    {
        // Verificar si tiene ventas asociadas
        $stmtVentas = $this->conn->prepare("SELECT id FROM ventas WHERE cliente_id = ? LIMIT 1");
        $stmtVentas->bind_param("i", $id);
        $stmtVentas->execute();
        $tieneVentas = $stmtVentas->get_result()->num_rows > 0;
        $stmtVentas->close();

        if ($tieneVentas) {
            return ['success' => false, 'mensaje' => 'No se puede eliminar el cliente porque tiene ventas asociadas.'];
        }

        $stmt = $this->conn->prepare("DELETE FROM clientes WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'mensaje' => 'Cliente eliminado exitosamente.'];
        } else {
            $error = $stmt->error;
            $stmt->close();
            return ['success' => false, 'mensaje' => 'Error al eliminar cliente: ' . $error];
        }
    }

    /**
     * Obtiene el historial de compras de un cliente.
     */
    public function obtenerHistorialVentas(int $clienteId): array
    {
        $sql = "SELECT v.id, v.codigo_factura, v.fecha, v.total, v.metodo_pago,
                       (SELECT SUM(cantidad) FROM detalle_ventas WHERE venta_id = v.id) as total_articulos
                FROM ventas v
                WHERE v.cliente_id = ?
                ORDER BY v.fecha DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $clienteId);
        $stmt->execute();
        $resultado = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $resultado;
    }
}