<?php
require_once __DIR__ . '/../../controllers/auth/AuthController.php';
require_once __DIR__ . '/../../models/Venta.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: ../Auth/login.php');
    exit;
}

$usuarioId = (int)($_SESSION['usuario']['id_usuario'] ?? 0);
$ventas = (new Venta())->obtenerHistorial(100, $usuarioId);
require_once __DIR__ . '/../Layoust/header.php';
?>
<div class="container-fluid py-2">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0 fw-bold" style="color:var(--color-primario);">
            <i class="fa-solid fa-clock-rotate-left me-2" style="color:var(--color-secundario);"></i> Mi historial de ventas
        </h2>
        <a href="mis_ventas.php" class="btn btn-primary"><i class="fa-solid fa-cash-register me-2"></i>Nueva venta</a>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Factura</th>
                        <th>Cliente</th>
                        <th>Método de pago</th>
                        <th class="text-end">Subtotal</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$ventas): ?>
                        <tr><td colspan="6" class="text-center text-muted py-5">Aún no tienes ventas registradas.</td></tr>
                    <?php else: ?>
                        <?php foreach ($ventas as $venta): ?>
                            <tr>
                                <td class="fw-semibold"><?php echo htmlspecialchars($venta['codigo_factura']); ?></td>
                                <td><?php echo $venta['cliente_id'] ? 'Cliente #' . (int)$venta['cliente_id'] : 'Venta general'; ?></td>
                                <td><?php echo htmlspecialchars($venta['metodo_pago']); ?></td>
                                <td class="text-end">$<?php echo number_format((float)$venta['subtotal'], 0, ',', '.'); ?></td>
                                <td class="text-end fw-bold">$<?php echo number_format((float)$venta['total'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../Layoust/footer.php'; ?>
