<?php
require_once __DIR__ . '/../../controllers/auth/BackupController.php';
$bc = new BackupController(); 

require_once __DIR__ . '/../Layoust/header.php';
?>

<div class="container-fluid py-2">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0 text-gray-800 fw-bold">
            <i class="fa-solid fa-database text-primary-custom me-2"></i> Respaldo y Restauración
        </h2>
    </div>

    <div class="row g-4">
        <!-- Generar Respaldo -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <i class="fa-solid fa-cloud-arrow-down fa-4x text-success opacity-75"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Generar Copia de Seguridad</h4>
                    <p class="text-muted mb-4">
                        Descarga un archivo <code>.sql</code> con toda la información actual del sistema: productos, inventario, ventas, clientes, usuarios y configuración.
                    </p>
                    <a href="../../controllers/auth/BackupController.php?action=descargarAjax" class="btn btn-success btn-lg fw-bold px-5 rounded-pill shadow-sm">
                        <i class="fa-solid fa-download me-2"></i> Descargar Respaldo
                    </a>
                </div>
            </div>
        </div>

        <!-- Restaurar Respaldo -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <i class="fa-solid fa-cloud-arrow-up fa-4x text-danger opacity-75"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Restaurar Información</h4>
                    <p class="text-muted mb-4">
                        Sube un archivo de respaldo <code>.sql</code> previamente generado por este sistema para restaurar todos los datos.
                    </p>
                    <button type="button" class="btn btn-danger btn-lg fw-bold px-5 rounded-pill shadow-sm" onclick="abrirModalRestauracion()">
                        <i class="fa-solid fa-upload me-2"></i> Subir Respaldo
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Restauración Extrema -->
<div class="modal fade" id="modalRestauracion" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-danger border-2 shadow-lg">
            <div class="modal-header bg-danger text-white border-0 rounded-top-3">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-triangle-exclamation me-2"></i> ¡Advertencia Crítica!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-restaurar">
                <div class="modal-body p-4 text-center">
                    <p class="mb-4">
                        Estás a punto de <strong>SOBRESCRIBIR TODA LA BASE DE DATOS</strong>.<br>
                        Cualquier venta, producto o cliente nuevo que no esté en el archivo de respaldo <strong>se perderá para siempre</strong>.
                    </p>
                    
                    <div class="mb-4 text-start">
                        <label class="form-label fw-bold text-muted small">Selecciona el archivo .sql</label>
                        <input type="file" class="form-control" id="archivo_sql" name="archivo_sql" accept=".sql" required>
                    </div>

                    <div class="alert alert-warning border-warning border-2 p-3 text-start">
                        <label class="form-label fw-bold small text-dark mb-1">Escribe "CONFIRMAR" en mayúsculas para habilitar el botón:</label>
                        <input type="text" class="form-control fw-bold text-center" id="input-confirmar" autocomplete="off" placeholder="Escribe aquí..." required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0 justify-content-between">
                    <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger fw-bold px-4 disabled" id="btn-procesar-restauracion">
                        <i class="fa-solid fa-fire me-2"></i> Ejecutar Restauración
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let modalRestauracion;

document.addEventListener("DOMContentLoaded", function() {
    modalRestauracion = new bootstrap.Modal(document.getElementById('modalRestauracion'));

    const inputConfirmar = document.getElementById('input-confirmar');
    const btnProcesar = document.getElementById('btn-procesar-restauracion');

    inputConfirmar.addEventListener('input', function() {
        if (this.value === 'CONFIRMAR') {
            btnProcesar.classList.remove('disabled');
        } else {
            btnProcesar.classList.add('disabled');
        }
    });

    document.getElementById('form-restaurar').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (inputConfirmar.value !== 'CONFIRMAR') return;

        const formData = new FormData(this);
        
        btnProcesar.disabled = true;
        btnProcesar.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Restaurando... No cierres esta ventana';

        fetch('../../controllers/auth/BackupController.php?action=restaurarAjax', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: '¡Restauración Exitosa!',
                    text: 'La base de datos ha sido reemplazada. Por seguridad, debes iniciar sesión de nuevo.',
                    icon: 'success',
                    allowOutsideClick: false
                }).then(() => {
                    window.location.href = '../../views/auth/login.php';
                });
            } else {
                Swal.fire('Error Grave', data.mensaje, 'error');
                btnProcesar.disabled = false;
                btnProcesar.innerHTML = '<i class="fa-solid fa-fire me-2"></i> Ejecutar Restauración';
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Error', 'Fallo en la conexión durante la restauración.', 'error');
            btnProcesar.disabled = false;
            btnProcesar.innerHTML = '<i class="fa-solid fa-fire me-2"></i> Ejecutar Restauración';
        });
    });
});

function abrirModalRestauracion() {
    document.getElementById('form-restaurar').reset();
    document.getElementById('btn-procesar-restauracion').classList.add('disabled');
    modalRestauracion.show();
}
</script>

<?php require_once __DIR__ . '/../Layoust/footer.php'; ?>

