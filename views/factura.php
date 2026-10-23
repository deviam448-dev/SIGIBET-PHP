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
        body { margin: 0; padding: 24px 12px; background: #f0f0f0; color: #000; font-family: "Times New Roman", Times, serif; font-size: 14px; }
        .invoice { width: 300px; max-width: 100%; margin: 0 auto; padding: 20px 15px; background: #fff; box-shadow: 0 0 10px #0000001a; }
        .toolbar { width: 300px; max-width: 100%; margin: 0 auto 12px; display: flex; justify-content: flex-end; gap: 8px; }
        .toolbar button { border: 0; border-radius: 5px; padding: 9px 12px; background: #62483f; color: #fff; font: 700 14px Arial, sans-serif; cursor: pointer; }
        .toolbar button:last-child { background: #b87983; }
        .top { text-align: center; }
        h1 { margin: 0 0 8px; color: #62483f; font-size: 20px; }
        .muted { color: #5f514c; line-height: 1.35; font-size: 12px; overflow-wrap: anywhere; }
        .number { margin-top: 14px; text-align: right; line-height: 1.5; }
        .number strong { color: #62483f; }
        hr { border: 0; border-top: 1px solid #000; margin: 12px 0; }
        .meta { margin-bottom: 14px; line-height: 1.5; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th { padding: 5px 2px; border-top: 1px solid #000; border-bottom: 1px solid #000; text-align: left; font-size: 12px; }
        td { padding: 6px 2px; vertical-align: top; overflow-wrap: anywhere; }
        .right { text-align: right; }
        .center { text-align: center; }
        .product-name { font-weight: 700; }
        .product-ref { display: block; color: #5f514c; font-size: 11px; }
        .totals { margin: 12px 0 0 auto; }
        .totals td { padding: 4px 2px; }
        .totals .label { text-align: right; font-weight: 700; }
        .totals .grand td { border-top: 3px double #000; font-size: 16px; font-weight: 700; }
        .footer { margin-top: 16px; padding-top: 12px; border-top: 1px dashed #555; text-align: center; line-height: 1.5; font-size: 13px; }
        .error { max-width: 800px; margin: 80px auto; padding: 30px; border-radius: 10px; background: #fff; color: #842029; text-align: center; }
        .error button { margin-top: 16px; padding: 9px 14px; cursor: pointer; }
        @media print {
            @page { size: 80mm auto; margin: 3mm; }
            body { width: 74mm; padding: 0; background: #fff; }
            .invoice { width: 100%; max-width: none; padding: 0; box-shadow: none; }
            .toolbar { display: none; }
            .error { margin: 0; box-shadow: none; }
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
            <h1><?= $escapar($config['nombre_empresa'] ?? 'Bella Tela') ?></h1>
            <div class="muted">
                <?php if (!empty($config['nit'])): ?>NIT: <?= $escapar($config['nit']) ?><br><?php endif; ?>
                <?php if (!empty($config['telefono'])): ?>Tel: <?= $escapar($config['telefono']) ?><br><?php endif; ?>
                <?php if (!empty($config['email'])): ?><?= $escapar($config['email']) ?><br><?php endif; ?>
                <?php if (!empty($config['direccion'])): ?><?= $escapar($config['direccion']) ?><?php endif; ?>
            </div>
            <div class="number">
                <strong>FACTURA <?= $escapar($venta['codigo_factura']) ?></strong><br>
                <span class="muted"><?= $escapar(date('d/m/Y h:i A', strtotime($venta['fecha']))) ?></span>
            </div>
        </header>
        <hr>
        <section class="meta">
            Cliente: <strong><?= $escapar($venta['cliente_nombre'] ?? 'Consumidor Final') ?></strong><br>
            Vendedor: <?= $escapar($venta['usuario_nombre'] ?? '—') ?><br>
            Pago: <?= $escapar($venta['metodo_pago']) ?>
        </section>
        <table>
            <thead>
                <tr>
                    <th style="width: 46%;">Producto</th>
                    <th class="center" style="width: 12%;">Cant.</th>
                    <th class="right" style="width: 21%;">Precio</th>
                    <th class="right" style="width: 21%;">Total</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($venta['detalles'] as $detalle): ?>
                <tr>
                    <td><span class="product-name"><?= $escapar($detalle['nombre']) ?></span><span class="product-ref">Ref: <?= $escapar($detalle['referencia']) ?><?php if (!empty($detalle['color'])): ?> · <?= $escapar($detalle['color']) ?><?php endif; ?></span></td>
                    <td class="center"><?= (int)$detalle['cantidad'] ?> mt</td>
                    <td class="right"><?= $formatoMoneda($detalle['precio_unitario']) ?></td>
                    <td class="right"><strong><?= $formatoMoneda($detalle['subtotal']) ?></strong></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php $iva = (float)$venta['total'] - (float)$venta['subtotal']; ?>
        <section class="totals">
            <table>
                <tr><td class="label">Subtotal</td><td class="right"><?= $formatoMoneda($venta['subtotal']) ?></td></tr>
                <?php if ($iva > 0.01): ?><tr><td class="label">IVA</td><td class="right"><?= $formatoMoneda($iva) ?></td></tr><?php endif; ?>
                <tr class="grand"><td class="label">TOTAL</td><td class="right"><?= $formatoMoneda($venta['total']) ?></td></tr>
            </table>
        </section>
        <footer class="footer">
            ¡Gracias por su compra!<br>
            <?= $escapar($config['tipo_facturacion'] ?? 'Régimen Simplificado') ?>
        </footer>
    </main>
    <script>
        window.addEventListener('load', () => window.setTimeout(() => window.print(), 250));
    </script>
<?php endif; ?>
</body>
</html>
