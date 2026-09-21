<?php
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

// Incluir el header (que automáticamente incluye el topbar y el sidebar)
require_once __DIR__ . '/../Layoust/header.php';
?>

<!-- Contenido Específico: Punto de Venta -->
<div class="container-fluid py-2">
    
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0 text-gray-800 fw-bold">
            <i class="fa-solid fa-file-invoice-dollar text-primary-custom me-2"></i> Punto de Venta
        </h2>
    </div>

    <div class="row">
        <!-- Columna Izquierda: Buscador de Productos -->
        <div class="col-lg-7 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                        <input type="text" id="buscador-productos" class="form-control border-start-0 bg-light" placeholder="Buscar producto por referencia, nombre o color...">
                    </div>
                </div>
                <div class="card-body p-0" style="max-height: 600px; overflow-y: auto;">
                    <div id="resultados-productos" class="list-group list-group-flush">
                        <!-- Los resultados se llenarán con JS -->
                        <div class="p-4 text-center text-muted">
                            <div class="spinner-border text-primary mb-2 d-none" id="spinner-busqueda" role="status"></div>
                            <p class="mb-0" id="mensaje-busqueda">Escribe para buscar o presiona enter para cargar todos.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Carrito y Totales -->
        <div class="col-lg-5 mb-4">
            <div class="card shadow-sm border-0 h-100" style="border-top: 4px solid var(--bs-primary) !important;">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <h6 class="m-0 font-weight-bold text-primary-custom">Detalle de Venta</h6>
                    <span class="badge bg-primary rounded-pill" id="cart-count">0 ítems</span>
                </div>
                <div class="card-body p-0 d-flex flex-column">
                    <!-- Lista del carrito -->
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
                                <!-- Elementos del carrito via JS -->
                                <tr><td colspan="4" class="text-center text-muted py-4">El carrito está vacío</td></tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Desglose de Totales -->
                    <div class="mt-auto">
                        <div class="bg-light p-3 rounded mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted fw-bold small">SUBTOTAL</span>
                                <span class="h6 mb-0 fw-bold text-dark" id="txt-subtotal">$0</span>
                            </div>
                            <?php if ($impuestoConf > 0): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted fw-bold small">IVA (<?php echo $impuestoConf; ?>%)</span>
                                <span class="h6 mb-0 fw-bold text-dark" id="txt-iva">$0</span>
                            </div>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top border-dark border-opacity-10">
                                <span class="text-dark fw-bold">TOTAL A PAGAR</span>
                                <span class="h3 mb-0 fw-bold text-success" id="txt-total">$0</span>
                            </div>
                        </div>
                        
                        <div class="mb-3 px-3">
                            <label class="form-label small text-muted fw-bold">Método de Pago</label>
                            <select id="metodo-pago" class="form-select mb-3">
                                <option value="Efectivo">Efectivo</option>
                                <option value="Transferencia">Transferencia</option>
                                <option value="Tarjeta">Tarjeta de Crédito/Débito</option>
                            </select>

                            <label class="form-label small text-muted fw-bold">Cliente Asociado (Opcional)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-user"></i></span>
                                <select id="cliente-id" class="form-select">
                                    <option value="">Consumidor Final (Sin nombre)</option>
                                    <!-- Options via JS -->
                                </select>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 p-3">
                            <button class="btn btn-primary btn-lg fw-bold" id="btn-confirmar" disabled>
                                <i class="fa-solid fa-check-circle me-2"></i>Confirmar Venta
                            </button>
                            <button class="btn btn-outline-danger fw-bold" id="btn-cancelar" disabled>
                                <i class="fa-solid fa-trash-alt me-2"></i>Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lógica del Punto de Venta -->
<script>
    let carrito = [];
    const tasaImpuesto = <?php echo json_encode($impuestoConf); ?>;

    document.addEventListener("DOMContentLoaded", function() {
        const inputBuscador = document.getElementById('buscador-productos');
        const contenedorResultados = document.getElementById('resultados-productos');
        const spinner = document.getElementById('spinner-busqueda');
        const mensaje = document.getElementById('mensaje-busqueda');
        
        const tbodyCarrito = document.getElementById('carrito-tbody');
        const txtSubtotal = document.getElementById('txt-subtotal');
        const txtTotal = document.getElementById('txt-total');
        const cartCount = document.getElementById('cart-count');
        
        const btnConfirmar = document.getElementById('btn-confirmar');
        const btnCancelar = document.getElementById('btn-cancelar');
        const selectMetodo = document.getElementById('metodo-pago');
        const selectCliente = document.getElementById('cliente-id');

        let timerBusqueda = null;

        // --- CARGAR CLIENTES ---
        fetch('../../controllers/auth/ClienteController.php?action=listarAjax')
            .then(res => res.json())
            .then(data => {
                data.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.innerText = c.nombre + ' (' + c.documento + ')';
                    selectCliente.appendChild(opt);
                });
            })
            .catch(err => console.error("Error cargando clientes:", err));

        // --- BUSCADOR ---
        inputBuscador.addEventListener('input', function() {
            clearTimeout(timerBusqueda);
            timerBusqueda = setTimeout(() => {
                buscarProductos(this.value);
            }, 300); // Debounce
        });

        inputBuscador.addEventListener('focus', function() {
            if(this.value.trim() === '') buscarProductos('');
        });

        function buscarProductos(query) {
            spinner.classList.remove('d-none');
            mensaje.classList.add('d-none');
            contenedorResultados.innerHTML = '';
            contenedorResultados.appendChild(spinner);
            contenedorResultados.appendChild(mensaje);

            fetch(`../../controllers/auth/VentaController.php?action=buscarAjax&q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    spinner.classList.add('d-none');
                    renderizarResultados(data);
                })
                .catch(err => {
                    console.error("Error buscando productos:", err);
                    spinner.classList.add('d-none');
                    mensaje.classList.remove('d-none');
                    mensaje.innerText = "Error de conexión.";
                });
        }

        function renderizarResultados(productos) {
            const items = contenedorResultados.querySelectorAll('.producto-item');
            items.forEach(i => i.remove());

            if (productos.length === 0) {
                mensaje.classList.remove('d-none');
                mensaje.innerText = "No se encontraron productos disponibles.";
                return;
            }

            productos.forEach(p => {
                const item = document.createElement('a');
                item.href = "#";
                item.className = "list-group-item list-group-item-action d-flex justify-content-between align-items-center p-3 producto-item";
                const precioFormat = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(p.precio);

                item.innerHTML = `
                    <div class="d-flex align-items-center">
                        <div class="bg-light rounded p-2 me-3 text-center border" style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-box text-muted" style="font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold text-dark">${p.nombre}</h6>
                            <small class="text-muted">Ref: ${p.referencia} | Color: <span class="badge bg-secondary">${p.color}</span></small>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold" style="color: var(--bs-primary);">${precioFormat}</div>
                        <small class="text-success fw-bold"><i class="fa-solid fa-check-circle me-1"></i>Stock: ${p.stock}</small>
                    </div>
                `;

                item.addEventListener('click', (e) => {
                    e.preventDefault();
                    agregarAlCarrito(p);
                });

                contenedorResultados.appendChild(item);
            });
        }

        // --- CARRITO ---
        function agregarAlCarrito(producto) {
            const existe = carrito.find(item => item.id === producto.id);
            if (existe) {
                if (existe.cantidad < parseInt(producto.stock)) {
                    existe.cantidad++;
                } else {
                    if(typeof Swal !== 'undefined') {
                        Swal.fire('Stock Máximo', 'No puedes agregar más unidades de las disponibles.', 'warning');
                    } else {
                        alert('Stock Máximo alcanzado.');
                    }
                }
            } else {
                carrito.push({
                    id: producto.id,
                    referencia: producto.referencia,
                    nombre: producto.nombre,
                    precio: parseFloat(producto.precio),
                    cantidad: 1,
                    stockMax: parseInt(producto.stock)
                });
            }
            renderizarCarrito();
        }

        function eliminarDelCarrito(id) {
            carrito = carrito.filter(item => item.id !== id);
            renderizarCarrito();
        }

        function cambiarCantidad(id, nuevaCantidad) {
            const item = carrito.find(i => i.id === id);
            if (!item) return;

            let cant = parseInt(nuevaCantidad);
            if (isNaN(cant) || cant < 1) cant = 1;
            if (cant > item.stockMax) cant = item.stockMax;
            
            item.cantidad = cant;
            renderizarCarrito();
        }

        function renderizarCarrito() {
            tbodyCarrito.innerHTML = '';
            let subtotal = 0;
            let totalItems = 0;

            if (carrito.length === 0) {
                tbodyCarrito.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">El carrito está vacío</td></tr>';
                btnConfirmar.disabled = true;
                btnCancelar.disabled = true;
            } else {
                btnConfirmar.disabled = false;
                btnCancelar.disabled = false;

                carrito.forEach(item => {
                    const itemSubtotal = item.precio * item.cantidad;
                    subtotal += itemSubtotal;
                    totalItems += item.cantidad;

                    const precioFmt = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(item.precio);
                    const subtFmt = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(itemSubtotal);

                    const tr = document.createElement('tr');
                    tr.className = "align-middle";
                    tr.innerHTML = `
                        <td>
                            <div class="fw-bold text-dark" style="font-size: 0.9rem;">${item.nombre}</div>
                            <small class="text-muted">Ref: ${item.referencia} | ${precioFmt}</small>
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm text-center" value="${item.cantidad}" min="1" max="${item.stockMax}" onchange="window.posCambiarCantidad(${item.id}, this.value)">
                        </td>
                        <td class="text-end fw-bold text-dark">${subtFmt}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-danger border-0" onclick="window.posEliminar(${item.id})">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        </td>
                    `;
                    tbodyCarrito.appendChild(tr);
                });
            }

            window.posEliminar = eliminarDelCarrito;
            window.posCambiarCantidad = cambiarCantidad;

            const iva = subtotal * (tasaImpuesto / 100);
            const total = subtotal + iva;

            const subFmt = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(subtotal);
            const ivaFmt = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(iva);
            const totalFmt = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(total);
            
            txtSubtotal.innerText = subFmt;
            if (document.getElementById('txt-iva')) {
                document.getElementById('txt-iva').innerText = ivaFmt;
            }
            txtTotal.innerText = totalFmt;
            cartCount.innerText = totalItems + (totalItems === 1 ? ' ítem' : ' ítems');
        }

        // --- ACCIONES ---
        btnCancelar.addEventListener('click', () => {
            if(typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Cancelar venta?',
                    text: "Se vaciará el carrito por completo.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e74a3b',
                    cancelButtonColor: '#858796',
                    confirmButtonText: 'Sí, cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        carrito = [];
                        renderizarCarrito();
                    }
                });
            } else {
                if(confirm('¿Seguro que deseas cancelar la venta?')) {
                    carrito = [];
                    renderizarCarrito();
                }
            }
        });

        btnConfirmar.addEventListener('click', () => {
            if (carrito.length === 0) return;

            const btnOriginalText = btnConfirmar.innerHTML;
            btnConfirmar.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...';
            btnConfirmar.disabled = true;

            const currentSubtotal = carrito.reduce((acc, curr) => acc + (curr.precio * curr.cantidad), 0);
            const currentIva = currentSubtotal * (tasaImpuesto / 100);
            const currentTotal = currentSubtotal + currentIva;

            const payload = {
                carrito: carrito.map(i => ({ id: i.id, cantidad: i.cantidad, precio: i.precio })),
                metodo_pago: selectMetodo.value,
                cliente_id: selectCliente.value ? parseInt(selectCliente.value) : null,
                subtotal: currentSubtotal,
                iva: currentIva,
                total: currentTotal
            };

            fetch('../../controllers/auth/VentaController.php?action=procesarAjax', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if(typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: '¡Venta Confirmada!',
                            text: `Factura generada: ${data.codigo_factura}`,
                            icon: 'success',
                            confirmButtonColor: '#10b981'
                        }).then(() => {
                            carrito = [];
                            renderizarCarrito();
                            buscarProductos(inputBuscador.value);
                        });
                    } else {
                        alert('¡Venta Exitosa!\nFactura: ' + data.codigo_factura);
                        carrito = [];
                        renderizarCarrito();
                        buscarProductos(inputBuscador.value);
                    }
                } else {
                    throw new Error(data.mensaje || 'Error desconocido.');
                }
            })
            .catch(err => {
                console.error(err);
                if(typeof Swal !== 'undefined') {
                    Swal.fire('Error', err.message || 'No se pudo procesar la venta.', 'error');
                } else {
                    alert('Error: ' + err.message);
                }
            })
            .finally(() => {
                btnConfirmar.innerHTML = btnOriginalText;
                btnConfirmar.disabled = carrito.length === 0;
            });
        });

    });
</script>

<?php
// Incluir el footer
require_once __DIR__ . '/../Layoust/footer.php';
?>

