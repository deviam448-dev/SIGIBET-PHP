            </main>
            <!-- Fin Main Content Area -->
            
            <footer class="app-footer bg-white border-top p-3 text-center text-muted small">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                    <span class="mb-2 mb-md-0">&copy; <?php echo date('Y'); ?> SIGIBET. Todos los derechos reservados.</span>
                    <span>Versión 1.0 <i class="fa-solid fa-rocket text-primary ms-1"></i></span>
                </div>
            </footer>
        </div> <!-- Fin app-main -->
    </div> <!-- Fin app-container -->

    <!-- ============================================== -->
    <!-- MÓDULO DE AYUDA Y SOPORTE (HU-011 / RF-11)   -->
    <!-- ============================================== -->
    <button class="btn btn-help-float shadow-lg rounded-circle" data-bs-toggle="modal" data-bs-target="#helpCenterModal" title="Centro de Ayuda" style="position: fixed; bottom: 20px; right: 20px; width: 50px; height: 50px; background-color: var(--color-botones); color: white; border: none; z-index: 1000;">
        <i class="fa-solid fa-headset"></i>
    </button>

    <!-- Modal Centro de Ayuda -->
    <div class="modal fade" id="helpCenterModal" tabindex="-1" aria-labelledby="helpCenterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header help-modal-header rounded-top-4 border-0 p-4">
                    <h5 class="modal-title fw-bold" id="helpCenterModalLabel">
                        <i class="fa-solid fa-life-ring me-2 help-accent"></i> Centro de Ayuda y Soporte
                    </h5>
                    <button type="button" class="btn border-0 p-0 help-close" data-bs-dismiss="modal" aria-label="Cerrar">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <style>
                        #helpCenterModal .help-modal-header {
                            background: var(--color-menu);
                            color: var(--color-menu-texto);
                            border-bottom: 3px solid var(--color-acento) !important;
                        }
                        #helpCenterModal .help-accent,
                        #helpCenterModal .help-email-icon {
                            color: var(--color-acento);
                        }
                        #helpCenterModal .help-close {
                            color: var(--color-menu-texto);
                            background: transparent;
                            font-size: 1.5rem;
                            line-height: 1;
                        }
                        #help-tabs .nav-link {
                            background: rgba(var(--color-botones-rgb), 0.08);
                            color: var(--color-secundario);
                            border: 1px solid rgba(var(--color-botones-rgb), 0.2);
                            transition: all 0.2s ease;
                        }
                        #help-tabs .nav-link.active {
                            background: var(--color-botones);
                            color: #fff;
                            border-color: var(--color-botones);
                            box-shadow: 0 8px 18px rgba(var(--color-botones-rgb), 0.22);
                        }
                        #helpCenterModal .help-email-button {
                            background: var(--color-botones);
                            border: 1px solid var(--color-botones);
                            color: #fff;
                            box-shadow: 0 8px 18px rgba(var(--color-botones-rgb), 0.22);
                        }
                        #helpCenterModal .help-email-button:hover,
                        #helpCenterModal .nav-link.active:hover {
                            filter: brightness(0.9);
                            color: #fff;
                        }
                    </style>
                    <ul class="nav nav-pills mb-4 gap-2" id="help-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active rounded-pill fw-bold px-4" id="contact-tab" data-bs-toggle="pill" data-bs-target="#contact-panel" type="button" role="tab">
                                <i class="fa-solid fa-address-book me-1"></i> Contacto y Soporte
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill fw-bold px-4" id="faq-tab" data-bs-toggle="pill" data-bs-target="#faq-panel" type="button" role="tab">
                                <i class="fa-solid fa-circle-question me-1"></i> Preguntas Frecuentes
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="help-tabs-content">
                        <!-- Panel de Contacto -->
                        <div class="tab-pane fade show active" id="contact-panel" role="tabpanel">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="card h-100 border-0 bg-light rounded-4 p-4 text-center shadow-sm">
                                        <div class="display-5 text-success mb-3"><i class="fa-brands fa-whatsapp"></i></div>
                                        <h5 class="fw-bold text-dark">Soporte Técnico</h5>
                                        <p class="text-muted small">Reporta fallas del sistema o problemas con el inventario de inmediato.</p>
                                        <a href="https://wa.me/573000000000" target="_blank" class="btn btn-success rounded-pill mt-auto fw-bold px-4">Chat WhatsApp</a>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100 border-0 bg-light rounded-4 p-4 text-center shadow-sm">
                                        <div class="display-5 mb-3 help-email-icon"><i class="fa-solid fa-envelope"></i></div>
                                        <h5 class="fw-bold text-dark">Contacto Administrativo</h5>
                                        <p class="text-muted small">Solicitudes de creación de nuevos usuarios o modificación de permisos.</p>
                                        <a href="mailto:admin@sigibet.com" class="btn help-email-button rounded-pill mt-auto fw-bold px-4">Enviar Email</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Panel de FAQs -->
                        <div class="tab-pane fade" id="faq-panel" role="tabpanel">
                            <div class="accordion accordion-flush bg-light rounded-4 p-3 shadow-sm" id="accordionFAQ">
                                <div class="accordion-item bg-transparent border-bottom">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed bg-transparent fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                            <i class="fa-solid fa-wifi text-muted me-2"></i> ¿Qué hacer si no encuentro una tela?
                                        </button>
                                    </h2>
                                    <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
                                        <div class="accordion-body text-muted small">
                                            Verifica que el nombre de la tela o la referencia estén correctamente escritos, y asegúrate de que esté marcada como Activa en el catálogo.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Script para Toggle del Sidebar en móviles -->
    <script>
        const toggleBtn = document.getElementById('toggleSidebar');
        const sidebar = document.getElementById('sidebar');
        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('show');
            });
        }
    </script>
    <!-- SweetAlert Global (Flash Messages) - soporta claves 'alerta' y 'alert' -->
    <?php
        // Unificar ambas claves de sesión
        if (!isset($_SESSION['alerta']) && isset($_SESSION['alert'])) {
            $_SESSION['alerta'] = [
                'tipo' => $_SESSION['alert']['icon'] ?? 'info',
                'msg'  => $_SESSION['alert']['text'] ?? ''
            ];
            unset($_SESSION['alert']);
        }
    ?>
    <?php if (isset($_SESSION['alerta'])): 
        $tipo_swal = $_SESSION['alerta']['tipo'] === 'danger' ? 'error' : ($_SESSION['alerta']['tipo'] === 'success' ? 'success' : ($_SESSION['alerta']['tipo'] === 'error' ? 'error' : 'info'));
        $titulo_swal = $tipo_swal === 'error' ? 'Error' : ($tipo_swal === 'success' ? 'Éxito' : 'Aviso');
    ?>
        <style>
            .glass-alert {
                background: rgba(41, 38, 35, 0.85) !important;
                backdrop-filter: blur(12px) !important;
                border: 1px solid rgba(255, 255, 255, 0.1) !important;
                color: #fff !important;
                border-radius: 16px !important;
                box-shadow: 0 10px 30px rgba(0,0,0,0.5) !important;
            }
            .alert-title { color: #fff !important; font-family: 'Inter', sans-serif !important; font-weight: 700 !important; }
            .swal-blur-backdrop { background: rgba(0,0,0,0.4) !important; backdrop-filter: blur(4px) !important; }
            .btn-premium-primary {
                background: linear-gradient(135deg, #B87983, #9E626D) !important;
                border: none !important; border-radius: 50px !important;
                padding: 10px 24px !important; font-weight: 600 !important;
                box-shadow: 0 6px 15px rgba(196, 121, 106, 0.4) !important;
            }
        </style>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: '<?= $tipo_swal ?>',
                    title: '<?= $titulo_swal ?>',
                    text: '<?= htmlspecialchars($_SESSION['alerta']['msg']) ?>',
                    background: 'transparent',
                    color: '#fff',
                    customClass: {
                        popup: 'glass-alert',
                        title: 'alert-title',
                        confirmButton: 'btn-premium-primary',
                        backdrop: 'swal-blur-backdrop'
                    },
                    buttonsStyling: false
                });
            });
        </script>
        <?php unset($_SESSION['alerta']); ?>
    <?php endif; ?>
</body>
</html>