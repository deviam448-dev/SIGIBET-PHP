<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuario = $_SESSION['usuario'] ?? null;
if (!$usuario) {
    header('Location: Auth/login.php');
    exit;
}

require_once __DIR__ . '/../models/Reporte.php';
require_once __DIR__ . '/../models/Configuracion.php';

$ventaId = filter_input(INPUT_GET, 'venta_id', FILTER_VALIDATE_INT);
$reporte = new Reporte();
$venta = $ventaId ? $reporte->obtenerVentaPorId($ventaId) : null;

if (!$venta) {
    http_response_code(404);
    $mensajeError = 'No se encontró la factura solicitada.';
} elseif ((int)($usuario['id_rol'] ?? 0) !== 1
    && (int)$venta['usuario_id'] !== (int)($usuario['id_usuario'] ?? 0)) {
    http_response_code(403);
    $mensajeError = 'No tienes permiso para consultar esta factura.';
} else {
    $config = (new Configuracion())->obtenerConfiguracion();
}

$escapar = static fn($valor): string => htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
$formatoMoneda = static fn($valor): string => '$' . number_format((float)$valor, 0, ',', '.');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($venta) && $venta ? 'Factura ' . $escapar($venta['codigo_factura']) : 'Factura' ?> - Bella Tela</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 28px; background: #f5f1ef; color: #2c1a14; font-family: Arial, sans-serif; }
        .invoice { max-width: 800px; margin: 0 auto; padding: 36px; background: #fff; border-radius: 14px; box-shadow: 0 8px 28px #38251b1c; }
        .toolbar { max-width: 800px; margin: 0 auto 14px; display: flex; justify-content: flex-end; gap: 10px; }
        .toolbar button { border: 0; border-radius: 7px; padding: 10px 16px; background: #62483f; color: #fff; font-weight: 700; cursor: pointer; }
        .toolbar button:last-child { background: #b87983; }
        .top { display: flex; justify-content: space-between; gap: 24px; align-items: flex-start; }
        h1 { margin: 0 0 8px; color: #62483f; font-size: 27px; }
        .muted { color: #806d66; line-height: 1.55; font-size: 13px; }
        .number { padding: 12px 17px; border-radius: 9px; background: linear-gradient(135deg,#62483f,#b87983); color: #fff; text-align: right; }
        .number small { display: block; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 1px; }
        hr { border: 0; border-top: 2px solid #f0ebe8; margin: 24px 0; }
        .meta { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 22px; }
        .meta div { padding: 12px 15px; border-radius: 8px; background: #faf6f4; }
        .meta small { display: block; margin-bottom: 5px; color: #9b7b73; text-transform: uppercase; letter-spacing: .7px; }
        table { width: 100%; border-collapse: collapse; }
        th { padding: 11px 9px; background: #62483f; color: #fff; text-align: left; font-size: 13px; }
        td { padding: 11px 9px; border-bottom: 1px solid #f0ebe8; }
        .right { text-align: right; }
        .center { text-align: center; }
        .totals { width: min(100%, 300px); margin: 20px 0 0 auto; }
        .totals div { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f0ebe8; }
        .totals .grand { margin-top: 5px; border-top: 2px solid #62483f; border-bottom: 0; color: #b87983; font-size: 20px; font-weight: 800; }
        .footer { margin-top: 28px; padding-top: 16px; border-top: 1px dashed #e0d5d0; color: #9b7b73; text-align: center; font-size: 13px; }
        .error { max-width: 800px; margin: 80px auto; padding: 30px; border-radius: 10px; background: #fff; color: #842029; text-align: center; }
        @media (max-width: 600px) {
            body { padding: 12px; }
            .invoice { padding: 22px 16px; }
            .top { flex-direction: column; }
            .number { text-align: left; }
            .meta { grid-template-columns: 1fr; }
            table { font-size: 12px; }
            th, td { padding: 8px 5px; }
        }
        @media print {
            @page { margin: 14mm; }
            body { padding: 0; background: #fff; print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .invoice { max-width: none; padding: 0; border-radius: 0; box-shadow: none; }
            .toolbar { display: none; }
        }
    </style>
</head>
<body>
<?php if (isset($mensajeError)): ?>
    <main class="error">
        <h1>No se pudo abrir la factura</h1>
        <p><?= $escapar($mensajeError) ?></p>
        <button type="button" onclick="window.close()">Cerrar ventana</button>
    </main>
<?php else: ?>
    <div class="toolbar">
        <button type="button" onclick="window.print()">Imprimir factura</button>
        <button type="button" onclick="window.close()">Cerrar</button>
    </div>
    <main class="invoice">
        <header class="top">
            <div>
                <h1><?= $escapar($config['nombre_empresa'] ?? 'Bella Tela') ?></h1>
                <div class="muted">
                    NIT: <?= $escapar($config['nit'] ?? '—') ?> | <?= $escapar($config['telefono'] ?? '—') ?><br>
                    <?= $escapar($config['email'] ?? '—') ?><br>
                    <?= $escapar($config['direccion'] ?? '—') ?>
                </div>
            </div>
            <div>
                <div class="number"><small>Factura</small><strong><?= $escapar($venta['codigo_factura']) ?></strong></div>
                <p class="muted right"><?= $escapar(date('d/m/Y h:i A', strtotime($venta['fecha']))) ?></p>
            </div>
        </header>
        <hr>
        <section class="meta">
            <div><small>ID de cliente por compra</small><strong><?= $escapar($venta['cliente_nombre'] ?? 'Consumidor Final') ?></strong></div>
            <div><small>Vendedor / Pago</small><strong><?= $escapar($venta['usuario_nombre'] ?? '—') ?></strong> · <?= $escapar($venta['metodo_pago']) ?></div>
        </section>
        <table>
            <thead>
                <tr><th>#</th><th>Producto</th><th class="center">Cantidad</th><th class="right">Precio unitario</th><th class="right">Subtotal</th></tr>
            </thead>
            <tbody>
            <?php foreach ($venta['detalles'] as $indice => $detalle): ?>
                <tr>
                    <td><?= $indice + 1 ?></td>
                    <td><strong><?= $escapar($detalle['nombre']) ?></strong><br><span class="muted">Ref: <?= $escapar($detalle['referencia']) ?></span></td>
                    <td class="center"><?= (int)$detalle['cantidad'] ?> mt</td>
                    <td class="right"><?= $formatoMoneda($detalle['precio_unitario']) ?></td>
                    <td class="right"><strong><?= $formatoMoneda($detalle['subtotal']) ?></strong></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php $iva = (float)$venta['total'] - (float)$venta['subtotal']; ?>
        <section class="totals">
            <div><span>Subtotal</span><strong><?= $formatoMoneda($venta['subtotal']) ?></strong></div>
            <?php if ($iva > 0.01): ?><div><span>IVA</span><strong><?= $formatoMoneda($iva) ?></strong></div><?php endif; ?>
            <div class="grand"><span>TOTAL</span><span><?= $formatoMoneda($venta['total']) ?></span></div>
        </section>
        <footer class="footer">
            Gracias por su compra · <?= $escapar($config['nombre_empresa'] ?? 'Bella Tela') ?> ·
            <?= $escapar($config['tipo_facturacion'] ?? 'Régimen Simplificado') ?>
        </footer>
    </main>
    <script>
        window.addEventListener('load', () => window.setTimeout(() => window.print(), 250));
    </script>
<?php endif; ?>
</body>
</html>
