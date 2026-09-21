<?php
// Protegemos la vista en caso de que alguien intente acceder a la URL manualmente
require_once __DIR__ . '/../../controllers/auth/UsuarioController.php';
$uc = new UsuarioController(); // Esto ejecutará la validación de rol del constructor y redirigirá si no es admin

require_once __DIR__ . '/../Layoust/header.php';
?>

<div class="container-fluid py-2">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0 text-gray-800 fw-bold">
            <i class="fa-solid fa-user-shield text-primary-custom me-2"></i> Gestión de Usuarios
        </h2>
        <button class="btn btn-primary fw-bold" onclick="abrirModalForm()">
            <i class="fa-solid fa-user-plus me-1"></i> Nuevo Usuario
        </button>
    </div>

    <!-- Alerta de política HU-002: límite 2 usuarios por rol -->
    <div class="row mb-4 g-3">
        <!-- Contador Administradores -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #F4C430 !important; border-radius: 12px;">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0"
                         style="width:48px;height:48px;background:#F4C430;font-size:1.3rem;">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="small text-muted fw-bold text-uppercase" style="letter-spacing:.5px;">Administradores activos</div>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <span class="fw-bold fs-5" id="count-admin">—</span>
                            <span class="text-muted small">/ 2 permitidos</span>
                            <span id="badge-admin"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Contador Empleados -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #B87983 !important; border-radius: 12px;">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0"
                         style="width:48px;height:48px;background:#B87983;font-size:1.3rem;">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="small text-muted fw-bold text-uppercase" style="letter-spacing:.5px;">Empleados activos</div>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <span class="fw-bold fs-5" id="count-empleado">—</span>
                            <span class="text-muted small">/ 2 permitidos</span>
                            <span id="badge-empleado"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="alert border-0 shadow-sm mb-4 d-flex align-items-center gap-2"
         style="background:rgba(184,121,131,0.1); color:#62483F; border-radius:10px;">
        <i class="fa-solid fa-circle-info fa-lg" style="color:#B87983;"></i>
        <span>
            <strong>Política de seguridad (HU-002):</strong>
            El sistema permite máximo <strong>2 usuarios activos por rol</strong>.
            Si un rol está lleno, primero desactiva un usuario existente antes de registrar uno nuevo.
        </span>
    </div>

    <!-- Tabla de Usuarios -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted">
                        <tr>
                            <th class="ps-4">Usuario</th>
                            <th>Nombre</th>
                            <th>Rol</th>
                            <th>Creación</th>
                            <th class="text-center">Estado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-usuarios">
                        <tr><td colspan="6" class="text-center py-4 text-muted"><i class="fa-solid fa-spinner fa-spin me-2"></i> Cargando usuarios...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Formulario Usuario -->
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header text-white rounded-top-4 border-0" style="background: linear-gradient(135deg, var(--bs-primary), #2C87B8);">
                <h5 class="modal-title fw-bold" id="modalUsuarioTitulo">Nuevo Usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-usuario">
                <div class="modal-body p-4">
                    <input type="hidden" id="usr_id" name="id" value="0">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Nombre Completo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="usr_nombre" name="nombre" required>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Nombre de Usuario <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="usr_usuario" name="usuario" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Teléfono</label>
                            <input type="text" class="form-control" id="usr_telefono" name="telefono">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Correo Electrónico</label>
                        <input type="email" class="form-control" id="usr_email" name="email">
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Rol <span class="text-danger">*</span></label>
                            <select class="form-select" id="usr_rol_id" name="rol_id" required>
                                <option value="2">Empleado</option>
                                <option value="1">Administrador</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Estado <span class="text-danger">*</span></label>
                            <select class="form-select" id="usr_estado" name="estado" required>
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold text-muted small">Contraseña <span class="text-danger" id="req-password">*</span></label>
                        <input type="password" class="form-control" id="usr_password" name="password" placeholder="Dejar en blanco para mantener la actual en edición">
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4" id="btn-guardar-usr">Guardar Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let modalForm;

document.addEventListener("DOMContentLoaded", function() {
    modalForm = new bootstrap.Modal(document.getElementById('modalUsuario'));
    cargarUsuarios();

    document.getElementById('form-usuario').addEventListener('submit', function(e) {
        e.preventDefault();
        guardarUsuario();
    });
});

function cargarUsuarios() {
    const tbody = document.getElementById('tabla-usuarios');
    
    fetch('../../controllers/auth/UsuarioController.php?action=listarAjax')
        .then(res => res.json())
        .then(data => {
            tbody.innerHTML = '';
            
            // Contar activos por rol para los contadores de HU-002
            let totalAdmin    = data.filter(u => u.rol_id == 1 && u.estado == 1).length;
            let totalEmpleado = data.filter(u => u.rol_id == 2 && u.estado == 1).length;
            actualizarContadores(totalAdmin, totalEmpleado);
            
            data.forEach(u => {
                const tr = document.createElement('tr');
                let estadoBadge = u.estado == 1 
                    ? '<span class="badge bg-success bg-opacity-10 text-success border border-success"><i class="fa-solid fa-circle me-1" style="font-size:0.5rem"></i> Activo</span>'
                    : '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger"><i class="fa-solid fa-ban me-1" style="font-size:0.5rem"></i> Inactivo</span>';
                let rolBadge = u.rol_id == 1 
                    ? `<span class="badge" style="background-color: var(--bs-warning); color: #000;">${u.nombre_rol}</span>`
                    : `<span class="badge bg-secondary">${u.nombre_rol}</span>`;

                tr.innerHTML = `
                    <td class="ps-4 fw-bold text-dark">@${u.usuario}</td>
                    <td>
                        <div>${u.nombre}</div>
                        <small class="text-muted">${u.email || 'Sin correo'}</small>
                    </td>
                    <td>${rolBadge}</td>
                    <td class="text-muted small">${new Date(u.fecha_creacion).toLocaleDateString('es-CO')}</td>
                    <td class="text-center">
                        <div class="form-check form-switch d-inline-block">
                            <input class="form-check-input cursor-pointer" type="checkbox" role="switch" 
                                ${u.estado == 1 ? 'checked' : ''} 
                                onchange="cambiarEstado(${u.id}, this.checked)"
                                title="Cambiar estado">
                        </div>
                    </td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-primary" title="Editar" onclick='abrirModalForm(${JSON.stringify(u).replace(/'/g, "&apos;")})'>
                            <i class="fa-solid fa-pen"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(err => {
            console.error(err);
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-danger">Error al cargar usuarios</td></tr>';
        });
}

function actualizarContadores(totalAdmin, totalEmpleado) {
    const countAdmin    = document.getElementById('count-admin');
    const countEmpleado = document.getElementById('count-empleado');
    const badgeAdmin    = document.getElementById('badge-admin');
    const badgeEmpleado = document.getElementById('badge-empleado');

    countAdmin.innerText    = totalAdmin;
    countEmpleado.innerText = totalEmpleado;

    // Semáforo visual: verde = disponible, rojo = lleno
    if (totalAdmin >= 2) {
        countAdmin.style.color = '#E85D5D';
        badgeAdmin.innerHTML   = '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger small">Límite alcanzado</span>';
    } else {
        countAdmin.style.color = '#198754';
        badgeAdmin.innerHTML   = `<span class="badge bg-success bg-opacity-10 text-success border border-success small">${2 - totalAdmin} disponible${(2-totalAdmin)!==1?'s':''}</span>`;
    }
    if (totalEmpleado >= 2) {
        countEmpleado.style.color = '#E85D5D';
        badgeEmpleado.innerHTML   = '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger small">Límite alcanzado</span>';
    } else {
        countEmpleado.style.color = '#198754';
        badgeEmpleado.innerHTML   = `<span class="badge bg-success bg-opacity-10 text-success border border-success small">${2 - totalEmpleado} disponible${(2-totalEmpleado)!==1?'s':''}</span>`;
    }
}

function abrirModalForm(usuario = null) {
    document.getElementById('form-usuario').reset();
    const reqPassword = document.getElementById('req-password');
    const inputPassword = document.getElementById('usr_password');

    if (usuario) {
        document.getElementById('modalUsuarioTitulo').innerText = 'Editar Usuario';
        document.getElementById('usr_id').value = usuario.id;
        document.getElementById('usr_nombre').value = usuario.nombre;
        document.getElementById('usr_usuario').value = usuario.usuario;
        document.getElementById('usr_telefono').value = usuario.telefono;
        document.getElementById('usr_email').value = usuario.email;
        document.getElementById('usr_rol_id').value = usuario.rol_id;
        document.getElementById('usr_estado').value = usuario.estado;
        
        reqPassword.style.display = 'none'; // Contraseña opcional al editar
        inputPassword.removeAttribute('required');
    } else {
        document.getElementById('modalUsuarioTitulo').innerText = 'Nuevo Usuario';
        document.getElementById('usr_id').value = 0;
        document.getElementById('usr_estado').value = 1;
        
        reqPassword.style.display = 'inline'; // Contraseña obligatoria al crear
        inputPassword.setAttribute('required', 'true');
    }
    modalForm.show();
}

function guardarUsuario() {
    const form = document.getElementById('form-usuario');
    const formData = new FormData(form);
    const btn = document.getElementById('btn-guardar-usr');
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando...';

    fetch('../../controllers/auth/UsuarioController.php?action=guardarAjax', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Registro exitoso!',
                text: data.mensaje,
                confirmButtonColor: '#B87983'
            });
            modalForm.hide();
            cargarUsuarios();
        } else {
            // Detectar si es por límite de rol para mensaje más claro (HU-002)
            const esLimite = data.mensaje && data.mensaje.toLowerCase().includes('2 usuarios');
            Swal.fire({
                icon: esLimite ? 'warning' : 'error',
                title: esLimite ? 'Límite de usuarios alcanzado' : 'Atención',
                text: data.mensaje,
                footer: esLimite
                    ? '<small>Desactiva un usuario del mismo rol antes de registrar uno nuevo.</small>'
                    : '',
                confirmButtonColor: '#B87983'
            });
        }
    })
    .catch(err => console.error(err))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = 'Guardar Usuario';
    });
}

function cambiarEstado(id, activo) {
    const nuevoEstado = activo ? 1 : 0;
    const formData = new FormData();
    formData.append('id', id);
    formData.append('estado', nuevoEstado);

    fetch('../../controllers/auth/UsuarioController.php?action=cambiarEstadoAjax', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Estado Actualizado',
                icon: 'success',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000
            });
        } else {
            Swal.fire('Error', data.mensaje, 'error');
            cargarUsuarios(); // Restaurar el switch visualmente
        }
    })
    .catch(err => {
        console.error(err);
        cargarUsuarios();
    });
}
</script>

<?php require_once __DIR__ . '/../Layoust/footer.php'; ?>

