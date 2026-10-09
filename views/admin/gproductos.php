<?php
// Incluir el header (que automáticamente incluye el topbar y el sidebar correspondiente)
require_once __DIR__ . '/../Layoust/header.php';
require_once __DIR__ . '/../../controllers/auth/productocontroller.php';

$productoController = new ProductoController();
$esAdmin = AuthController::esAdmin();
if ($esAdmin && !isset($_SESSION['csrf_archivar_producto'])) {
    $_SESSION['csrf_archivar_producto'] = bin2hex(random_bytes(32));
}

function obtenerImagenReferencia(string $categoria = '', string $nombre = ''): string
{
    $categoriaNormalizada = strtolower(trim($categoria));
    $nombreNormalizado = strtolower(trim($nombre));

    $mapaImagenes = [
        'moda femenina' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=900&q=80',
        'deportivo' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=900&q=80',
        'hogar' => 'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?auto=format&fit=crop&w=900&q=80',
        'lencería' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=900&q=80',
        'industrial' => 'https://images.unsplash.com/photo-1555529669-26f6d7d5a3d0?auto=format&fit=crop&w=900&q=80',
        'insumos' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=900&q=80',
        'sin categoría' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=900&q=80',
    ];

    if (isset($mapaImagenes[$categoriaNormalizada])) {
        return $mapaImagenes[$categoriaNormalizada];
    }

    if (
        str_contains($nombreNormalizado, 'hilo') ||
        str_contains($nombreNormalizado, 'insumo') ||
        str_contains($nombreNormalizado, 'boton') ||
        str_contains($nombreNormalizado, 'botón') ||
        str_contains($nombreNormalizado, 'aguja') ||
        str_contains($nombreNormalizado, 'cierre') ||
        str_contains($nombreNormalizado, 'elástico') ||
        str_contains($nombreNormalizado, 'entretela') ||
        str_contains($nombreNormalizado, 'acabado') ||
        str_contains($nombreNormalizado, 'tejido') ||
        str_contains($nombreNormalizado, 'tela') ||
        str_contains($nombreNormalizado, 'seda') ||
        str_contains($nombreNormalizado, 'algodón') ||
        str_contains($nombreNormalizado, 'lino') ||
        str_contains($nombreNormalizado, 'denim')
    ) {
        return 'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?auto=format&fit=crop&w=900&q=80';
    }

    return 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=900&q=80';
}

// Manejo de búsqueda
$busqueda = trim($_GET['q'] ?? '');
$productos = $productoController->index($busqueda);

$productosPorCategoria = [];
foreach ($productos as $producto) {
    $categoria = trim((string)($producto['categoria'] ?? ''));
    $claveCategoria = $categoria !== '' ? $categoria : 'Sin categoría';
    $productosPorCategoria[$claveCategoria][] = $producto;
}
ksort($productosPorCategoria, SORT_NATURAL | SORT_FLAG_CASE);

// SweetAlert2 dependencies are assumed to be loaded in header or here
?>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Contenido Específico: Gestión de Productos -->
<div class="container-fluid">
    
    <!-- Encabezado de la página -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <h2 class="h4 mb-0 text-gray-800 fw-bold">
            <i class="fa-solid fa-box-open text-primary-custom me-2"></i> Gestión de Productos
        </h2>
        <button type="button" class="btn btn-primary-custom shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoProducto">
            <i class="fa-solid fa-plus fa-sm text-white-50"></i> Nuevo Producto
        </button>
    </div>

    <!-- Barra de Búsqueda -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="gproductos.php" method="GET" class="d-flex" id="searchForm">
                <div class="input-group">
                    <input type="text" class="form-control bg-light border-0 small" placeholder="Buscar por nombre, referencia o color..." name="q" id="searchInput" value="<?php echo htmlspecialchars($busqueda); ?>" aria-label="Search">
                    <button class="btn btn-primary-custom" type="submit" id="btnSearch">
                        <i class="fas fa-search fa-sm"></i> Buscar
                    </button>
                    <?php if (!empty($busqueda)): ?>
                        <a href="gproductos.php" class="btn btn-outline-secondary">Limpiar</a>
                    <?php endif; ?>
                </div>
            </form>
            <script>
                // Validar búsqueda no vacía
                document.getElementById('searchForm').addEventListener('submit', function(e) {
                    if (document.getElementById('searchInput').value.trim() === '') {
                        e.preventDefault(); // Evitar recarga inútil
                    }
                });
            </script>
        </div>
    </div>

    <!-- Tarjeta Principal: Tabla de Productos -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary-custom">Catálogo de Telas (<?php echo count($productos); ?>)</h6>
        </div>
        <div class="card-body">
            <?php if (count($productos) > 0): ?>
                <?php foreach ($productosPorCategoria as $categoria => $items): ?>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center bg-light rounded px-3 py-2 mb-2 border">
                            <h6 class="mb-0 text-dark fw-bold">
                                <i class="fa-solid fa-tags me-2 text-primary-custom"></i>
                                <?php echo htmlspecialchars($categoria); ?>
                            </h6>
                            <span class="badge bg-primary-subtle text-primary-emphasis rounded-pill"><?php echo count($items); ?> productos</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width: 90px;">Imagen</th>
                                        <th>Ref.</th>
                                        <th>Nombre</th>
                                        <th>Color</th>
                                        <th>Precio Compra</th>
                                        <th>Precio Venta</th>
                                        <?php if ($esAdmin): ?>
                                            <th>Ganancia / Margen</th>
                                        <?php endif; ?>
                                        <th>Stock</th>
                                        <th>Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $p): ?>
                                        <?php $imagenProducto = !empty($p['imagen']) ? '../../public/img/' . $p['imagen'] : obtenerImagenReferencia($p['categoria'] ?? '', $p['nombre'] ?? ''); ?>
                                        <tr>
                                            <td class="text-center">
                                                <img src="<?php echo htmlspecialchars($imagenProducto); ?>"
                                                     alt="Referencia de <?php echo htmlspecialchars($p['nombre']); ?>"
                                                     class="rounded border shadow-sm img-preview-producto"
                                                     data-image="<?php echo htmlspecialchars($imagenProducto); ?>"
                                                     data-title="<?php echo htmlspecialchars($p['nombre']); ?>"
                                                     style="width: 70px; height: 70px; object-fit: cover; background: #f8f9fa; cursor: pointer; transition: transform 0.2s ease;"
                                                     onmouseover="this.style.transform='scale(1.05)'"
                                                     onmouseout="this.style.transform='scale(1)'">
                                            </td>
                                            <td class="fw-bold"><?php echo htmlspecialchars($p['referencia']); ?></td>
                                            <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                                            <td>
                                                <span class="badge bg-light text-dark border"><i class="fa-solid fa-palette me-1"></i><?php echo htmlspecialchars($p['color']); ?></span>
                                            </td>
                                            <td class="text-muted fw-semibold">$<?php echo number_format((float)$p['precio_compra'], 2); ?></td>
                                            <td class="text-success fw-semibold">$<?php echo number_format($p['precio'], 2); ?></td>
                                            <?php if ($esAdmin): ?>
                                                <?php
                                                    $gananciaUnitaria = (float)$p['precio'] - (float)$p['precio_compra'];
                                                    $margenGanancia = (float)$p['precio'] > 0
                                                        ? ($gananciaUnitaria / (float)$p['precio']) * 100
                                                        : 0;
                                                ?>
                                                <td>
                                                    <span class="fw-bold <?php echo $gananciaUnitaria >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                        $<?php echo number_format($gananciaUnitaria, 2); ?>
                                                    </span>
                                                    <small class="d-block text-muted"><?php echo number_format($margenGanancia, 2); ?>% margen</small>
                                                </td>
                                            <?php endif; ?>
                                            <td>
                                                <?php if ($p['stock'] <= $p['stock_minimo']): ?>
                                                    <span class="badge bg-danger rounded-pill"><?php echo $p['stock']; ?> (Bajo)</span>
                                                <?php else: ?>
                                                    <span class="badge bg-info text-dark rounded-pill"><?php echo $p['stock']; ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($p['estado'] == 1): ?>
                                                    <span class="badge bg-success">Activo</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactivo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-info" title="Cambiar imagen"
                                                        onclick='abrirModalEditar(<?php echo json_encode($p); ?>)'>
                                                        <i class="fa-solid fa-camera"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" title="Modificar"
                                                        onclick='abrirModalEditar(<?php echo json_encode($p); ?>)'>
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </button>
                                                    <?php if ($esAdmin && (int)$p['estado'] === 1): ?>
                                                        <form action="../../controllers/auth/productocontroller.php?accion=archivar"
                                                              method="POST" class="d-inline form-archivar-producto">
                                                            <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                                                            <input type="hidden" name="csrf_token"
                                                                   value="<?php echo htmlspecialchars($_SESSION['csrf_archivar_producto'], ENT_QUOTES, 'UTF-8'); ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger btn-archivar"
                                                                    title="Archivar producto" aria-label="Archivar <?php echo htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8'); ?>">
                                                                <i class="fa-solid fa-box-archive"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center text-muted py-5">
                    <i class="fa-solid fa-box-open fa-3x mb-3 text-light"></i>
                    <h5><?php echo !empty($busqueda) ? 'Producto no encontrado' : 'No hay productos registrados aún.'; ?></h5>
                    <p><?php echo !empty($busqueda) ? 'Intenta con otra palabra clave.' : 'Haz clic en "Nuevo Producto" para empezar.'; ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="modalVistaImagen" tabindex="-1" aria-labelledby="modalVistaImagenLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalVistaImagenLabel">Vista de Tela</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-3">
                <img id="modalImagenProducto" src="" alt="Tela" class="img-fluid rounded shadow-sm" style="max-height: 70vh; object-fit: contain; background: #f7f3f1;">
            </div>
        </div>
    </div>
</div>

<!-- Modal Nuevo Producto -->
<div class="modal fade" id="modalNuevoProducto" tabindex="-1" aria-labelledby="modalNuevoProductoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalNuevoProductoLabel"><i class="fa-solid fa-plus me-2"></i>Registrar Nueva Tela</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../../controllers/auth/productocontroller.php?accion=guardar" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Referencia <span class="text-danger">*</span></label>
                            <input type="text" name="referencia" class="form-control" required placeholder="Ej: TELA-001">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control" required placeholder="Ej: Seda Premium">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Color <span class="text-danger">*</span></label>
                            <input type="text" name="color" class="form-control" required placeholder="Ej: Palo de Rosa">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Categoría</label>
                            <input type="text" name="categoria" class="form-control" placeholder="Ej: Moda Femenina">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Precio Venta <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0" name="precio" class="form-control" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Precio Compra</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0" name="precio_compra" class="form-control" value="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cantidad Inicial (Stock) <span class="text-danger">*</span></label>
                            <input type="number" name="stock" class="form-control" min="1" required value="1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Stock Mínimo</label>
                            <input type="number" name="stock_minimo" class="form-control" min="0" value="5">
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Imagen del producto</label>
                            <input type="file" name="imagen" class="form-control" accept="image/jpeg,image/png,image/webp">
                            <small class="text-muted">JPG, PNG o WEBP, máximo 5 MB.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="2" placeholder="Detalles de la tela..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-custom"><i class="fa-solid fa-save me-1"></i>Guardar Producto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Producto -->
<div class="modal fade" id="modalEditarProducto" tabindex="-1" aria-labelledby="modalEditarProductoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="modalEditarProductoLabel"><i class="fa-solid fa-pen-to-square me-2"></i>Modificar Tela</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../../controllers/auth/productocontroller.php?accion=actualizar" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id_producto" id="edit_id">
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Referencia <span class="text-danger">*</span></label>
                            <input type="text" name="referencia" id="edit_referencia" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="edit_nombre" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Color <span class="text-danger">*</span></label>
                            <input type="text" name="color" id="edit_color" class="form-control" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Categoría</label>
                            <input type="text" name="categoria" id="edit_categoria" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Precio Venta <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0" name="precio" id="edit_precio" class="form-control" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Precio Compra</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0" name="precio_compra" id="edit_precio_compra" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cantidad (Stock) <span class="text-danger">*</span></label>
                            <input type="number" name="stock" id="edit_stock" class="form-control" min="1" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Estado</label>
                            <select name="estado" id="edit_estado" class="form-select">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Cambiar imagen</label>
                            <input type="file" name="imagen" class="form-control" accept="image/jpeg,image/png,image/webp">
                            <small class="text-muted">Selecciona una nueva imagen para reemplazar la actual.</small>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="eliminar_imagen" id="edit_eliminar_imagen">
                                <label class="form-check-label" for="edit_eliminar_imagen">Eliminar imagen personalizada</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" id="edit_descripcion" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-custom"><i class="fa-solid fa-sync me-1"></i>Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Script para abrir modal de edición y llenar datos
function abrirModalEditar(producto) {
    document.getElementById('edit_id').value = producto.id;
    document.getElementById('edit_referencia').value = producto.referencia;
    document.getElementById('edit_nombre').value = producto.nombre;
    document.getElementById('edit_color').value = producto.color;
    document.getElementById('edit_categoria').value = producto.categoria;
    document.getElementById('edit_precio').value = producto.precio;
    document.getElementById('edit_precio_compra').value = producto.precio_compra;
    document.getElementById('edit_stock').value = producto.stock;
    document.getElementById('edit_estado').value = producto.estado;
    document.getElementById('edit_descripcion').value = producto.descripcion;
    document.getElementById('edit_eliminar_imagen').checked = false;
    
    var modal = new bootstrap.Modal(document.getElementById('modalEditarProducto'));
    modal.show();
}

function abrirVistaImagen(url, title) {
    const img = document.getElementById('modalImagenProducto');
    const label = document.getElementById('modalVistaImagenLabel');
    if (img) {
        img.src = url;
        img.alt = title;
    }
    if (label) {
        label.textContent = title;
    }

    const modal = new bootstrap.Modal(document.getElementById('modalVistaImagen'));
    modal.show();
}

// SweetAlert2 Alerts
document.addEventListener('DOMContentLoaded', function() {
    <?php if (isset($_SESSION['alert'])): ?>
        Swal.fire({
            icon: '<?php echo $_SESSION['alert']['icon']; ?>',
            title: '<?php echo $_SESSION['alert']['title']; ?>',
            text: '<?php echo $_SESSION['alert']['text']; ?>',
            confirmButtonColor: '#b56576'
        });
        <?php unset($_SESSION['alert']); ?>
    <?php endif; ?>

    document.querySelectorAll('.img-preview-producto').forEach(img => {
        img.addEventListener('click', function() {
            abrirVistaImagen(this.dataset.image, this.dataset.title || 'Tela');
        });
    });

    document.querySelectorAll('.form-archivar-producto').forEach(form => {
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: '¿Archivar producto?',
                text: 'Se ocultará del catálogo público, pero conservará su stock y el historial de ventas.',
                showCancelButton: true,
                confirmButtonColor: '#A85F6D',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, archivar',
                cancelButtonText: 'Cancelar'
            }).then(result => {
                if (result.isConfirmed) {
                    HTMLFormElement.prototype.submit.call(form);
                }
            });
        });
    });
});
</script>

<?php
// Incluir el footer (que cierra las etiquetas <main>, <div> y <body> abiertas en el header)
require_once __DIR__ . '/../Layoust/footer.php';
?>
