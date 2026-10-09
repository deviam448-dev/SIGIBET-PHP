<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../models/Producto.php';
require_once __DIR__ . '/../../models/Movimiento.php';
require_once __DIR__ . '/../../models/Auditoria.php';
require_once __DIR__ . '/AuthController.php';

/**
 * CONTROLADOR DE PRODUCTOS
 */
class ProductoController
{
    private Producto $modelo;

    public function __construct()
    {
        if (!isset($_SESSION['usuario'])) {
            header('Location: ../../views/Auth/login.php');
            exit;
        }

        $this->modelo = new Producto();
    }

    /**
     * Obtiene los productos, opcionalmente filtrados por búsqueda.
     */
    public function index($busqueda = '')
    {
        $todos = $this->modelo->obtenerTodos();
        
        if (empty(trim($busqueda))) {
            return $todos;
        }

        $busqueda = strtolower(trim($busqueda));
        $filtrados = array_filter($todos, function($p) use ($busqueda) {
            return str_contains(strtolower($p['nombre']), $busqueda) || 
                   str_contains(strtolower($p['referencia']), $busqueda) || 
                   str_contains(strtolower($p['color']), $busqueda);
        });

        return $filtrados;
    }

    /**
     * Maneja la creación de un nuevo producto.
     */
    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $referencia = trim($_POST['referencia'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $color = trim($_POST['color'] ?? '');
        $categoria = trim($_POST['categoria'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio = floatval($_POST['precio'] ?? 0);
        $precio_compra = floatval($_POST['precio_compra'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0);
        $stock_minimo = intval($_POST['stock_minimo'] ?? 5);
        $imagen = $this->guardarImagenSubida($_FILES['imagen'] ?? null);

        // Validaciones estrictas
        if (empty($referencia) || empty($nombre) || empty($color)) {
            $this->setAlert('warning', 'Campos incompletos', 'Referencia, Nombre y Color son obligatorios.');
            $this->redirect();
        }

        if ($precio < 0 || $precio_compra < 0) {
            $this->setAlert('error', 'Valor inválido', 'Los precios no pueden ser números negativos.');
            $this->redirect();
        }

        if ($stock < 1) {
            $this->setAlert('error', 'Cantidad inválida', 'La cantidad (stock) inicial debe ser de al menos 1.');
            $this->redirect();
        }

        // Validación de duplicados
        if ($this->modelo->referenciaExiste($referencia)) {
            $this->setAlert('error', 'Referencia duplicada', 'Ya existe un producto registrado con la referencia: ' . htmlspecialchars($referencia));
            $this->redirect();
        }

        // Guardar (ahora retorna el ID insertado)
        $id_nuevo_producto = $this->modelo->registrar($referencia, $nombre, $color, $categoria, $descripcion, $precio, $precio_compra, $stock, $stock_minimo, 1, $imagen);

        if ($id_nuevo_producto) {
            // Registrar auditoria
            Auditoria::registrar('Productos', 'Crear Producto', "Referencia: $referencia");

            // Registrar movimiento inicial de inventario
            $usuario_id = (int)($_SESSION['usuario']['id_usuario'] ?? 0);
            if ($usuario_id <= 0) {
                $this->setAlert('error', 'Sesión inválida', 'No se pudo identificar al usuario que registra el movimiento.');
                $this->redirect();
            }
            $modeloMovimiento = new Movimiento();
            $modeloMovimiento->registrar($id_nuevo_producto, $usuario_id, 'CREACION', $stock, 0, $stock, 'Inventario inicial al crear el producto');

            $this->setAlert('success', '¡Éxito!', 'Producto registrado correctamente.');
        } else {
            $this->setAlert('error', 'Error', 'Ocurrió un problema al guardar en la base de datos.');
        }
        $this->redirect();
    }

    /**
     * Maneja la actualización de un producto existente.
     */
    public function actualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $id = intval($_POST['id_producto'] ?? 0);
        $referencia = trim($_POST['referencia'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $color = trim($_POST['color'] ?? '');
        $categoria = trim($_POST['categoria'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio = floatval($_POST['precio'] ?? 0);
        $precio_compra = floatval($_POST['precio_compra'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0); // En actualización, podemos validar si quieren ajustar el stock directo
        $stock_minimo = intval($_POST['stock_minimo'] ?? 5);
        $estado = intval($_POST['estado'] ?? 1);
        $imagen = $this->guardarImagenSubida($_FILES['imagen'] ?? null);
        $eliminarImagen = isset($_POST['eliminar_imagen']);

        if ($id <= 0) {
            $this->setAlert('error', 'Error crítico', 'No se identificó el producto a actualizar.');
            $this->redirect();
        }

        if ($precio < 0 || $precio_compra < 0) {
            $this->setAlert('error', 'Valor inválido', 'Los precios no pueden ser números negativos.');
            $this->redirect();
        }

        if ($stock < 1) {
            $this->setAlert('error', 'Cantidad inválida', 'La cantidad (stock) debe ser mayor a 0 al modificar.');
            $this->redirect();
        }

        // Verificamos si la referencia cambió y si ya está en uso
        $producto_actual = $this->modelo->obtenerPorId($id);
        if ($producto_actual && $producto_actual['referencia'] !== $referencia) {
            if ($this->modelo->referenciaExiste($referencia)) {
                $this->setAlert('error', 'Referencia duplicada', 'La nueva referencia ya está en uso por otro producto.');
                $this->redirect();
            }
        }

        if ($eliminarImagen || $imagen !== null) {
            $imagenAnterior = $producto_actual['imagen'] ?? null;
            if ($imagenAnterior && str_starts_with($imagenAnterior, 'productos/')) {
                $rutaAnterior = __DIR__ . '/../../public/img/' . $imagenAnterior;
                if (is_file($rutaAnterior)) unlink($rutaAnterior);
            }
        }

        $datos = [
            'referencia' => $referencia,
            'nombre' => $nombre,
            'color' => $color,
            'categoria' => $categoria,
            'descripcion' => $descripcion,
            'precio' => $precio,
            'precio_compra' => $precio_compra,
            'stock_minimo' => $stock_minimo,
            'estado' => $estado
        ];
        if ($eliminarImagen || $imagen !== null) $datos['imagen'] = $imagen;

        $exito = $this->modelo->actualizar($id, $datos);

        // Si mandaron un nuevo stock, forzamos la actualización manual del stock absoluto y creamos el movimiento.
        if ($exito === true && $producto_actual['stock'] != $stock) {
            // Actualizar stock
            global $conn;
            $stmt = $conn->prepare("UPDATE productos SET stock = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param('ii', $stock, $id);
                $stmt->execute();
                $stmt->close();
            }

            // Registrar Movimiento de Ajuste
            $diferencia = abs($stock - $producto_actual['stock']);
            $tipo_ajuste = 'AJUSTE'; // o ENTRADA/SALIDA dependiendo de si subió o bajó, pero AJUSTE es semánticamente correcto aquí.
            $usuario_id = (int)($_SESSION['usuario']['id_usuario'] ?? 0);
            if ($usuario_id <= 0) {
                $this->setAlert('error', 'Sesión inválida', 'No se pudo identificar al usuario que ajusta el inventario.');
                $this->redirect();
            }
            $modeloMovimiento = new Movimiento();
            $modeloMovimiento->registrar($id, $usuario_id, $tipo_ajuste, $diferencia, $producto_actual['stock'], $stock, 'Ajuste manual desde edición de producto');
        }

        if ($exito === true) {
            Auditoria::registrar('Productos', 'Editar Producto', "Referencia: $referencia");
            $this->setAlert('success', '¡Actualizado!', 'El producto ha sido modificado exitosamente.');
        } else {
            $this->setAlert('error', 'Error', is_string($exito) ? $exito : 'Error desconocido al actualizar.');
        }
        
        $this->redirect();
    }

    /**
     * Archiva un producto desde una acción exclusiva del administrador.
     */
    public function archivar()
    {
        if (!AuthController::esAdmin()) {
            http_response_code(403);
            exit('Acceso denegado: solo un administrador puede archivar productos.');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            exit('Método no permitido.');
        }

        $csrfSesion = $_SESSION['csrf_archivar_producto'] ?? '';
        $csrfEnviado = $_POST['csrf_token'] ?? '';
        if (!is_string($csrfSesion) || $csrfSesion === '' || !is_string($csrfEnviado)
            || !hash_equals($csrfSesion, $csrfEnviado)) {
            http_response_code(403);
            exit('La solicitud expiró o no es válida. Recarga el catálogo e inténtalo de nuevo.');
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->setAlert('error', 'Error', 'ID de producto inválido.');
            $this->redirect();
        }

        $producto = $this->modelo->obtenerPorId($id);
        
        if (!$producto) {
            $this->setAlert('error', 'No encontrado', 'El producto que intenta archivar ya no existe.');
            $this->redirect();
        }

        if ((int)$producto['estado'] !== 1) {
            $this->setAlert('warning', 'Producto archivado', 'Este producto ya está archivado.');
            $this->redirect();
        }

        $exito = $this->modelo->archivar($id);

        if ($exito === true) {
            Auditoria::registrar(
                'Productos',
                'Archivar Producto',
                "ID Producto: $id; Referencia: {$producto['referencia']}; Stock conservado: {$producto['stock']}"
            );
            $this->setAlert('success', 'Producto archivado', 'Se ocultó del catálogo público y se conservaron sus existencias e historial.');
        } else {
            $this->setAlert('error', 'No se pudo archivar', 'El producto no se modificó. Recarga el catálogo e inténtalo de nuevo.');
        }

        $this->redirect();
    }

    private function setAlert($icon, $title, $text)
    {
        $_SESSION['alert'] = [
            'icon' => $icon,
            'title' => $title,
            'text' => $text
        ];
    }

    private function guardarImagenSubida(?array $archivo): ?string
    {
        if (!$archivo || ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
        if (($archivo['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || ($archivo['size'] ?? 0) > 5 * 1024 * 1024) {
            $this->setAlert('error', 'Imagen inválida', 'La imagen debe pesar menos de 5 MB.');
            $this->redirect();
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
        $extensiones = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($extensiones[$mime])) {
            $this->setAlert('error', 'Formato inválido', 'Usa una imagen JPG, PNG o WEBP.');
            $this->redirect();
        }
        $directorio = __DIR__ . '/../../public/img/productos';
        if (!is_dir($directorio)) mkdir($directorio, 0775, true);
        $nombre = bin2hex(random_bytes(12)) . '.' . $extensiones[$mime];
        if (!move_uploaded_file($archivo['tmp_name'], $directorio . '/' . $nombre)) {
            $this->setAlert('error', 'Error de imagen', 'No se pudo guardar la imagen.');
            $this->redirect();
        }
        return 'productos/' . $nombre;
    }

    private function redirect()
    {
        header('Location: ../../views/admin/gproductos.php');
        exit;
    }
}

// Router Simple (Front Controller Embedded)
if (strtolower(basename($_SERVER['PHP_SELF'] ?? '')) === 'productocontroller.php') {
    $controller = new ProductoController();
    $accion = $_GET['accion'] ?? '';

    switch ($accion) {
        case 'guardar':
            $controller->guardar();
            break;
        case 'actualizar':
            $controller->actualizar();
            break;
        case 'archivar':
            $controller->archivar();
            break;
        default:
            header('Location: ../../views/admin/gproductos.php');
            exit;
    }
}
?>
