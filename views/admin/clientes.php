<?php
require_once __DIR__ . '/../Layoust/header.php';
?>

<div class="container-fluid py-2">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="h4 mb-1 fw-bold text-gray-800">
                <i class="fa-solid fa-users text-primary-custom me-2"></i>Compras por cliente
            </h2>
            <p class="text-muted mb-0">Cada compra confirmada recibe un ID automático. No se solicita ni se guarda el nombre del comprador.</p>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3">
            <form id="form-buscador" class="d-flex gap-2">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                    <input type="text" id="input-busqueda" class="form-control border-start-0 bg-light"
                           placeholder="Buscar por ID de cliente o factura..." aria-label="Buscar por ID de cliente o factura">
                </div>
                <button type="submit" class="btn btn-secondary px-4">Buscar</button>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted">
                        <tr>
                            <th class="ps-4">ID de cliente</th>
                            <th>Factura de compra</th>
                            <th>Fecha</th>
                            <th>Método de pago</th>
                            <th class="text-end pe-4">Total</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-clientes">
                        <tr><td colspan="5" class="text-center py-4 text-muted"><i class="fa-solid fa-spinner fa-spin me-2"></i>Cargando compras...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-buscador');
    const input = document.getElementById('input-busqueda');
    form.addEventListener('submit', event => {
        event.preventDefault();
        cargarCompras(input.value.trim());
    });
    input.addEventListener('input', () => {
        if (input.value.trim() === '') cargarCompras();
    });
    cargarCompras();
});

function cargarCompras(busqueda = '') {
    const tbody = document.getElementById('tabla-clientes');
    tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted"><i class="fa-solid fa-spinner fa-spin me-2"></i>Cargando compras...</td></tr>';

    fetch(`../../controllers/auth/ClienteController.php?action=listarAjax&q=${encodeURIComponent(busqueda)}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) throw new Error(data.mensaje || 'No se pudieron cargar las compras.');
            tbody.replaceChildren();
            if (data.clientes.length === 0) {
                const row = tbody.insertRow();
                const cell = row.insertCell();
                cell.colSpan = 5;
                cell.className = 'text-center py-5 text-muted';
                cell.textContent = 'Aún no hay compras registradas para esta búsqueda.';
                return;
            }

            const formatoMoneda = value => new Intl.NumberFormat('es-CO', {
                style: 'currency',
                currency: 'COP',
                maximumFractionDigits: 0
            }).format(Number(value));

            data.clientes.forEach(compra => {
                const row = tbody.insertRow();
                const idCell = row.insertCell();
                idCell.className = 'ps-4 fw-bold';
                idCell.textContent = `Cliente #${compra.id}`;

                const facturaCell = row.insertCell();
                const link = document.createElement('a');
                link.href = `../factura.php?venta_id=${encodeURIComponent(compra.venta_id)}`;
                link.target = '_blank';
                link.rel = 'noopener';
                link.className = 'fw-semibold';
                link.textContent = compra.codigo_factura;
                facturaCell.appendChild(link);

                const fechaCell = row.insertCell();
                fechaCell.textContent = new Date(compra.fecha_venta.replace(' ', 'T')).toLocaleString('es-CO');

                const pagoCell = row.insertCell();
                pagoCell.textContent = compra.metodo_pago || '—';

                const totalCell = row.insertCell();
                totalCell.className = 'text-end pe-4 fw-bold';
                totalCell.textContent = formatoMoneda(compra.total);
            });
        })
        .catch(error => {
            console.error(error);
            tbody.replaceChildren();
            const row = tbody.insertRow();
            const cell = row.insertCell();
            cell.colSpan = 5;
            cell.className = 'text-center py-4 text-danger';
            cell.textContent = error.message || 'Error de conexión al cargar las compras.';
        });
}
</script>

<?php require_once __DIR__ . '/../Layoust/footer.php'; ?>
