<?php
require_once __DIR__ . '/../config/database.php';

/**
 * MODELO DE CONFIGURACIÓN
 */
class Configuracion
{
    private \mysqli $conn;
    private const COLOR_PREDETERMINADO = '#B87983';
    private const COLORES_PREDETERMINADOS = [
        'color_botones' => '#A85F6D',
        'color_menu' => '#1C191B',
        'color_acento' => '#B9BF66'
    ];
    private const TIPOGRAFIAS_PERMITIDAS = [
        'Inter' => "'Inter', 'Segoe UI', sans-serif",
        'Arial' => "Arial, Helvetica, sans-serif",
        'Verdana' => "Verdana, Geneva, sans-serif",
        'Georgia' => "Georgia, 'Times New Roman', serif",
        'Trebuchet MS' => "'Trebuchet MS', sans-serif"
    ];

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
        foreach (self::COLORES_PREDETERMINADOS as $campo => $predeterminado) {
            $configuracion[$campo] = $this->normalizarColor(
                $configuracion[$campo] ?? null,
                $predeterminado
            );
        }
        $nombreTipografia = $this->normalizarNombreTipografia($configuracion['tipografia'] ?? 'Inter');
        $configuracion['tipografia_nombre'] = $nombreTipografia;
        $configuracion['tipografia'] = $this->normalizarTipografia($nombreTipografia);

        return $configuracion;
    }

    /**
     * Acepta únicamente colores hexadecimales para evitar CSS inválido.
     */
    public function normalizarColor(?string $color, string $predeterminado = self::COLOR_PREDETERMINADO): string
    {
        $color = strtoupper(trim((string)$color));

        if (preg_match('/^#[0-9A-F]{6}$/', $color) === 1) {
            return $color;
        }

        return $predeterminado;
    }

    public function normalizarTipografia(?string $tipografia): string
    {
        return self::TIPOGRAFIAS_PERMITIDAS[$tipografia ?? ''] ?? self::TIPOGRAFIAS_PERMITIDAS['Inter'];
    }

    /**
     * Actualiza la configuración general del sistema.
     */
    public function actualizar(array $datos): array
    {
        $datos['color_botones'] = $this->normalizarColor(
            $datos['color_botones'] ?? $datos['tema_colores'] ?? null,
            self::COLORES_PREDETERMINADOS['color_botones']
        );
        $datos['color_menu'] = $this->normalizarColor(
            $datos['color_menu'] ?? null,
            self::COLORES_PREDETERMINADOS['color_menu']
        );
        $datos['color_acento'] = $this->normalizarColor(
            $datos['color_acento'] ?? null,
            self::COLORES_PREDETERMINADOS['color_acento']
        );
        $datos['tipografia'] = $this->normalizarNombreTipografia($datos['tipografia'] ?? 'Inter');
        $datos['tema_colores'] = $datos['color_botones'];

        $query = "UPDATE configuracion SET 
                  nombre_empresa = ?, 
                  nit = ?, 
                  telefono = ?, 
                  email = ?, 
                  direccion = ?, 
                  impuesto = ?, 
                  tipo_facturacion = ?, 
                  tema_colores = ?,
                  color_botones = ?,
                  color_menu = ?,
                  color_acento = ?,
                  tipografia = ?
                  WHERE id = 1";
                  
        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("sssssdssssss",
                $datos['nombre_empresa'], 
                $datos['nit'], 
                $datos['telefono'], 
                $datos['email'], 
                $datos['direccion'], 
                $datos['impuesto'], 
                $datos['tipo_facturacion'], 
                $datos['tema_colores'],
                $datos['color_botones'],
                $datos['color_menu'],
                $datos['color_acento'],
                $datos['tipografia']
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

    public function actualizarApariencia(array $datos): array
    {
        $colores = [];
        foreach (self::COLORES_PREDETERMINADOS as $campo => $predeterminado) {
            $valor = trim((string)($datos[$campo] ?? ''));
            if (preg_match('/^#[0-9A-Fa-f]{6}$/', $valor) !== 1) {
                return ['success' => false, 'mensaje' => 'Uno de los colores seleccionados no es válido.'];
            }
            $colores[$campo] = strtoupper($valor);
        }

        $nombreTipografia = (string)($datos['tipografia'] ?? '');
        if (!array_key_exists($nombreTipografia, self::TIPOGRAFIAS_PERMITIDAS)) {
            return ['success' => false, 'mensaje' => 'La tipografía seleccionada no es válida.'];
        }

        $stmt = $this->conn->prepare(
            "UPDATE configuracion SET tema_colores = ?, color_botones = ?, color_menu = ?, color_acento = ?, tipografia = ? WHERE id = 1"
        );
        if (!$stmt) {
            return ['success' => false, 'mensaje' => 'No se pudo preparar el guardado de apariencia: ' . $this->conn->error];
        }

        $stmt->bind_param(
            "sssss",
            $colores['color_botones'],
            $colores['color_botones'],
            $colores['color_menu'],
            $colores['color_acento'],
            $nombreTipografia
        );
        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            return ['success' => false, 'mensaje' => 'No se pudo guardar la apariencia: ' . $error];
        }

        $stmt->close();
        return ['success' => true, 'mensaje' => 'Apariencia guardada.'];
    }

    private function normalizarNombreTipografia(string $tipografia): string
    {
        return array_key_exists($tipografia, self::TIPOGRAFIAS_PERMITIDAS) ? $tipografia : 'Inter';
    }
}