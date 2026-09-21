<?php
require_once __DIR__ . '/../config/database.php';

/**
 * MODELO DE BACKUP
 * Genera y restaura backups usando mysqldump/mysql de Laragon.
 * Detecta automáticamente la ruta de mysqldump en el servidor.
 */
class Backup
{
    private string $dbHost;
    private string $dbUser;
    private string $dbPass;
    private string $dbName;

    // Rutas conocidas de mysqldump en Laragon/XAMPP para Windows
    private array $rutasConocidas = [
        'C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqldump.exe',
        'C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqldump.exe',
        'C:\laragon\bin\mysql\mysql-8.0.36-winx64\bin\mysqldump.exe',
        'C:\laragon\bin\mysql\mysql-8.0.40-winx64\bin\mysqldump.exe',
        'C:\laragon\bin\mysql\mysql-5.7.40-winx64\bin\mysqldump.exe',
        'C:\xampp\mysql\bin\mysqldump.exe',
    ];

    private array $rutasMysql = [
        'C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe',
        'C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe',
        'C:\laragon\bin\mysql\mysql-8.0.36-winx64\bin\mysql.exe',
        'C:\laragon\bin\mysql\mysql-8.0.40-winx64\bin\mysql.exe',
        'C:\laragon\bin\mysql\mysql-5.7.40-winx64\bin\mysql.exe',
        'C:\xampp\mysql\bin\mysql.exe',
    ];

    public function __construct()
    {
        // Leer credenciales desde las variables globales de config/database.php
        global $host, $user, $password, $bd;
        $this->dbHost = $host     ?? 'localhost';
        $this->dbUser = $user     ?? 'root';
        $this->dbPass = $password ?? '';
        $this->dbName = $bd       ?? 'sigibet_db';
    }

    /**
     * Busca el ejecutable mysqldump/mysql buscando primero en rutas conocidas,
     * luego en el directorio de Laragon dinámicamente.
     */
    private function encontrarEjecutable(string $nombre): string
    {
        $rutas = ($nombre === 'mysqldump') ? $this->rutasConocidas : $this->rutasMysql;

        // 1. Buscar en rutas hardcodeadas
        foreach ($rutas as $ruta) {
            if (file_exists($ruta)) {
                return '"' . $ruta . '"';
            }
        }

        // 2. Buscar dinámicamente en C:\laragon\bin\mysql\
        $dirBase = 'C:\laragon\bin\mysql';
        if (is_dir($dirBase)) {
            $subdirs = glob($dirBase . '\mysql-*', GLOB_ONLYDIR);
            if ($subdirs) {
                // Ordenar descendente para tomar la versión más nueva
                rsort($subdirs);
                foreach ($subdirs as $dir) {
                    $exe = $dir . '\bin\\' . $nombre . '.exe';
                    if (file_exists($exe)) {
                        return '"' . $exe . '"';
                    }
                }
            }
        }

        // 3. Fallback: esperar que esté en PATH del sistema
        return $nombre;
    }

    public function generarBackup(string $rutaDestino): bool
    {
        $mysqldump = $this->encontrarEjecutable('mysqldump');

        $auth = empty($this->dbPass)
            ? "-u " . escapeshellarg($this->dbUser)
            : "-u " . escapeshellarg($this->dbUser) . " -p" . escapeshellarg($this->dbPass);

        $cmd = $mysqldump . " {$auth} -h " . escapeshellarg($this->dbHost)
             . " " . escapeshellarg($this->dbName)
             . " > " . escapeshellarg($rutaDestino);

        $output     = [];
        $return_var = 0;
        exec($cmd . ' 2>&1', $output, $return_var);

        return $return_var === 0 && file_exists($rutaDestino) && filesize($rutaDestino) > 0;
    }

    public function restaurarBackup(string $rutaArchivo): array
    {
        if (!file_exists($rutaArchivo)) {
            return ['success' => false, 'mensaje' => 'El archivo de respaldo no existe o no se pudo cargar.'];
        }

        $mysql = $this->encontrarEjecutable('mysql');

        $auth = empty($this->dbPass)
            ? "-u " . escapeshellarg($this->dbUser)
            : "-u " . escapeshellarg($this->dbUser) . " -p" . escapeshellarg($this->dbPass);

        $cmd = $mysql . " {$auth} -h " . escapeshellarg($this->dbHost)
             . " " . escapeshellarg($this->dbName)
             . " < " . escapeshellarg($rutaArchivo);

        $output     = [];
        $return_var = 0;
        exec($cmd . ' 2>&1', $output, $return_var);

        if ($return_var === 0) {
            return ['success' => true, 'mensaje' => 'Base de datos restaurada correctamente.'];
        }

        return ['success' => false, 'mensaje' => 'Error al restaurar: ' . implode("\n", $output)];
    }
}
