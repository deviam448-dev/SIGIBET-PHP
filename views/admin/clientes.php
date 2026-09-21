<?php
require_once __DIR__ . '/../Layoust/header.php';
?>

<div class="container-fluid py-2">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0 text-gray-800 fw-bold">
            <i class="fa-solid fa-users text-primary-custom me-2"></i> Gestión de Clientes
        </h2>
        <button class="btn btn-primary fw-bold" onclick="abrirModalForm()">
            <i class="fa-solid fa-plus me-1"></i> Nuevo Cliente
        </button>
    </div>

    <!-- Buscador -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3">
            <form id="form-buscador" class="d-flex gap-2">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                    <input type="text" id="input-busqueda" class="form-control border-start-0 bg-light" placeholder="Buscar por nombre o número de documento...">
                </div>
                <button type="submit" class="btn btn-secondary px-4">Buscar</button>
            </form>
        </div>
    </div>

    <!-- Tabla de Clientes -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted">
                        <tr>
                            <th class="ps-4">Nombre / Razón Social</th>
                            <th>Documento</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-clientes">
                        <tr><td colspan="5" class="text-center py-4 text-muted"><i class="fa-solid fa-spinner fa-spin me-2"></i> Cargando clientes...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Formulario Cliente -->
<div class="modal fade" id="modalCliente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header text-white rounded-top-4 border-0" style="background: linear-gradient(135deg, var(--bs-primary), #2C87B8);">
                <h5 class="modal-title fw-bold" id="modalClienteTitulo">Nuevo Cliente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-cliente">
                <div class="modal-body p-4">
                    <input type="hidden" id="cli_id" name="id" value="0">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Nombre Completo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="cli_nombre" name="nombre" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Documento / Cédula <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="cli_documento" name="documento" required>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Teléfono</label>
                            <input type="text" class="form-control" id="cli_telefono" name="telefono">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Correo Electrónico</label>
                            <input type="email" class="form-control" id="cli_email" name="email">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Dirección</label>
                        <input type="text" class="form-control" id="cli_direccion" name="direccion">
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold text-muted small">Observaciones / Inquietudes</label>
                        <textarea class="form-control" id="cli_observaciones" name="observaciones" rows="2" placeholder="Notas sobre el cliente..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4" id="btn-guardar-cli">Guardar Cliente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Perfil e Historial -->
<div class="modal fade" id="modalPerfil" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-0 bg-light pb-0">
                <h5 class="modal-title fw-bold text-dark w-100 d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-address-card text-primary me-2"></i> Perfil del Cliente</span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </h5>
            </div>
            <div class="modal-body p-0">
                <!-- Tarjeta Info -->
                <div class="p-4 border-bottom bg-light">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 60px; height: 60px;" id="perfil-inicial">C</div>
                        <div>
                            <h4 class="mb-0 fw-bold text-dark" id="perfil-nombre">Nombre del Cliente</h4>
                            <p class="text-muted mb-0">CC: <span id="perfil-documento">123456</span> | Tel: <span id="perfil-telefono">--</span></p>
                            <p class="text-muted mb-0 small"><i class="fa-solid fa-envelope me-1"></i> <span id="perfil-email">--</span> | <i class="fa-solid fa-location-dot me-1"></i> <span id="perfil-direccion">--</span></p>
                        </div>
                    </div>
                </div>

                <div class="row g-0">
                    <!-- Observaciones -->
                    <div class="col-md-5 border-end p-4 bg-white">
                        <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-comment-dots text-warning me-2"></i> Inquietudes / Notas</h6>
                        <form id="form-notas-rapidas">
                            <input type="hidden" id="nota_cli_id" name="id">
                            <input type="hidden" id="nota_cli_nombre" name="nombre">
                            <input type="hidden" id="nota_cli_documento" name="documento">
                            <!-- Conservamos los demás campos para actualizar solo notas -->
                            <input type="hidden" id="nota_cli_telefono" name="telefono">
                            <input type="hidden" id="nota_cli_email" name="email">
                            <input type="hidden" id="nota_cli_direccion" name="direccion">
                            
                            <textarea class="form-control bg-light border-0 mb-3" id="nota_cli_observaciones" name="observaciones" rows="5" placeholder="Escribe aquí el seguimiento del cliente..."></textarea>
                            <button type="submit" class="btn btn-sm btn-outline-primary w-100 fw-bold" id="btn-guardar-notas">Actualizar Notas</button>
                        </form>
                    </div>

                    <!-- Historial Compras -->
                    <div class="col-md-7 p-4 bg-white">
                        <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-bag-shopping text-success me-2"></i> Historial de Compras</h6>
                        <div class="table-responsive" style="max-height: 300px;">
                            <table class="table table-sm table-hover align-middle">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Factura</th>
                                        <th>Fecha</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="perfil-historial-tbody">
                                    <!-- js -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let modalForm;
let modalPerfil;
let clienteActualParaNotas = null;

document.addEventListener("DOMContentLoaded", function() {
    modalForm = new bootstrap.Modal(document.getElementById('modalCliente'));
    modalPerfil = new bootstrap.Modal(document.getElementById('modalPerfil'));

    cargarClientes();

    document.getElementById('form-buscador').addEventListener('submit', function(e) {
        e.preventDefault();
        const query = document.getElementById('input-busqueda').value.trim();
        if (query === '') {
            Swal.fire('Atención', 'Ingresa un nombre o documento para buscar.', 'warning');
            return;
        }
        cargarClientes(query);
    });

    document.getElementById('input-busqueda').addEventListener('input', function(e) {
        if(this.value.trim() === '') cargarClientes(); // Restaurar si limpia el buscador
    });

    document.getElementById('form-cliente').addEventListener('submit', function(e) {
        e.preventDefault();
        guardarCliente();
    });

    document.getElementById('form-notas-rapidas').addEventListener('submit', function(e) {
        e.preventDefault();
        guardarNotas();
    });
});

function cargarClientes(query = '') {
    const tbody = document.getElementById('tabla-clientes');
    tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted"><i class="fa-solid fa-spinner fa-spin me-2"></i> Buscando...</td></tr>';

    fetch(`../../controllers/auth/ClienteController.php?action=listarAjax&q=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => {
            tbody.innerHTML = '';
            if (data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center py-5 text-muted">
                    <i class="fa-solid fa-user-slash fa-3x mb-3 opacity-50"></i><br>
                    Cliente no encontrado.
                </td></tr>`;
                return;
            }

            data.forEach(c => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="ps-4">
                        <div class="fw-bold text-dark">${c.nombre}</div>
                        ${c.observaciones ? '<small class="text-warning"><i class="fa-solid fa-comment-dots"></i> Tiene notas</small>' : ''}
                    </td>
                    <td><span class="badge bg-light text-dark border">${c.documento}</span></td>
                    <td>${c.telefono || '<span class="text-muted">-</span>'}</td>
                    <td>${c.email || '<span class="text-muted">-</span>'}</td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-info text-white me-1" title="Ver Perfil/Historial" onclick="verPerfil(${c.id})"><i class="fa-solid fa-eye"></i></button>
                        <button class="btn btn-sm btn-primary me-1" title="Editar" onclick='abrirModalForm(${JSON.stringify(c).replace(/'/g, "&apos;")})'><i class="fa-solid fa-pen"></i></button>
                        <button class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="eliminarCliente(${c.id})"><i class="fa-solid fa-trash"></i></button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(err => {
            console.error(err);
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-danger">Error de conexión</td></tr>';
        });
}

function abrirModalForm(cliente = null) {
    document.getElementById('form-cliente').reset();
    if (cliente) {
        document.getElementById('modalClienteTitulo').innerText = 'Editar Cliente';
        document.getElementById('cli_id').value = cliente.id;
        document.getElementById('cli_nombre').value = cliente.nombre;
        document.getElementById('cli_documento').value = cliente.documento;
        document.getElementById('cli_telefono').value = cliente.telefono;
        document.getElementById('cli_email').value = cliente.email;
        document.getElementById('cli_direccion').value = cliente.direccion;
        document.getElementById('cli_observaciones').value = cliente.observaciones;
    } else {
        document.getElementById('modalClienteTitulo').innerText = 'Nuevo Cliente';
        document.getElementById('cli_id').value = 0;
    }
    modalForm.show();
}

function guardarCliente() {
    const form = document.getElementById('form-cliente');
    const formData = new FormData(form);
    const btn = document.getElementById('btn-guardar-cli');
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando...';

    fetch('../../controllers/auth/ClienteController.php?action=guardarAjax', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire('¡Éxito!', data.mensaje, 'success');
            modalForm.hide();
            cargarClientes(document.getElementById('input-busqueda').value);
        } else {
            Swal.fire('Error', data.mensaje, 'error');
        }
    })
    .catch(err => console.error(err))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = 'Guardar Cliente';
    });
}

function eliminarCliente(id) {
    Swal.fire({
        title: '¿Eliminar cliente?',
        text: "Si el cliente tiene ventas asociadas, no podrás eliminarlo.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74a3b',
        cancelButtonColor: '#858796',
        confirmButtonText: 'Sí, eliminar'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('id', id);
            
            fetch('../../controllers/auth/ClienteController.php?action=eliminarAjax', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Eliminado', data.mensaje, 'success');
                    cargarClientes(document.getElementById('input-busqueda').value);
                } else {
                    Swal.fire('No se pudo eliminar', data.mensaje, 'error');
                }
            });
        }
    });
}

function verPerfil(id) {
    const tbody = document.getElementById('perfil-historial-tbody');
    tbody.innerHTML = '<tr><td colspan="3" class="text-center py-3"><i class="fa-solid fa-spinner fa-spin"></i> Cargando...</td></tr>';
    
    fetch(`../../controllers/auth/ClienteController.php?action=obtenerPerfilAjax&id=${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const c = data.cliente;
                clienteActualParaNotas = c; // Guardar ref
                
                document.getElementById('perfil-inicial').innerText = c.nombre.charAt(0).toUpperCase();
                document.getElementById('perfil-nombre').innerText = c.nombre;
                document.getElementById('perfil-documento').innerText = c.documento;
                document.getElementById('perfil-telefono').innerText = c.telefono || 'Sin teléfono';
                document.getElementById('perfil-email').innerText = c.email || 'Sin correo';
                document.getElementById('perfil-direccion').innerText = c.direccion || 'Sin dirección';

                // Llenar form oculto de notas
                document.getElementById('nota_cli_id').value = c.id;
                document.getElementById('nota_cli_nombre').value = c.nombre;
                document.getElementById('nota_cli_documento').value = c.documento;
                document.getElementById('nota_cli_telefono').value = c.telefono;
                document.getElementById('nota_cli_email').value = c.email;
                document.getElementById('nota_cli_direccion').value = c.direccion;
                document.getElementById('nota_cli_observaciones').value = c.observaciones || '';

                // Historial
                tbody.innerHTML = '';
                if (c.historial && c.historial.length > 0) {
                    c.historial.forEach(v => {
                        const fmt = new Intl.NumberFormat('es-CO', {style: 'currency', currency: 'COP', maximumFractionDigits: 0}).format(v.total);
                        const fecha = new Date(v.fecha).toLocaleDateString('es-CO');
                        
                        tbody.innerHTML += `
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark" style="font-size: 0.85rem;">${v.codigo_factura}</div>
                                    <small class="text-muted">${v.total_articulos} ítems</small>
                                </td>
                                <td class="text-muted" style="font-size: 0.85rem;">${fecha}</td>
                                <td class="text-end fw-bold text-primary" style="font-size: 0.9rem;">${fmt}</td>
                            </tr>
                        `;
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="3" class="text-center py-4 text-muted small">Este cliente no ha realizado compras.</td></tr>';
                }

                modalPerfil.show();
            } else {
                Swal.fire('Error', data.mensaje, 'error');
            }
        });
}

function guardarNotas() {
    const form = document.getElementById('form-notas-rapidas');
    const formData = new FormData(form);
    const btn = document.getElementById('btn-guardar-notas');
    
    btn.disabled = true;
    btn.innerHTML = 'Guardando...';

    fetch('../../controllers/auth/ClienteController.php?action=guardarAjax', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Notas Actualizadas',
                icon: 'success',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000
            });
            // Recargar tabla de fondo silenciosamente
            cargarClientes(document.getElementById('input-busqueda').value);
        } else {
            Swal.fire('Error', data.mensaje, 'error');
        }
    })
    .catch(err => console.error(err))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = 'Actualizar Notas';
    });
}
</script>

<?php require_once __DIR__ . '/../Layoust/footer.php'; ?>

