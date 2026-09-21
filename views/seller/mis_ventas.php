<?php
/**
 * VISTA: Punto de Venta para Empleados (rol 2)
 * 
 * Los empleados usan el mismo POS que el admin.
 * El header.php verifica la sesión y carga el sidebarseller.php automáticamente según el rol.
 */

require_once __DIR__ . '/../../controllers/auth/VentaController.php';
$vc = new VentaController();

// Obtener impuesto de configuración
require_once __DIR__ . '/../../config/database.php';
global $conn;
$resConf = $conn->query("SELECT impuesto FROM configuracion WHERE id = 1");
$impuestoConf = 0;
if ($resConf && $resConf->num_rows > 0) {
    $rowConf = $resConf->fetch_assoc();
    $impuestoConf = (float)$rowConf['impuesto'];
}

// Incluir el header (verifica sesión, carga topbar y sidebar del empleado)
require_once __DIR__ . '/../Layoust/header.php';
?>

<!-- Contenido: Punto de Venta Empleado -->
<div class="container-fluid py-2">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0 fw-bold" style="color: var(--color-primario);">
            <i class="fa-solid fa-file-invoice-dollar me-2" style="color: var(--color-secundario);"></i>
            Punto de Venta
        </h2>
    </div>

    <div class="row">
        <!-- Buscador de Productos -->
        <div class="col-lg-7 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fa-solid fa-search text-muted"></i>
                        </span>
                        <input type="text" id="buscador-productos"
                               class="form-control border-start-0 bg-light"
                               placeholder="Buscar por nombre, referencia o color...">
                    </div>
                </div>
                <div class="card-body p-0" style="max-height: 600px; overflow-y: auto;">
                    <div id="resultados-productos" class="list-group list-group-flush">
                        <div class="p-4 text-center text-muted">
                            <div class="spinner-border mb-2 d-none" id="spinner-busqueda"
                                 role="status" style="color: var(--color-secundario);"></div>
                            <p class="mb-0" id="mensaje-busqueda">
                                Escribe para buscar o haz clic para cargar todos los productos.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Carrito y Totales -->
        <div class="col-lg-5 mb-4">
            <div class="card shadow-sm border-0 h-100"
                 style="border-top: 4px solid var(--color-secundario) !important;">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <h6 class="m-0 fw-bold" style="color: var(--color-primario);">
                        Detalle de Venta
                    </h6>
                    <span class="badge rounded-pill" id="cart-count"
                          style="background-color: var(--color-secundario);">0 ítems</span>
                </div>
                <div class="card-body p-0 d-flex flex-column">
                    <!-- Tabla del carrito -->
                    <div class="flex-grow-1" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Producto</th>
                                    <th width="90">Cant.</th>
                                    <th class="text-end">Subtotal</th>
                                    <th width="50"></th>
                                </tr>
                            </thead>
                            <tbody id="carrito-tbody">
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        El carrito está vacío
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Totales -->
                    <div class="mt-auto">
                        <div class="bg-light p-3 rounded mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted fw-bold small">SUBTOTAL</span>
                                <span class="h6 mb-0 fw-bold text-dark" id="txt-subtotal">$0</span>
                            </div>
                            <?php if ($impuestoConf > 0): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted fw-bold small">
                                    IVA (<?= $impuestoConf ?>%)
                                </span>
                                <span class="h6 mb-0 fw-bold text-dark" id="txt-iva">$0</span>
                            </div>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                <span class="fw-bold text-dark">TOTAL A PAGAR</span>
                                <span class="h3 mb-0 fw-bold text-success" id="txt-total">$0</span>
                            </div>
                        </div>

                        <div class="mb-3 px-3">
                            <label class="form-label small fw-bold text-muted">Método de Pago</label>
                            <select id="metodo-pago" class="form-select mb-3">
                                <option value="Efectivo">Efectivo</option>
                                <option value="Transferencia">Transferencia</option>
                                <option value="Tarjeta">Tarjeta de Crédito/Débito</option>
                            </select>

                            <label class="form-label small fw-bold text-muted">
                                Cliente Asociado (Opcional)
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fa-solid fa-user"></i>
                                </span>
                                <select id="cliente-id" class="form-select">
                                    <option value="">Consumidor Final</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-grid gap-2 p-3">
                                <button class="btn btn-lg fw-bold text-white pos-action" id="btn-confirmar" disabled
                                    aria-disabled="true" title="Agrega al menos un producto para confirmar la venta"
                                    style="background-color: var(--color-secundario);">
                                <i class="fa-solid fa-check-circle me-2"></i>Confirmar Venta
                            </button>
                                <button class="btn btn-outline-danger fw-bold pos-action" id="btn-cancelar" disabled
                                    aria-disabled="true" title="No hay productos para cancelar">
                                <i class="fa-solid fa-trash-alt me-2"></i>Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let carrito = [];
    const tasaImpuesto = <?= json_encode($impuestoConf) ?>;

    document.addEventListener('DOMContentLoaded', function () {
        const inputBuscador        = document.getElementById('buscador-productos');
        const contenedorResultados = document.getElementById('resultados-productos');
        const spinner              = document.getElementById('spinner-busqueda');
        const mensaje              = document.getElementById('mensaje-busqueda');
        const tbodyCarrito         = document.getElementById('carrito-tbody');
        const txtSubtotal          = document.getElementById('txt-subtotal');
        const txtTotal             = document.getElementById('txt-total');
        const cartCount            = document.getElementById('cart-count');
        const btnConfirmar         = document.getElementById('btn-confirmar');
        const btnCancelar          = document.getElementById('btn-cancelar');
        const selectMetodo         = document.getElementById('metodo-pago');
        const selectCliente        = document.getElementById('cliente-id');
        let timerBusqueda = null;

        // Cargar clientes al iniciar
        fetch('../../controllers/auth/ClienteController.php?action=listarAjax')
            .then(r => r.json())
            .then(data => {
                data.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value    = c.id;
                    opt.innerText = c.nombre + ' (' + c.documento + ')';
                    selectCliente.appendChild(opt);
                });
            })
            .catch(err => console.error('Error cargando clientes:', err));

        // Buscador con debounce
        inputBuscador.addEventListener('input', function () {
            clearTimeout(timerBusqueda);
            timerBusqueda = setTimeout(() => buscarProductos(this.value), 300);
        });
        inputBuscador.addEventListener('focus', function () {
            if (this.value.trim() === '') buscarProductos('');
        });

        function buscarProductos(query) {
            spinner.classList.remove('d-none');
            mensaje.classList.add('d-none');
            contenedorResultados.innerHTML = '';
            contenedorResultados.appendChild(spinner);
            contenedorResultados.appendChild(mensaje);

            fetch('../../controllers/auth/VentaController.php?action=buscarAjax&q=' + encodeURIComponent(query))
                .then(r => r.json())
                .then(data => { spinner.classList.add('d-none'); renderizarResultados(data); })
                .catch(() => {
                    spinner.classList.add('d-none');
                    mensaje.classList.remove('d-none');
                    mensaje.innerText = 'Error de conexión.';
                });
        }

        function renderizarResultados(productos) {
            contenedorResultados.querySelectorAll('.producto-item').forEach(i => i.remove());
            if (productos.length === 0) {
                mensaje.classList.remove('d-none');
                mensaje.innerText = 'No se encontraron productos disponibles.';
                return;
            }
            const fmt = v => new Intl.NumberFormat('es-CO', {style:'currency',currency:'COP',maximumFractionDigits:0}).format(v);
            productos.forEach(p => {
                const item = document.createElement('a');
                item.href      = '#';
                item.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center p-3 producto-item';
                item.innerHTML = `
                    <div class="d-flex align-items-center">
                        <div class="bg-light rounded p-2 me-3 text-center border" style="width:50px;height:50px;">
                            <i class="fa-solid fa-box text-muted" style="font-size:1.5rem;"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold text-dark">${p.nombre}</h6>
                            <small class="text-muted">Ref: ${p.referencia} | Color: <span class="badge bg-secondary">${p.color}</span></small>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold" style="color:var(--color-secundario);">${fmt(p.precio)}</div>
                        <small class="text-success fw-bold"><i class="fa-solid fa-check-circle me-1"></i>Stock: ${p.stock}</small>
                    </div>`;
                item.addEventListener('click', e => { e.preventDefault(); agregarAlCarrito(p); });
                contenedorResultados.appendChild(item);
            });
        }

        function agregarAlCarrito(producto) {
            const existe = carrito.find(i => i.id === producto.id);
            if (existe) {
                if (existe.cantidad < parseInt(producto.stock)) {
                    existe.cantidad++;
                } else {
                    Swal.fire('Stock Máximo', 'No puedes agregar más unidades disponibles.', 'warning');
                }
            } else {
                carrito.push({
                    id: producto.id, referencia: producto.referencia,
                    nombre: producto.nombre, precio: parseFloat(producto.precio),
                    cantidad: 1, stockMax: parseInt(producto.stock)
                });
            }
            renderizarCarrito();
        }

        function eliminarDelCarrito(id) {
            carrito = carrito.filter(i => i.id !== id);
            renderizarCarrito();
        }

        function cambiarCantidad(id, val) {
            const item = carrito.find(i => i.id === id);
            if (!item) return;
            let cant = parseInt(val);
            if (isNaN(cant) || cant < 1) cant = 1;
            if (cant > item.stockMax) cant = item.stockMax;
            item.cantidad = cant;
            renderizarCarrito();
        }

        function renderizarCarrito() {
            tbodyCarrito.innerHTML = '';
            let subtotal = 0, totalItems = 0;
            const fmt = v => new Intl.NumberFormat('es-CO',{style:'currency',currency:'COP',maximumFractionDigits:0}).format(v);

            if (carrito.length === 0) {
                tbodyCarrito.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">El carrito está vacío</td></tr>';
                btnConfirmar.disabled = true;
                btnCancelar.disabled  = true;
            } else {
                btnConfirmar.disabled = false;
                btnCancelar.disabled  = false;
                carrito.forEach(item => {
                    const sub = item.precio * item.cantidad;
                    subtotal   += sub;
                    totalItems += item.cantidad;
                    const tr = document.createElement('tr');
                    tr.className = 'align-middle';
                    tr.innerHTML = `
                        <td>
                            <div class="fw-bold text-dark" style="font-size:.9rem;">${item.nombre}</div>
                            <small class="text-muted">Ref: ${item.referencia} | ${fmt(item.precio)}</small>
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm text-center"
                                   value="${item.cantidad}" min="1" max="${item.stockMax}"
                                   onchange="window.posCambiarCantidad(${item.id}, this.value)">
                        </td>
                        <td class="text-end fw-bold text-dark">${fmt(sub)}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-danger border-0"
                                    onclick="window.posEliminar(${item.id})">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        </td>`;
                    tbodyCarrito.appendChild(tr);
                });
            }

            window.posEliminar        = eliminarDelCarrito;
            window.posCambiarCantidad = cambiarCantidad;

            const iva   = subtotal * (tasaImpuesto / 100);
            const total = subtotal + iva;
            const fmt2  = v => new Intl.NumberFormat('es-CO',{style:'currency',currency:'COP',maximumFractionDigits:0}).format(v);

            txtSubtotal.innerText = fmt2(subtotal);
            const elIva = document.getElementById('txt-iva');
            if (elIva) elIva.innerText = fmt2(iva);
            txtTotal.innerText = fmt2(total);
            cartCount.innerText = totalItems + (totalItems === 1 ? ' ítem' : ' ítems');
        }

        btnCancelar.addEventListener('click', () => {
            Swal.fire({
                title: '¿Cancelar venta?', text: 'Se vaciará el carrito.',
                icon: 'warning', showCancelButton: true,
                confirmButtonColor: '#e74a3b', cancelButtonColor: '#858796',
                confirmButtonText: 'Sí, cancelar'
            }).then(r => { if (r.isConfirmed) { carrito = []; renderizarCarrito(); } });
        });

        btnConfirmar.addEventListener('click', () => {
            if (carrito.length === 0) return;
            const orig = btnConfirmar.innerHTML;
            btnConfirmar.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Procesando...';
            btnConfirmar.disabled  = true;

            const sub   = carrito.reduce((a,c) => a + c.precio * c.cantidad, 0);
            const iva   = sub * (tasaImpuesto / 100);
            const total = sub + iva;

            fetch('../../controllers/auth/VentaController.php?action=procesarAjax', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    carrito:     carrito.map(i => ({id:i.id, cantidad:i.cantidad, precio:i.precio})),
                    metodo_pago: selectMetodo.value,
                    cliente_id:  selectCliente.value ? parseInt(selectCliente.value) : null,
                    subtotal: sub, iva, total
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('¡Venta Confirmada!', 'Factura: ' + data.codigo_factura, 'success')
                        .then(() => { carrito = []; renderizarCarrito(); buscarProductos(''); });
                } else {
                    throw new Error(data.mensaje || 'Error desconocido.');
                }
            })
            .catch(err => Swal.fire('Error', err.message || 'No se pudo procesar la venta.', 'error'))
            .finally(() => {
                btnConfirmar.innerHTML = orig;
                btnConfirmar.disabled  = carrito.length === 0;
            });
        });

        // Cargar productos al entrar
        buscarProductos('');
    });
</script>

<?php require_once __DIR__ . '/../Layoust/footer.php'; ?>
