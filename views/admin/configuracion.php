<?php
require_once __DIR__ . '/../../controllers/auth/ConfiguracionController.php';
require_once __DIR__ . '/../../models/Configuracion.php';
$cc = new ConfiguracionController();
$config = (new Configuracion())->obtenerConfiguracion();

require_once __DIR__ . '/../Layoust/header.php';
?>

<div class="container-fluid py-2">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0 text-gray-800 fw-bold">
            <i class="fa-solid fa-laptop-code text-primary-custom me-2"></i> Configuración del Sistema
        </h2>
    </div>

    <form id="form-configuracion">
        <div class="row g-4">
            <!-- Columna Izquierda: Datos del Negocio -->
            <div class="col-lg-7">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                        <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-building me-2 text-primary-custom"></i> Datos de la Empresa</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted small">Nombre del Negocio <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-light" id="conf_nombre" name="nombre_empresa" value="<?php echo htmlspecialchars($config['nombre_empresa'] ?? ''); ?>" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted small">NIT / Documento</label>
                                <input type="text" class="form-control bg-light" id="conf_nit" name="nit" value="<?php echo htmlspecialchars($config['nit'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted small">Teléfono</label>
                                <input type="text" class="form-control bg-light" id="conf_telefono" name="telefono" value="<?php echo htmlspecialchars($config['telefono'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted small">Correo Electrónico</label>
                            <input type="email" class="form-control bg-light" id="conf_email" name="email" value="<?php echo htmlspecialchars($config['email'] ?? ''); ?>">
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-bold text-muted small">Dirección Comercial</label>
                            <input type="text" class="form-control bg-light" id="conf_direccion" name="direccion" value="<?php echo htmlspecialchars($config['direccion'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Finanzas y Apariencia -->
            <div class="col-lg-5">
                <div class="row g-4">
                    <!-- Facturación -->
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                                <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-file-invoice-dollar me-2 text-success"></i> Reglas de Facturación</h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-muted small">Impuesto / IVA (%)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control bg-light" id="conf_impuesto" name="impuesto" step="0.01" min="0" max="100" value="<?php echo htmlspecialchars($config['impuesto'] ?? '19'); ?>">
                                        <span class="input-group-text bg-light">%</span>
                                    </div>
                                    <div class="form-text small">Predeterminado: 19%. Se suma al subtotal de la venta y también aparece en la cotización.</div>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label fw-bold text-muted small">Tipo de Régimen</label>
                                    <select class="form-select bg-light" id="conf_tipo_facturacion" name="tipo_facturacion">
                                        <option value="Régimen Simplificado" <?php echo (($config['tipo_facturacion'] ?? '') == 'Régimen Simplificado') ? 'selected' : ''; ?>>Régimen Simplificado</option>
                                        <option value="Régimen Común" <?php echo (($config['tipo_facturacion'] ?? '') == 'Régimen Común') ? 'selected' : ''; ?>>Régimen Común</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Apariencia -->
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                                <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-palette me-2" style="color: var(--bs-purple);"></i> Apariencia y Tema</h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-4">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold text-muted small" for="conf_color_botones">Color de botones</label>
                                        <input type="color" class="form-control form-control-color border-0 p-1 shadow-sm" id="conf_color_botones" name="color_botones" value="<?php echo htmlspecialchars($config['color_botones'] ?? '#A85F6D'); ?>" title="Elegir color de botones">
                                        <div class="form-text small">Botones y acciones principales.</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold text-muted small" for="conf_color_menu">Color de menús</label>
                                        <input type="color" class="form-control form-control-color border-0 p-1 shadow-sm" id="conf_color_menu" name="color_menu" value="<?php echo htmlspecialchars($config['color_menu'] ?? '#1C191B'); ?>" title="Elegir color de menús">
                                        <div class="form-text small">Fondo de la barra lateral.</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold text-muted small" for="conf_color_acento">Color de acentos</label>
                                        <input type="color" class="form-control form-control-color border-0 p-1 shadow-sm" id="conf_color_acento" name="color_acento" value="<?php echo htmlspecialchars($config['color_acento'] ?? '#B9BF66'); ?>" title="Elegir color de acentos">
                                        <div class="form-text small">Resaltes, bordes e indicadores activos.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-muted small" for="conf_tipografia">Tipo de letra</label>
                                        <select class="form-select bg-light" id="conf_tipografia" name="tipografia">
                                            <?php foreach (['Inter', 'Arial', 'Verdana', 'Georgia', 'Trebuchet MS'] as $opcionTipografia): ?>
                                                <option value="<?php echo htmlspecialchars($opcionTipografia); ?>" <?php echo (($config['tipografia_nombre'] ?? 'Inter') === $opcionTipografia) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($opcionTipografia); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text small">Se aplica a todo el panel y al inicio de sesión.</div>
                                    </div>
                                    <div class="col-12">
                                        <div id="estado-apariencia" class="small text-muted" role="status" aria-live="polite">
                                            Los colores y la tipografía se guardan automáticamente al elegirlos.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Botón Guardar -->
            <div class="col-12 mt-4 text-end">
                <hr class="border-light opacity-50 mb-4">
                <button type="submit" class="btn btn-primary btn-lg fw-bold px-5 rounded-pill shadow-sm" id="btn-guardar-config">
                    <i class="fa-solid fa-save me-2"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const controlesApariencia = [
        document.getElementById('conf_color_botones'),
        document.getElementById('conf_color_menu'),
        document.getElementById('conf_color_acento'),
        document.getElementById('conf_tipografia')
    ];
    const estadoApariencia = document.getElementById('estado-apariencia');
    let temporizadorApariencia = null;
    let guardandoApariencia = false;
    let aparienciaPendiente = false;

    function aplicarVistaPreviaApariencia() {
        const raiz = document.documentElement;
        const colorBotones = document.getElementById('conf_color_botones').value;
        const colorMenu = document.getElementById('conf_color_menu').value;
        const colorAcento = document.getElementById('conf_color_acento').value;
        const fuentes = {
            'Inter': "'Inter', 'Segoe UI', sans-serif",
            'Arial': 'Arial, Helvetica, sans-serif',
            'Verdana': 'Verdana, Geneva, sans-serif',
            'Georgia': "Georgia, 'Times New Roman', serif",
            'Trebuchet MS': "'Trebuchet MS', sans-serif"
        };
        const rgbBotones = colorBotones.match(/[A-Fa-f0-9]{2}/g).map(par => parseInt(par, 16)).join(', ');
        const rgbMenu = colorMenu.match(/[A-Fa-f0-9]{2}/g).map(par => parseInt(par, 16));
        const colorTextoMenu = rgbMenu.reduce((suma, canal) => suma + canal, 0) / 3 < 140 ? '#FFFFFF' : '#1C191B';

        raiz.style.setProperty('--color-secundario', colorBotones);
        raiz.style.setProperty('--color-botones', colorBotones);
        raiz.style.setProperty('--color-botones-rgb', rgbBotones);
        raiz.style.setProperty('--bs-primary', colorBotones);
        raiz.style.setProperty('--bs-primary-rgb', rgbBotones);
        raiz.style.setProperty('--color-menu', colorMenu);
        raiz.style.setProperty('--color-primario', colorMenu);
        raiz.style.setProperty('--color-menu-texto', colorTextoMenu);
        raiz.style.setProperty('--color-acento', colorAcento);
        raiz.style.setProperty('--font-family-base', fuentes[document.getElementById('conf_tipografia').value]);
    }

    function guardarApariencia() {
        aparienciaPendiente = true;
        estadoApariencia.textContent = 'Guardando apariencia...';
        if (guardandoApariencia) return;
        guardandoApariencia = true;

        const enviarCambios = () => {
            aparienciaPendiente = false;
            const datos = new FormData();
            controlesApariencia.forEach(control => datos.append(control.name, control.value));

            fetch('../../controllers/auth/ConfiguracionController.php?action=guardarAparienciaAjax', {
                method: 'POST',
                body: datos
            })
            .then(res => res.json().then(data => {
                if (!res.ok || !data.success) throw new Error(data.mensaje || 'No se pudo guardar la apariencia.');
                estadoApariencia.textContent = 'Apariencia guardada.';
            }))
            .catch(error => {
                estadoApariencia.textContent = error.message;
                estadoApariencia.classList.add('text-danger');
            })
            .finally(() => {
                if (aparienciaPendiente) {
                    enviarCambios();
                } else {
                    guardandoApariencia = false;
                }
            });
        };

        clearTimeout(temporizadorApariencia);
        temporizadorApariencia = setTimeout(enviarCambios, 250);
    }

    controlesApariencia.forEach(control => {
        control.addEventListener('input', aplicarVistaPreviaApariencia);
        control.addEventListener('change', () => {
            estadoApariencia.classList.remove('text-danger');
            guardarApariencia();
        });
    });

    document.getElementById('form-configuracion').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const btn = document.getElementById('btn-guardar-config');
        const formData = new FormData(this);
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Guardando...';

        fetch('../../controllers/auth/ConfiguracionController.php?action=guardarAjax', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: '¡Ajustes Guardados!',
                    text: 'La configuración se actualizó correctamente.',
                    icon: 'success',
                    confirmButtonText: 'Refrescar para ver cambios'
                }).then(() => {
                    // Refrescar para aplicar los cambios de color CSS en el layout
                    window.location.reload();
                });
            } else {
                Swal.fire('Error', data.mensaje, 'error');
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Error', 'Problema de conexión al guardar.', 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-save me-2"></i> Guardar Cambios';
        });
    });
});
</script>

<?php require_once __DIR__ . '/../Layoust/footer.php'; ?>
