<?php
require_once __DIR__ . '/../config/database.php';

/**
 * MODELO DE USUARIOS (Autenticación y Seguridad)
 * 
 * Adaptado a la estructura de db.sql
 */
class Usuario
{
    private \mysqli $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
        $this->asegurarTablaRecuperacion();
    }

    private function asegurarTablaRecuperacion(): void
    {
        $this->conn->query("CREATE TABLE IF NOT EXISTS recuperacion_password (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NOT NULL,
            token_hash CHAR(64) NOT NULL UNIQUE,
            expira_en DATETIME NOT NULL,
            usado TINYINT NOT NULL DEFAULT 0,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    /**
     * Cuenta cuántos usuarios activos existen para un rol determinado.
     */
    public function contarPorRol(int $rol_id, ?int $excluirId = null): int
    {
        $sql = "SELECT COUNT(id) as total FROM usuarios WHERE rol_id = ? AND estado = 1";
        $params = [$rol_id];
        $types = "i";

        if ($excluirId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excluirId;
            $types .= "i";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $resultado = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return (int)($resultado['total'] ?? 0);
    }

    public function registrar(string $nombre, string $usuario, string $email, string $telefono, string $password, int $rol_id, int $estado = 1)
    {
        // Validar límite de 2 usuarios por rol (si se está creando activo)
        if ($estado === 1 && $this->contarPorRol($rol_id) >= 2) {
            return ['success' => false, 'mensaje' => 'No se pueden registrar más de 2 usuarios activos para este rol.'];
        }

        // Validar si el usuario o email ya existe
        if ($this->emailExiste($email) || $this->usuarioExiste($usuario)) {
            return ['success' => false, 'mensaje' => 'El correo o el nombre de usuario ya están en uso.'];
        }

        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $query = 'INSERT INTO usuarios (nombre, usuario, email, telefono, password, rol_id, estado) VALUES (?, ?, ?, ?, ?, ?, ?)';

        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param('sssssii', $nombre, $usuario, $email, $telefono, $hashed_password, $rol_id, $estado);
            $result = $stmt->execute();
            if (!$result) {
                $error = $stmt->error;
                $stmt->close();
                return ['success' => false, 'mensaje' => 'Error BD: ' . $error];
            }
            $stmt->close();
            return ['success' => true, 'mensaje' => 'Usuario registrado exitosamente.'];
        }
        return ['success' => false, 'mensaje' => 'Error al preparar consulta.'];
    }

    public function usuarioExiste(string $usuario, ?int $excluirId = null)
    {
        $sql = "SELECT id FROM usuarios WHERE usuario = ?";
        $params = [$usuario];
        $types = "s";
        if ($excluirId) {
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

    public function emailExiste(string $email, ?int $excluirId = null)
    {
        $query = 'SELECT id FROM usuarios WHERE email = ?';
        $params = [$email];
        $types = "s";
        if ($excluirId) {
            $query .= " AND id != ?";
            $params[] = $excluirId;
            $types .= "i";
        }
        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $stmt->store_result();
            $num_rows = $stmt->num_rows;
            $stmt->close();
            return $num_rows > 0;
        }
        return false;
    }

    public function obtenerPorEmail(string $email)
    {
        // Se obtiene por email o por nombre de usuario
        $query = 'SELECT id AS id_usuario, nombre, usuario, email, telefono, password AS password_hash, rol_id AS id_rol, intentos_fallidos, bloqueado_hasta, estado FROM usuarios WHERE email = ? OR usuario = ?';
        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param('ss', $email, $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $usuario_data = $result->fetch_assoc();
            $stmt->close();

            if ($usuario_data) {
                // Adaptar el estado para compatibilidad con el controlador
                if ($usuario_data['estado'] == 0) {
                    $usuario_data['estado'] = 'inactivo';
                } elseif ($usuario_data['bloqueado_hasta'] != null && strtotime($usuario_data['bloqueado_hasta']) > time()) {
                    $usuario_data['estado'] = 'bloqueado';
                    $usuario_data['ultimo_acceso'] = date('Y-m-d H:i:s', strtotime($usuario_data['bloqueado_hasta']) - 15*60); // Simulando ultimo acceso para el cálculo
                } else {
                    $usuario_data['estado'] = 'activo';
                }
            }
            return $usuario_data;
        }
        return null;
    }

    public function registrarIntentoFallido(int $id_usuario, int $intentos_actuales)
    {
        $nuevos_intentos = $intentos_actuales + 1;
        $bloqueado_hasta = ($nuevos_intentos >= 3) ? date('Y-m-d H:i:s', strtotime('+15 minutes')) : null;
        
        $query = 'UPDATE usuarios SET intentos_fallidos = ?, bloqueado_hasta = ? WHERE id = ?';
        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param('isi', $nuevos_intentos, $bloqueado_hasta, $id_usuario);
            $stmt->execute();
            $stmt->close();
        }
    }

    public function crearTokenRecuperacion(string $identificador): ?string
    {
        $usuario = $this->obtenerPorEmail($identificador);
        if (!$usuario || $usuario['estado'] === 'inactivo') {
            return null;
        }

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiraEn = date('Y-m-d H:i:s', time() + 1800);

        $this->conn->query("UPDATE recuperacion_password SET usado = 1 WHERE usuario_id = " . (int)$usuario['id_usuario'] . " AND usado = 0");
        $stmt = $this->conn->prepare('INSERT INTO recuperacion_password (usuario_id, token_hash, expira_en) VALUES (?, ?, ?)');
        $stmt->bind_param('iss', $usuario['id_usuario'], $tokenHash, $expiraEn);
        $resultado = $stmt->execute();
        $stmt->close();

        return $resultado ? $token : null;
    }

    public function obtenerPorToken(string $token): ?array
    {
        $tokenHash = hash('sha256', $token);
        $stmt = $this->conn->prepare('SELECT r.id, r.usuario_id, u.email FROM recuperacion_password r INNER JOIN usuarios u ON u.id = r.usuario_id WHERE r.token_hash = ? AND r.usado = 0 AND r.expira_en > NOW()');
        $stmt->bind_param('s', $tokenHash);
        $stmt->execute();
        $datos = $stmt->get_result()->fetch_assoc() ?: null;
        $stmt->close();
        return $datos;
    }

    public function restablecerPasswordConToken(string $token, string $nuevaPassword): bool
    {
        $datos = $this->obtenerPorToken($token);
        if (!$datos) {
            return false;
        }

        $passwordHash = password_hash($nuevaPassword, PASSWORD_BCRYPT);
        $this->conn->begin_transaction();
        try {
            $stmt = $this->conn->prepare('UPDATE usuarios SET password = ?, intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id = ?');
            $stmt->bind_param('si', $passwordHash, $datos['usuario_id']);
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare('UPDATE recuperacion_password SET usado = 1 WHERE id = ?');
            $stmt->bind_param('i', $datos['id']);
            $stmt->execute();
            $stmt->close();
            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            $this->conn->rollback();
            return false;
        }
    }

    public function actualizarPassword(int $id_usuario, string $nueva_password)
    {
        $hashed = password_hash($nueva_password, PASSWORD_BCRYPT);
        $query = 'UPDATE usuarios SET password = ? WHERE id = ?';
        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param('si', $hashed, $id_usuario);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }
        return false;
    }

    public function resetearIntentosYActualizarAcceso(int $id_usuario)
    {
        $query = 'UPDATE usuarios SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id = ?';
        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param('i', $id_usuario);
            $stmt->execute();
            $stmt->close();
        }
    }

    public function obtenerTodos()
    {
        $query = 'SELECT u.id, u.nombre, u.usuario, u.email, u.telefono, u.rol_id, r.nombre AS nombre_rol, u.estado, u.fecha_creacion
                  FROM usuarios u 
                  LEFT JOIN roles r ON u.rol_id = r.id';
        $result = $this->conn->query($query);
        $usuarios = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $usuarios[] = $row;
            }
        }
        return $usuarios;
    }

    public function obtenerEstados()
    {
        return [
            1 => 'Activo',
            0 => 'Inactivo'
        ];
    }

    public function actualizar(int $id_usuario, array $datos)
    {
        // Validar límite de roles si el usuario está activo o se va a activar
        $estado = (int)$datos['estado'];
        $rol_id = (int)$datos['rol_id'];

        if ($estado === 1 && $this->contarPorRol($rol_id, $id_usuario) >= 2) {
            return ['success' => false, 'mensaje' => 'No se pueden tener más de 2 usuarios activos para este rol.'];
        }

        // Validar unique email / usuario
        if (!empty($datos['email']) && $this->emailExiste($datos['email'], $id_usuario)) {
            return ['success' => false, 'mensaje' => 'El correo electrónico ya está en uso por otro usuario.'];
        }
        if ($this->usuarioExiste($datos['usuario'], $id_usuario)) {
            return ['success' => false, 'mensaje' => 'El nombre de usuario ya está en uso.'];
        }

        $query = 'UPDATE usuarios SET nombre = ?, usuario = ?, telefono = ?, rol_id = ?, estado = ?';
        $types = 'sssii';
        $params = [$datos['nombre'], $datos['usuario'], $datos['telefono'], $rol_id, $estado];

        if (!empty($datos['email'])) {
            $query .= ', email = ?';
            $types .= 's';
            $params[] = $datos['email'];
        }

        // Opcional password update
        if (!empty($datos['password'])) {
            $query .= ', password = ?';
            $types .= 's';
            $params[] = password_hash($datos['password'], PASSWORD_BCRYPT);
        }

        $query .= ' WHERE id = ?';
        $types .= 'i';
        $params[] = $id_usuario;

        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param($types, ...$params);
            $result = $stmt->execute();
            if (!$result) {
                $error = $stmt->error;
                $stmt->close();
                return ['success' => false, 'mensaje' => 'Error DB: ' . $error];
            }
            $stmt->close();
            return ['success' => true, 'mensaje' => 'Usuario actualizado correctamente.'];
        }
        return ['success' => false, 'mensaje' => 'Error al preparar actualización.'];
    }

    public function cambiarEstado(int $id_usuario, int $nuevoEstado)
    {
        if ($nuevoEstado === 1) {
            // Verificar limite si se va a activar
            $stmt = $this->conn->prepare("SELECT rol_id FROM usuarios WHERE id = ?");
            $stmt->bind_param('i', $id_usuario);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($res && $this->contarPorRol($res['rol_id'], $id_usuario) >= 2) {
                return ['success' => false, 'mensaje' => 'Límite máximo de 2 usuarios alcanzado para este rol.'];
            }
        }

        $query = 'UPDATE usuarios SET estado = ? WHERE id = ?';
        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param('ii', $nuevoEstado, $id_usuario);
            $result = $stmt->execute();
            $stmt->close();
            return ['success' => true, 'mensaje' => 'Estado actualizado.'];
        }
        return ['success' => false, 'mensaje' => 'Error al actualizar estado.'];
    }
}
?>