<?php
require_once __DIR__ . '/../../controllers/auth/AuditoriaController.php';
$ac = new AuditoriaController(); 

require_once __DIR__ . '/../Layoust/header.php';
?>

<div class="container-fluid py-2">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0 text-gray-800 fw-bold">
            <i class="fa-solid fa-list-check text-primary-custom me-2"></i> Log de Auditoría
        </h2>
    </div>

    <!-- Filtros -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3">
            <form id="form-filtros" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Módulo</label>
                    <select class="form-select" id="filtro-modulo">
                        <option value="">Todos los módulos</option>
                        <?php foreach($modulos as $mod): ?>
                            <option value="<?php echo htmlspecialchars($mod); ?>"><?php echo htmlspecialchars($mod); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Usuario</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0 bg-light" id="filtro-usuario" placeholder="Nombre o @usuario">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Desde</label>
                    <input type="date" class="form-control" id="filtro-fecha-inicio">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Hasta</label>
                    <input type="date" class="form-control" id="filtro-fecha-fin">
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-secondary fw-bold">Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted">
                        <tr>
                            <th class="ps-4">Fecha y Hora</th>
                            <th>Usuario</th>
                            <th>Módulo</th>
                            <th>Acción</th>
                            <th class="pe-4">Detalles</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-logs">
                        <tr><td colspan="5" class="text-center py-4 text-muted"><i class="fa-solid fa-spinner fa-spin me-2"></i> Cargando historial...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    cargarLogs();

    document.getElementById('form-filtros').addEventListener('submit', function(e) {
        e.preventDefault();
        cargarLogs();
    });
});

function cargarLogs() {
    const tbody = document.getElementById('tabla-logs');
    tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted"><i class="fa-solid fa-spinner fa-spin me-2"></i> Cargando...</td></tr>';

    const modulo = encodeURIComponent(document.getElementById('filtro-modulo').value);
    const usuario = encodeURIComponent(document.getElementById('filtro-usuario').value);
    const fechaInicio = encodeURIComponent(document.getElementById('filtro-fecha-inicio').value);
    const fechaFin = encodeURIComponent(document.getElementById('filtro-fecha-fin').value);

    fetch(`../../controllers/auth/AuditoriaController.php?action=listarAjax&modulo=${modulo}&usuario=${usuario}&fechaInicio=${fechaInicio}&fechaFin=${fechaFin}`)
        .then(res => res.json())
        .then(data => {
            tbody.innerHTML = '';
            
            if (data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center py-5 text-muted">
                    <i class="fa-solid fa-file-circle-xmark fa-3x mb-3 opacity-50"></i><br>
                    No se encontraron registros de auditoría para estos filtros.
                </td></tr>`;
                return;
            }

            data.forEach(log => {
                const tr = document.createElement('tr');
                
                // Formato Fecha
                const f = new Date(log.fecha);
                const fechaStr = f.toLocaleDateString('es-CO') + ' ' + f.toLocaleTimeString('es-CO', {hour: '2-digit', minute:'2-digit'});
                
                // Rol Badge
                const rolBadge = log.rol_nombre === 'Administrador' 
                    ? `<span class="badge bg-warning text-dark me-2" style="font-size:0.65rem">${log.rol_nombre}</span>` 
                    : `<span class="badge bg-secondary me-2" style="font-size:0.65rem">${log.rol_nombre}</span>`;

                tr.innerHTML = `
                    <td class="ps-4 text-muted small">${fechaStr}</td>
                    <td>
                        <div class="fw-bold text-dark">${log.usuario_nombre}</div>
                        ${rolBadge} <small class="text-muted">@${log.username}</small>
                    </td>
                    <td><span class="badge bg-light text-dark border">${log.modulo}</span></td>
                    <td class="fw-bold" style="color: var(--bs-primary);">${log.accion}</td>
                    <td class="pe-4 text-muted small">${log.detalles || '-'}</td>
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(err => {
            console.error(err);
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-danger">Error al cargar el historial.</td></tr>';
        });
}
</script>

<?php require_once __DIR__ . '/../Layoust/footer.php'; ?>

