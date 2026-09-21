<?php
require_once __DIR__ . '/../config/database.php';

/**
 * MODELO DE CONFIGURACIÓN
 */
class Configuracion
{
    private \mysqli $conn;
    private const COLOR_PREDETERMINADO = '#B87983';

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
        $this->asegurarRegistroUnico();
    }

    /**
     * Asegura que exista al menos la fila con id = 1 en la tabla configuracion.
     */
    private function asegurarRegistroUnico()
    {
        $stmt = $this->conn->query("SELECT id FROM configuracion WHERE id = 1");
        if ($stmt->num_rows === 0) {
            $this->conn->query("INSERT INTO configuracion (id) VALUES (1)");
        }
    }

    /**
     * Obtiene todos los datos de configuración.
     */
    public function obtenerConfiguracion(): array
    {
        $stmt = $this->conn->query("SELECT * FROM configuracion WHERE id = 1");
        $configuracion = $stmt->fetch_assoc() ?: [];
        $configuracion['tema_colores'] = $this->normalizarColor($configuracion['tema_colores'] ?? null);

        return $configuracion;
    }

    /**
     * Acepta únicamente colores hexadecimales para evitar CSS inválido.
     */
    public function normalizarColor(?string $color): string
    {
        $color = strtoupper(trim((string)$color));

        if (preg_match('/^#[0-9A-F]{6}$/', $color) === 1) {
            return $color;
        }

        return self::COLOR_PREDETERMINADO;
    }

    /**
     * Actualiza la configuración general del sistema.
     */
    public function actualizar(array $datos): array
    {
        $datos['tema_colores'] = $this->normalizarColor($datos['tema_colores'] ?? null);

        $query = "UPDATE configuracion SET 
                  nombre_empresa = ?, 
                  nit = ?, 
                  telefono = ?, 
                  email = ?, 
                  direccion = ?, 
                  impuesto = ?, 
                  tipo_facturacion = ?, 
                  tema_colores = ? 
                  WHERE id = 1";
                  
        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("sssssdss", 
                $datos['nombre_empresa'], 
                $datos['nit'], 
                $datos['telefono'], 
                $datos['email'], 
                $datos['direccion'], 
                $datos['impuesto'], 
                $datos['tipo_facturacion'], 
                $datos['tema_colores']
            );
            
            if ($stmt->execute()) {
                $stmt->close();
                return ['success' => true, 'mensaje' => 'Configuración actualizada exitosamente.'];
            } else {
                $error = $stmt->error;
                $stmt->close();
                return ['success' => false, 'mensaje' => 'Error BD: ' . $error];
            }
        }
        return ['success' => false, 'mensaje' => 'Error preparando consulta.'];
    }
}