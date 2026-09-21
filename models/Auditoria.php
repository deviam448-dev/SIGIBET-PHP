<?php
require_once __DIR__ . '/../config/database.php';

/**
 * MODELO DE AUDITORÍA
 * 
 * Gestiona el log de actividades y la consulta del mismo.
 */
class Auditoria
{
    /**
     * Registra una acción en el log de auditoría.
     * 
     * @param string $modulo Módulo donde ocurre (Ej. 'Ventas', 'Productos')
     * @param string $accion Acción realizada (Ej. 'Crear', 'Eliminar')
     * @param string $detalles Detalles extra (Ej. 'Se creó producto X')
     */
    public static function registrar(string $modulo, string $accion, string $detalles = '')
    {
        global $conn;
        
        // Obtener el usuario actual
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $usuario_id = $_SESSION['usuario']['id_usuario'] ?? null;
        if (!$usuario_id) return false;

        $stmt = $conn->prepare("INSERT INTO auditoria (usuario_id, modulo, accion, detalles) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("isss", $usuario_id, $modulo, $accion, $detalles);
            $res = $stmt->execute();
            $stmt->close();
            return $res;
        }
        return false;
    }

    /**
     * Obtiene los logs de auditoría con filtros opcionales.
     */
    public function obtenerLogs(string $modulo = '', string $usuario = '', string $fechaInicio = '', string $fechaFin = ''): array
    {
        global $conn;
        
        $sql = "SELECT a.*, u.nombre as usuario_nombre, u.usuario as username, r.nombre as rol_nombre
                FROM auditoria a
                JOIN usuarios u ON a.usuario_id = u.id
                JOIN roles r ON u.rol_id = r.id
                WHERE 1=1";
        
        $params = [];
        $types = "";

        if (!empty($modulo)) {
            $sql .= " AND a.modulo = ?";
            $params[] = $modulo;
            $types .= "s";
        }
        
        if (!empty($usuario)) {
            $sql .= " AND (u.nombre LIKE ? OR u.usuario LIKE ?)";
            $busquedaStr = "%" . $usuario . "%";
            $params[] = $busquedaStr;
            $params[] = $busquedaStr;
            $types .= "ss";
        }
        
        if (!empty($fechaInicio)) {
            $sql .= " AND DATE(a.fecha) >= ?";
            $params[] = $fechaInicio;
            $types .= "s";
        }
        
        if (!empty($fechaFin)) {
            $sql .= " AND DATE(a.fecha) <= ?";
            $params[] = $fechaFin;
            $types .= "s";
        }

        $sql .= " ORDER BY a.fecha DESC LIMIT 1000";

        $stmt = $conn->prepare($sql);
        if ($stmt && !empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        if ($stmt) {
            $stmt->execute();
            $resultado = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $resultado;
        }
        
        return [];
    }

    /**
     * Obtiene la lista de módulos únicos registrados para el filtro.
     */
    public function obtenerModulosUnicos(): array
    {
        global $conn;
        $sql = "SELECT DISTINCT modulo FROM auditoria ORDER BY modulo ASC";
        $resultado = $conn->query($sql);
        $modulos = [];
        if ($resultado) {
            while ($row = $resultado->fetch_assoc()) {
                $modulos[] = $row['modulo'];
            }
        }
        return $modulos;
    }
}