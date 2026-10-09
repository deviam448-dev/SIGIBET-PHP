<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../models/Cliente.php';

class ClienteController
{
    private Cliente $modelo;

    public function __construct()
    {
        $this->modelo = new Cliente();
    }

    public function listarAjax(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_SESSION['usuario'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'mensaje' => 'Sesión expirada. Inicie sesión nuevamente.']);
            exit;
        }

        try {
            $clientes = $this->modelo->obtenerTodos(trim((string)($_GET['q'] ?? '')));
            echo json_encode(['success' => true, 'clientes' => $clientes]);
        } catch (Throwable $exception) {
            error_log('Error al consultar compras por cliente: ' . $exception->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'mensaje' => 'No se pudieron cargar las compras. Inténtalo de nuevo.']);
        }
        exit;
    }
}

if (basename($_SERVER['PHP_SELF']) === 'ClienteController.php') {
    $controller = new ClienteController();
    if (($_GET['action'] ?? '') === 'listarAjax') {
        $controller->listarAjax();
    }

    http_response_code(404);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'mensaje' => 'Acción no disponible.']);
    exit;
}
