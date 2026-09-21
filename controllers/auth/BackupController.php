<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../models/Backup.php';
require_once __DIR__ . '/../../models/Auditoria.php';
require_once __DIR__ . '/AuthController.php';

/**
 * CONTROLADOR DE BACKUPS
 */
class BackupController
{
    private Backup $modelo;

    public function __construct()
    {
        // Seguridad: SOLO Administradores
        if (basename($_SERVER['PHP_SELF']) !== 'BackupController.php') {
            if (!AuthController::esAdmin()) {
                header('Location: ../../views/admin/gproductos.php');
                exit;
            }
        } else {
            AuthController::requerirRol([1]);
        }
        
        $this->modelo = new Backup();
    }

    public function index()
    {
        require_once __DIR__ . '/../../views/admin/respaldo.php';
    }

    public function descargarAjax()
    {
        // Generar archivo en carpeta temporal
        $fecha = date('Ymd_His');
        $nombreArchivo = "sigibet_backup_{$fecha}.sql";
        $rutaTemp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $nombreArchivo;

        if ($this->modelo->generarBackup($rutaTemp)) {
            Auditoria::registrar('Respaldo', 'Generar Backup', "Archivo: $nombreArchivo");
            
            // Forzar descarga
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($rutaTemp) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($rutaTemp));
            readfile($rutaTemp);
            unlink($rutaTemp); // Eliminar archivo temporal
            exit;
        } else {
            echo "Error al generar la copia de seguridad. Verifique que mysqldump esté disponible en el servidor.";
            exit;
        }
    }

    public function restaurarAjax()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['archivo_sql'])) {
            echo json_encode(['success' => false, 'mensaje' => 'No se subió ningún archivo válido.']);
            exit;
        }

        $archivo = $_FILES['archivo_sql'];
        
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'mensaje' => 'Error en la subida del archivo.']);
            exit;
        }
        
        // Validación básica de extensión
        $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if ($ext !== 'sql') {
            echo json_encode(['success' => false, 'mensaje' => 'Formato no válido. Debe ser un archivo .sql.']);
            exit;
        }

        // 1. Crear respaldo automático de emergencia antes de restaurar
        $fechaEmerg = date('Ymd_His');
        $rutaEmerg = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "emergencia_previo_restauracion_{$fechaEmerg}.sql";
        $this->modelo->generarBackup($rutaEmerg);
        // Aquí lo ideal sería mover el archivo de emergencia a una carpeta segura, pero lo dejaremos en tmp.

        // 2. Proceder con la restauración del archivo subido
        $resultado = $this->modelo->restaurarBackup($archivo['tmp_name']);
        
        if ($resultado['success']) {
            Auditoria::registrar('Respaldo', 'Restaurar Backup', "Restaurado con el archivo: " . $archivo['name']);
        } else {
            Auditoria::registrar('Respaldo', 'Error Restauración', "Fallo al intentar restaurar con: " . $archivo['name']);
        }
        
        echo json_encode($resultado);
        exit;
    }
}

// Router Simple (Front Controller Embedded)
if (basename($_SERVER['PHP_SELF']) === 'BackupController.php') {
    $controller = new BackupController();
    $accion = $_GET['action'] ?? '';

    switch ($accion) {
        case 'descargarAjax':
            $controller->descargarAjax(); // Esto descargará el archivo
            break;
        case 'restaurarAjax':
            $controller->restaurarAjax();
            break;
        default:
            echo json_encode(['success' => false, 'mensaje' => 'Acción no válida.']);
            exit;
    }
}
