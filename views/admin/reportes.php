<?php
// Protección: solo administradores pueden acceder a reportes
require_once __DIR__ . '/../../controllers/auth/AuthController.php';
if (!AuthController::esAdmin()) {
    header('Location: ../seller/mis_ventas.php');
    exit;
}

// Incluir el header
require_once __DIR__ . '/../Layoust/header.php';
?>

<div class="container-fluid py-2">
    <!-- Encabezado y Filtros -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
        <h2 class="h4 mb-0 text-gray-800 fw-bold d-flex align-items-center">
            <i class="fa-solid fa-chart-line text-primary-custom me-2"></i> Historial y Reportes
        </h2>
        
        <form id="form-filtros" class="d-flex gap-2">
            <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" title="Fecha Inicio">
            <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" title="Fecha Fin">
            <button type="submit" class="btn btn-primary d-flex align-items-center gap-2" id="btn-filtrar">
                <i class="fa-solid fa-filter"></i> Filtrar
            </button>
        </form>
    </div>

    <!-- Tarjetas de Resumen -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow-sm h-100 py-2 border-0" style="border-left: 4px solid var(--bs-warning); border-radius: 12px; transition: transform 0.3s ease;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #4a5568; letter-spacing: 0.5px;">
                                Valor del Inventario</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="resumen-inventario">$0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x" style="color: rgba(244, 196, 48, 0.3);"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow-sm h-100 py-2 border-0" style="border-left: 4px solid var(--bs-primary); border-radius: 12px; transition: transform 0.3s ease;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #4a5568; letter-spacing: 0.5px;">
                                Ingresos (Ventas)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="resumen-ingresos">$0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x" style="color: rgba(61, 169, 224, 0.2);"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow-sm h-100 py-2 border-0" style="border-left: 4px solid var(--bs-danger); border-radius: 12px; transition: transform 0.3s ease;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #4a5568; letter-spacing: 0.5px;">
                                N° de Ventas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="resumen-cantidad">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x" style="color: rgba(232, 93, 93, 0.2);"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold" style="color: var(--bs-primary);">Ingresos por Periodo</h6>
                </div>
                <div class="card-body">
                    <canvas id="chart-ingresos"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold" style="color: var(--bs-warning);">Top Productos Más Vendidos</h6>
                </div>
                <div class="card-body">
                    <canvas id="chart-top-productos"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Historial de Ventas -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-dark">Historial de Transacciones</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tabla-historial">
                    <thead class="table-light text-muted" style="font-size: 0.85rem;">
                        <tr>
                            <th class="ps-4">Factura</th>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th>Productos (Cant)</th>
                            <th>Método Pago</th>
                            <th class="text-end pe-4">Total</th>
                        </tr>
                    </thead>
                    <tbody id="historial-tbody">
                        <tr><td colspan="6" class="text-center py-4">Cargando datos...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    let chartIngresos = null;
    let chartTop = null;

    const formFiltros = document.getElementById('form-filtros');
    const tbodyHistorial = document.getElementById('historial-tbody');
    const btnFiltrar = document.getElementById('btn-filtrar');
    
    // Elementos de resumen
    const resInv = document.getElementById('resumen-inventario');
    const resIng = document.getElementById('resumen-ingresos');
    const resCant = document.getElementById('resumen-cantidad');

    // Cargar datos iniciales
    cargarDatos();

    formFiltros.addEventListener('submit', function(e) {
        e.preventDefault();
        cargarDatos();
    });

    function cargarDatos() {
        const fechaInicio = document.getElementById('fecha_inicio').value;
        const fechaFin = document.getElementById('fecha_fin').value;
        
        btnFiltrar.disabled = true;
        btnFiltrar.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Cargando...';

        const url = `../../controllers/auth/ReporteController.php?action=obtenerDatosAjax&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    actualizarResumen(data.resumen);
                    actualizarGraficos(data.grafico_ingresos, data.grafico_top);
                    actualizarTabla(data.historial);
                } else {
                    console.error(data.mensaje);
                    if(typeof Swal !== 'undefined') Swal.fire('Error', data.mensaje, 'error');
                }
            })
            .catch(err => console.error("Error obteniendo datos:", err))
            .finally(() => {
                btnFiltrar.disabled = false;
                btnFiltrar.innerHTML = '<i class="fa-solid fa-filter"></i> Filtrar';
            });
    }

    function formatMoneda(valor) {
        return new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(valor);
    }

    function actualizarResumen(resumen) {
        resInv.innerText = formatMoneda(resumen.valor_inventario);
        resIng.innerText = formatMoneda(resumen.ingresos_totales);
        resCant.innerText = resumen.total_ventas;
    }

    function actualizarTabla(historial) {
        tbodyHistorial.innerHTML = '';
        
        if (historial.length === 0) {
            tbodyHistorial.innerHTML = `<tr><td colspan="6" class="text-center py-5 text-muted">
                <i class="fa-solid fa-folder-open fa-3x mb-3 opacity-50"></i><br>
                No se encontraron resultados para los filtros aplicados.
            </td></tr>`;
            return;
        }

        historial.forEach(v => {
            const tr = document.createElement('tr');
            
            // Construir tooltip de detalles
            let detallesTexto = v.detalles.map(d => `- ${d.nombre} (x${d.cantidad})`).join('\n');
            
            tr.innerHTML = `
                <td class="ps-4 fw-bold text-dark"><span class="badge bg-light text-dark border">${v.codigo_factura}</span></td>
                <td>${new Date(v.fecha).toLocaleString('es-CO')}</td>
                <td><i class="fa-solid fa-user text-muted me-1"></i> ${v.usuario_nombre}</td>
                <td>
                    <span class="fw-bold" title="${detallesTexto}" style="cursor:help;color:var(--bs-primary);">
                        ${v.total_articulos || 0} ítems
                    </span>
                </td>
                <td><span class="badge bg-secondary">${v.metodo_pago}</span></td>
                <td class="text-end pe-4 fw-bold" style="color: var(--bs-primary);">${formatMoneda(v.total)}</td>
            `;
            tbodyHistorial.appendChild(tr);
        });
    }

    function actualizarGraficos(datosIngresos, datosTop) {
        // Colores base (Gris, Azul, Amarillo, Coral)
        const colorAzul = '#3DA9E0';
        const colorAmarillo = '#F4C430';
        const colorCoral = '#E85D5D';
        const colorCarbon = '#3E3A36';

        // 1. Gráfico Ingresos (Líneas)
        const labelsIngresos = datosIngresos.map(d => d.fecha_corta);
        const valoresIngresos = datosIngresos.map(d => d.ingresos);

        const ctxIngresos = document.getElementById('chart-ingresos').getContext('2d');
        if (chartIngresos) chartIngresos.destroy();
        
        chartIngresos = new Chart(ctxIngresos, {
            type: 'line',
            data: {
                labels: labelsIngresos.length ? labelsIngresos : ['Sin datos'],
                datasets: [{
                    label: 'Ingresos Diarios',
                    data: valoresIngresos.length ? valoresIngresos : [0],
                    borderColor: colorAzul,
                    backgroundColor: 'rgba(61, 169, 224, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: colorAzul
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: function(value) { return '$' + value; } } }
                }
            }
        });

        // 2. Gráfico Top Productos (Barras Horizontales)
        const labelsTop = datosTop.map(d => d.nombre.substring(0,20) + '...');
        const valoresTop = datosTop.map(d => d.total_vendido);

        const ctxTop = document.getElementById('chart-top-productos').getContext('2d');
        if (chartTop) chartTop.destroy();

        chartTop = new Chart(ctxTop, {
            type: 'bar',
            data: {
                labels: labelsTop.length ? labelsTop : ['Sin datos'],
                datasets: [{
                    label: 'Unidades Vendidas',
                    data: valoresTop.length ? valoresTop : [0],
                    backgroundColor: [colorAmarillo, colorAzul, colorCoral, colorCarbon, '#858796'],
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    }
});
</script>

<!-- ══════════════════════════════════════════
     MODAL FACTURA IMPRIMIBLE (HU-019)
══════════════════════════════════════════ -->
<div class="modal fade" id="modalFactura" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-0 d-print-none"
                 style="background:linear-gradient(135deg,#62483F,#B87983);">
                <h5 class="modal-title fw-bold text-white">
                    <i class="fa-solid fa-file-invoice me-2"></i>Factura de Venta
                </h5>
                <div class="ms-auto d-flex gap-2">
                    <button type="button" class="btn-close btn-close-white"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-0" id="factura-contenido">
                <!-- Se llena dinámicamente -->
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-spinner fa-spin fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
/* ── Estilos de impresión ── */
const estiloImpresion = `
    @media print {
        body * { visibility: hidden !important; }
        #factura-contenido, #factura-contenido * { visibility: visible !important; }
        #factura-contenido { position: fixed; top: 0; left: 0; width: 100%; }
        .d-print-none { display: none !important; }
    }
`;
const styleEl = document.createElement('style');
styleEl.textContent = estiloImpresion;
document.head.appendChild(styleEl);

const modalFacturaEl = new bootstrap.Modal(document.getElementById('modalFactura'));

function verFactura(ventaId) {
    const contenido = document.getElementById('factura-contenido');
    contenido.innerHTML = '<div class="text-center py-5 text-muted"><i class="fa-solid fa-spinner fa-spin fa-2x"></i><p class="mt-2">Cargando factura...</p></div>';
    modalFacturaEl.show();

    fetch(`../../controllers/auth/ReporteController.php?action=facturaAjax&venta_id=${ventaId}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                contenido.innerHTML = `<div class="text-center py-5 text-danger"><i class="fa-solid fa-circle-exclamation fa-2x mb-2"></i><p>${data.mensaje}</p></div>`;
                return;
            }
            contenido.innerHTML = construirHTMLFactura(data.venta, data.config);
        })
        .catch(() => {
            contenido.innerHTML = '<div class="text-center py-5 text-danger">Error al cargar la factura.</div>';
        });
}

function construirHTMLFactura(v, cfg) {
    const fmt = val => new Intl.NumberFormat('es-CO',{style:'currency',currency:'COP',maximumFractionDigits:0}).format(val);
    const fecha = new Date(v.fecha).toLocaleString('es-CO', {
        year:'numeric', month:'long', day:'numeric',
        hour:'2-digit', minute:'2-digit'
    });

    // Filas de detalle
    let filasDetalle = '';
    (v.detalles || []).forEach((d, i) => {
        filasDetalle += `
            <tr style="border-bottom:1px solid #f0ebe8;">
                <td style="padding:8px 12px; color:#62483F;">${i+1}</td>
                <td style="padding:8px 12px;">
                    <strong>${d.nombre}</strong><br>
                    <small style="color:#9b7b73;">Ref: ${d.referencia}</small>
                </td>
                <td style="padding:8px 12px; text-align:center;">${d.cantidad} mt</td>
                <td style="padding:8px 12px; text-align:right;">${fmt(d.precio_unitario)}</td>
                <td style="padding:8px 12px; text-align:right; font-weight:700;">${fmt(d.subtotal)}</td>
            </tr>`;
    });

    const iva = parseFloat(v.total) - parseFloat(v.subtotal);
    const hayIva = iva > 0.01;

    return `
    <div style="font-family:'Inter','Segoe UI',sans-serif; max-width:700px; margin:0 auto; padding:32px 28px; color:#2C1A14;">

        <!-- Encabezado empresa -->
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:24px;">
            <div>
                <h2 style="margin:0; font-size:1.6rem; font-weight:800; color:#62483F;">${cfg.nombre_empresa || 'Bella Tela'}</h2>
                <p style="margin:4px 0 0; color:#9b7b73; font-size:0.85rem;">
                    NIT: ${cfg.nit || '—'} &nbsp;|&nbsp; ${cfg.telefono || '—'}<br>
                    ${cfg.email || '—'}<br>
                    ${cfg.direccion || '—'}
                </p>
            </div>
            <div style="text-align:right;">
                <div style="background:linear-gradient(135deg,#62483F,#B87983); color:#fff; padding:10px 18px; border-radius:10px;">
                    <div style="font-size:0.7rem; letter-spacing:1px; opacity:0.8; text-transform:uppercase;">Factura</div>
                    <div style="font-size:1.1rem; font-weight:800;">${v.codigo_factura}</div>
                </div>
                <p style="margin:8px 0 0; font-size:0.8rem; color:#9b7b73;">${fecha}</p>
            </div>
        </div>

        <!-- Línea divisoria -->
        <hr style="border:none; border-top:2px solid #f0ebe8; margin:0 0 20px;">

        <!-- Info venta -->
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:24px;">
            <div style="background:#FAF6F4; border-radius:10px; padding:12px 16px;">
                <div style="font-size:0.7rem; color:#9b7b73; text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Vendedor</div>
                <div style="font-weight:700;">${v.usuario_nombre}</div>
            </div>
            <div style="background:#FAF6F4; border-radius:10px; padding:12px 16px;">
                <div style="font-size:0.7rem; color:#9b7b73; text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Método de Pago</div>
                <div style="font-weight:700;">${v.metodo_pago}</div>
            </div>
        </div>

        <!-- Tabla de productos -->
        <table style="width:100%; border-collapse:collapse; margin-bottom:20px;">
            <thead>
                <tr style="background:#62483F; color:#fff;">
                    <th style="padding:10px 12px; text-align:left; font-size:0.8rem; border-radius:8px 0 0 0;">#</th>
                    <th style="padding:10px 12px; text-align:left; font-size:0.8rem;">Producto</th>
                    <th style="padding:10px 12px; text-align:center; font-size:0.8rem;">Cant.</th>
                    <th style="padding:10px 12px; text-align:right; font-size:0.8rem;">Precio/mt</th>
                    <th style="padding:10px 12px; text-align:right; font-size:0.8rem; border-radius:0 8px 0 0;">Subtotal</th>
                </tr>
            </thead>
            <tbody>${filasDetalle}</tbody>
        </table>

        <!-- Totales -->
        <div style="display:flex; justify-content:flex-end;">
            <div style="min-width:260px;">
                <div style="display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #f0ebe8;">
                    <span style="color:#9b7b73;">Subtotal</span>
                    <span style="font-weight:600;">${fmt(v.subtotal)}</span>
                </div>
                ${hayIva ? `
                <div style="display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #f0ebe8;">
                    <span style="color:#9b7b73;">IVA</span>
                    <span style="font-weight:600;">${fmt(iva)}</span>
                </div>` : ''}
                <div style="display:flex; justify-content:space-between; padding:10px 0; margin-top:4px;
                                border-top:2px solid #62483F;">
                            <span style="font-size:1.1rem; font-weight:800; color:#62483F;">TOTAL</span>
                            <span style="font-size:1.2rem; font-weight:800; color:#B87983;">${fmt(v.total)}</span>
                </div>
            </div>
        </div>

        <!-- Pie -->
        <div style="margin-top:28px; padding-top:16px; border-top:1px dashed #e0d5d0; text-align:center;
                    font-size:0.78rem; color:#9b7b73;">
            Gracias por su compra &nbsp;•&nbsp; ${cfg.nombre_empresa || 'Bella Tela'}
            &nbsp;•&nbsp; ${cfg.tipo_facturacion || 'Régimen Simplificado'}
        </div>
    </div>`;
}

</script>

<?php require_once __DIR__ . '/../Layoust/footer.php'; ?>

