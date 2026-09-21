<?php
// Configuración global de zona horaria
date_default_timezone_set('America/Bogota');

$host = "127.0.0.1";
$user = "root";
$password = "";
$bd = "sigibet_db";
$port = 3306;

try {
    $conn = new mysqli($host, $user, $password, $bd, $port);
} catch (mysqli_sql_exception $e) {
    die("Error de Conexion: " . $e->getMessage());
}
?>