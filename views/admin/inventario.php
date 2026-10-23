<?php
// Incluir el header (que automáticamente incluye el topbar y el sidebar correspondiente)
require_once __DIR__ . '/../Layoust/header.php';
require_once __DIR__ . '/../../controllers/auth/InventarioController.php';

$inventarioController = new InventarioController();
$datos = $inventarioController->obtenerDatosDashboard();

$productos = $datos['productos'];
$movimientos = $datos['movimientos'];
$valor_total = $datos['valor_total'];
$bajo_stock_count = $datos['bajo_stock_count'];
$total_unidades = array_sum(array_map(static fn($producto) => (int)$producto['stock'], $productos));
$categorias = array_unique(array_map(
    static fn($producto) => trim((string)($producto['categoria'] ?? '')),
    $productos
));
sort($categorias, SORT_NATURAL | SORT_FLAG_CASE);

// Helper para colores de tipo de movimiento
function colorTipoMovimiento($tipo) {
    switch ($tipo) {
        case 'ENTRADA': return 'success';
        case 'SALIDA_VENTA': return 'primary';
        case 'AJUSTE': return 'warning text-dark';
        case 'CREACION': return 'info text-dark';
        default: return 'secondary';
    }
}
?>

<!-- Contenido Específico: Dashboard de Inventario -->
<div class="container-fluid py-2">
    
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0 text-gray-800 fw-bold">
            <i class="fa-solid fa-warehouse text-primary-custom me-2"></i> Dashboard de Inventario
        </h2>
    </div>

    <!-- Tarjetas de Resumen -->
    <div class="row mb-4">
        <!-- Valor Total -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow-sm h-100 py-2 border-0" style="border-left: 4px solid var(--bs-primary); border-radius: 12px; transition: transform 0.3s ease;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #4a5568; letter-spacing: 0.5px;">
                                Valor Total del Inventario (Costo)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($valor_total, 2); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x" style="color: rgba(61, 169, 224, 0.2);"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cantidad de Productos -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow-sm h-100 py-2 border-0" style="border-left: 4px solid var(--bs-warning); border-radius: 12px; transition: transform 0.3s ease;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #4a5568; letter-spacing: 0.5px;">
                                Referencias en Catálogo</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($productos); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box-open fa-2x" style="color: rgba(244, 196, 48, 0.3);"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alertas de Stock -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow-sm h-100 py-2 border-0" style="border-left: 4px solid var(--bs-danger); border-radius: 12px; transition: transform 0.3s ease;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #4a5568; letter-spacing: 0.5px;">
                                Alertas de Bajo Stock</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $bajo_stock_count; ?> productos</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x" style="color: rgba(232, 93, 93, 0.2);"></i>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card shadow-sm h-100 py-2 border-0" style="border-left: 4px solid var(--bs-success); border-radius: 12px;">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #4a5568; letter-spacing: 0.5px;">
                                        Unidades en Existencia
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_unidades); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pestañas (Tabs) para organizar el contenido -->
    <ul class="nav nav-tabs mb-4 border-bottom" id="inventarioTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-semibold text-dark border-0 border-bottom border-primary border-3" id="estado-tab" data-bs-toggle="tab" data-bs-target="#estado" type="button" role="tab" aria-controls="estado" aria-selected="true" style="background: transparent;">
                <i class="fa-solid fa-clipboard-check me-1 text-primary-custom"></i> Estado Actual
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold text-secondary border-0" id="movimientos-tab" data-bs-toggle="tab" data-bs-target="#movimientos" type="button" role="tab" aria-controls="movimientos" aria-selected="false" style="background: transparent;">
                <i class="fa-solid fa-clock-rotate-left me-1"></i> Historial de Movimientos
            </button>
        </li>
    </ul>

    <div class="tab-content" id="inventarioTabsContent">
        <!-- TAB 1: ESTADO DEL INVENTARIO -->
        <div class="tab-pane fade show active" id="estado" role="tabpanel" aria-labelledby="estado-tab">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 d-flex flex-wrap gap-2 align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary-custom">Disponibilidad en Bodega</h6>
                    <label class="visually-hidden" for="buscar-productos-inventario">Buscar productos</label>
                    <input type="search" class="form-control" id="buscar-productos-inventario"
                           placeholder="Buscar referencia, producto, categoría o color"
                           style="max-width: 360px;" autocomplete="off">
                    <label class="visually-hidden" for="filtro-stock-inventario">Filtrar nivel de stock</label>
                    <select class="form-select" id="filtro-stock-inventario" style="max-width: 190px;">
                        <option value="">Todos los niveles</option>
                        <option value="agotado">Agotado</option>
                        <option value="bajo">Bajo</option>
                        <option value="optimo">Óptimo</option>
                    </select>
                    <label class="visually-hidden" for="filtro-categoria-inventario">Filtrar categoría</label>
                    <select class="form-select" id="filtro-categoria-inventario" style="max-width: 220px;">
                        <option value="">Todas las categorías</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?php echo htmlspecialchars($categoria, ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($categoria !== '' ? $categoria : 'Sin categoría', ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2" id="inventario-resultados" aria-live="polite"></p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle" id="tabla-inventario">
                            <thead class="table-light">
                                <tr>
                                    <th>Referencia</th>
                                    <th>Producto</th>
                                    <th>Categoría</th>
                                    <th>Color</th>
                                    <th class="text-end">Costo Unit.</th>
                                    <th class="text-end">Costo Total</th>
                                    <th class="text-center">Stock Actual</th>
                                    <th class="text-center">Stock Mínimo</th>
                                    <th class="text-center">Nivel de Stock</th>
                                    <th class="text-center">Estado del Producto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($productos) > 0): ?>
                                    <?php foreach ($productos as $p): ?>
                                        <?php
                                            $stockProducto = (int)$p['stock'];
                                            $stockMinimo = (int)$p['stock_minimo'];
                                            $esBajoStock = ($stockProducto <= $stockMinimo);
                                            $nivelStock = $stockProducto === 0 ? 'agotado' : ($esBajoStock ? 'bajo' : 'optimo');
                                        ?>
                                        <tr class="<?php echo implode(' ', array_filter([$esBajoStock ? 'table-danger' : '', (int)$p['estado'] !== 1 ? 'text-muted' : ''])); ?>"
                                            data-search="<?php echo htmlspecialchars(implode(' ', array_filter([$p['referencia'] ?? '', $p['nombre'] ?? '', $p['categoria'] ?? '', $p['color'] ?? ''])), ENT_QUOTES, 'UTF-8'); ?>"
                                            data-category="<?php echo htmlspecialchars(trim((string)($p['categoria'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>"
                                            data-stock-level="<?php echo $nivelStock; ?>">
                                            <td class="fw-bold"><?php echo htmlspecialchars($p['referencia']); ?></td>
                                            <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                                            <td><?php echo htmlspecialchars($p['categoria'] ?: 'Sin categoría'); ?></td>
                                            <td><?php echo htmlspecialchars($p['color']); ?></td>
                                            <td class="text-end">$<?php echo number_format($p['precio_compra'], 2); ?></td>
                                            <td class="text-end fw-semibold">$<?php echo number_format($p['precio_compra'] * $p['stock'], 2); ?></td>
                                            <td class="text-center">
                                                <span class="fs-6 fw-bold"><?php echo $p['stock']; ?></span>
                                            </td>
                                            <td class="text-center"><?php echo (int)$p['stock_minimo']; ?></td>
                                            <td class="text-center">
                                                <?php if ($stockProducto === 0): ?>
                                                    <span class="badge bg-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i>Agotado</span>
                                                <?php elseif ($esBajoStock): ?>
                                                    <span class="badge bg-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i>Bajo</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Óptimo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ((int)$p['estado'] === 1): ?>
                                                    <span class="badge bg-primary">Activo</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Archivado</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">No hay productos en el catálogo.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: HISTORIAL DE MOVIMIENTOS -->
        <div class="tab-pane fade" id="movimientos" role="tabpanel" aria-labelledby="movimientos-tab">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary-custom">Últimos 50 Movimientos</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle" style="font-size: 0.9rem;">
                            <thead class="table-dark">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Producto</th>
                                    <th>Cant.</th>
                                    <th>Stock (Ant → Nue)</th>
                                    <th>Usuario</th>
                                    <th>Motivo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($movimientos) > 0): ?>
                                    <?php foreach ($movimientos as $m): ?>
                                        <tr>
                                            <td>
                                                <small class="text-muted d-block"><?php echo date('d M Y', strtotime($m['fecha'])); ?></small>
                                                <strong><?php echo date('H:i', strtotime($m['fecha'])); ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo colorTipoMovimiento($m['tipo']); ?>">
                                                    <?php echo str_replace('_', ' ', $m['tipo']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($m['referencia']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($m['producto_nombre']); ?></small>
                                            </td>
                                            <td class="fw-bold fs-6 text-center">
                                                <?php 
                                                    if (in_array($m['tipo'], ['ENTRADA', 'CREACION']) || ($m['tipo'] == 'AJUSTE' && $m['stock_nuevo'] > $m['stock_anterior'])) {
                                                        echo '<span class="text-success">+' . $m['cantidad'] . '</span>';
                                                    } else {
                                                        echo '<span class="text-danger">-' . $m['cantidad'] . '</span>';
                                                    }
                                                ?>
                                            </td>
                                            <td class="text-center text-muted">
                                                <?php echo $m['stock_anterior']; ?> <i class="fa-solid fa-arrow-right mx-1"></i> <?php echo $m['stock_nuevo']; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($m['usuario_nombre']); ?></td>
                                            <td><small><?php echo htmlspecialchars($m['motivo']); ?></small></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-5">
                                            <i class="fa-solid fa-clock-rotate-left fa-3x mb-3 text-light"></i>
                                            <h5>No hay movimientos registrados aún</h5>
                                            <p>Los movimientos aparecerán aquí cuando crees productos, ajustes stock o realices ventas.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabla = document.getElementById('tabla-inventario');
    const busqueda = document.getElementById('buscar-productos-inventario');
    const filtroStock = document.getElementById('filtro-stock-inventario');
    const filtroCategoria = document.getElementById('filtro-categoria-inventario');
    const resumen = document.getElementById('inventario-resultados');
    if (!tabla || !busqueda || !filtroStock || !filtroCategoria || !resumen) return;

    const filas = Array.from(tabla.querySelectorAll('tbody tr[data-search]'));
    const normalizar = valor => valor.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
    const filtrar = () => {
        const consulta = normalizar(busqueda.value.trim());
        const nivelSeleccionado = filtroStock.value;
        const categoriaSeleccionada = filtroCategoria.value;
        let visibles = 0;
        filas.forEach(fila => {
            const coincideTexto = normalizar(fila.dataset.search || '').includes(consulta);
            const coincideStock = !nivelSeleccionado || fila.dataset.stockLevel === nivelSeleccionado;
            const coincideCategoria = !categoriaSeleccionada || fila.dataset.category === categoriaSeleccionada;
            const coincide = coincideTexto && coincideStock && coincideCategoria;
            fila.hidden = !coincide;
            if (coincide) visibles++;
        });
        resumen.textContent = `Mostrando ${visibles} de ${filas.length} productos`;
    };

    busqueda.addEventListener('input', filtrar);
    filtroStock.addEventListener('change', filtrar);
    filtroCategoria.addEventListener('change', filtrar);
    filtrar();
});
</script>

<?php
// Incluir el footer
require_once __DIR__ . '/../Layoust/footer.php';
?>
