<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Auditoria.php';

/**
 * MODELO DE VENTAS
 *
 * Gestiona:
 * - Registro de ventas
 * - Detalles de venta
 * - Actualización de stock
 * - Movimientos de inventario
 * - Historial de ventas
 * - Consulta de ventas
 * - Anulación de ventas
 * - Auditoría
 */
class Venta
{
    private \mysqli $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    /**
     * =========================================================
     * REGISTRAR VENTA COMPLETA
     * =========================================================
     */
    public function registrarVentaCompleta(
        int $usuarioId,
        array $carrito,
        float $subtotal,
        float $total,
        string $metodoPago = 'Efectivo',
        ?int $clienteId = null
    ): array {

        try {

            /* =====================================================
               VALIDACIONES INICIALES
            ===================================================== */

            if ($usuarioId <= 0) {
                throw new Exception("Usuario no válido.");
            }

            if (empty($carrito)) {
                throw new Exception(
                    "No se puede registrar una venta sin productos."
                );
            }

            if ($subtotal < 0) {
                throw new Exception(
                    "El subtotal no puede ser negativo."
                );
            }

            if ($total < 0) {
                throw new Exception(
                    "El total no puede ser negativo."
                );
            }

            $metodosPermitidos = [
                'Efectivo',
                'Tarjeta',
                'Transferencia'
            ];

            if (!in_array($metodoPago, $metodosPermitidos, true)) {
                throw new Exception(
                    "Método de pago no válido."
                );
            }

            /* =====================================================
               INICIAR TRANSACCIÓN
            ===================================================== */

            $this->conn->begin_transaction();

            /* =====================================================
               GENERAR CÓDIGO DE FACTURA ÚNICO
            ===================================================== */

            do {

                $codigoFactura =
                    'FAC-' .
                    date('YmdHis') .
                    '-' .
                    random_int(100, 999);

                $stmtCheck = $this->conn->prepare("
                    SELECT id
                    FROM ventas
                    WHERE codigo_factura = ?
                    LIMIT 1
                ");

                if (!$stmtCheck) {
                    throw new Exception(
                        "Error al verificar factura: " .
                        $this->conn->error
                    );
                }

                $stmtCheck->bind_param(
                    "s",
                    $codigoFactura
                );

                $stmtCheck->execute();

                $resultadoCheck =
                    $stmtCheck->get_result();

                $existe =
                    $resultadoCheck->num_rows > 0;

                $stmtCheck->close();

            } while ($existe);

            /* =====================================================
               INSERTAR CABECERA DE VENTA
            ===================================================== */

            $queryVenta = "
                INSERT INTO ventas
                (
                    codigo_factura,
                    usuario_id,
                    cliente_id,
                    subtotal,
                    total,
                    metodo_pago
                )
                VALUES (?, ?, ?, ?, ?, ?)
            ";

            $stmtVenta =
                $this->conn->prepare($queryVenta);

            if (!$stmtVenta) {
                throw new Exception(
                    "Error al preparar venta: " .
                    $this->conn->error
                );
            }

            $stmtVenta->bind_param(
                "siidds",
                $codigoFactura,
                $usuarioId,
                $clienteId,
                $subtotal,
                $total,
                $metodoPago
            );

            if (!$stmtVenta->execute()) {
                throw new Exception(
                    "Error al insertar venta: " .
                    $stmtVenta->error
                );
            }

            $ventaId =
                $this->conn->insert_id;

            $stmtVenta->close();

            /* =====================================================
               PREPARAR DETALLE
            ===================================================== */

            $queryDetalle = "
                INSERT INTO detalle_ventas
                (
                    venta_id,
                    producto_id,
                    cantidad,
                    precio_unitario,
                    subtotal
                )
                VALUES (?, ?, ?, ?, ?)
            ";

            $stmtDetalle =
                $this->conn->prepare($queryDetalle);

            if (!$stmtDetalle) {
                throw new Exception(
                    "Error al preparar detalle: " .
                    $this->conn->error
                );
            }

            /* =====================================================
               CONSULTAR STOCK
            ===================================================== */

            $queryStock = "
                SELECT stock
                FROM productos
                WHERE id = ?
                FOR UPDATE
            ";

            $stmtStock =
                $this->conn->prepare($queryStock);

            if (!$stmtStock) {
                throw new Exception(
                    "Error al preparar consulta de stock: " .
                    $this->conn->error
                );
            }

            /* =====================================================
               ACTUALIZAR STOCK
            ===================================================== */

            $queryUpdateStock = "
                UPDATE productos
                SET stock = ?
                WHERE id = ?
            ";

            $stmtUpdateStock =
                $this->conn->prepare($queryUpdateStock);

            if (!$stmtUpdateStock) {
                throw new Exception(
                    "Error al preparar actualización de stock: " .
                    $this->conn->error
                );
            }

            /* =====================================================
               REGISTRAR MOVIMIENTO
            ===================================================== */

            $queryMovimiento = "
                INSERT INTO movimientos
                (
                    producto_id,
                    usuario_id,
                    tipo,
                    cantidad,
                    stock_anterior,
                    stock_nuevo,
                    motivo
                )
                VALUES
                (
                    ?,
                    ?,
                    'SALIDA_VENTA',
                    ?,
                    ?,
                    ?,
                    ?
                )
            ";

            $stmtMovimiento =
                $this->conn->prepare($queryMovimiento);

            if (!$stmtMovimiento) {
                throw new Exception(
                    "Error al preparar movimiento: " .
                    $this->conn->error
                );
            }

            /* =====================================================
               PROCESAR CARRITO
            ===================================================== */

            $cantidadTotalProductos = 0;

            foreach ($carrito as $item) {

                if (
                    !isset($item['id']) ||
                    !isset($item['cantidad']) ||
                    !isset($item['precio'])
                ) {
                    throw new Exception(
                        "El producto contiene información incompleta."
                    );
                }

                $productoId =
                    (int)$item['id'];

                $cantidad =
                    (int)$item['cantidad'];

                $precioUnitario =
                    (float)$item['precio'];

                /* ---------------------------------------------
                   VALIDACIONES
                --------------------------------------------- */

                if ($productoId <= 0) {
                    throw new Exception(
                        "Producto no válido."
                    );
                }

                if ($cantidad < 1) {
                    throw new Exception(
                        "La cantidad debe ser mayor a cero."
                    );
                }

                if ($precioUnitario < 0) {
                    throw new Exception(
                        "El precio no puede ser negativo."
                    );
                }

                $cantidadTotalProductos += $cantidad;

                /* =================================================
                   VERIFICAR STOCK
                ================================================= */

                $stmtStock->bind_param(
                    "i",
                    $productoId
                );

                if (!$stmtStock->execute()) {
                    throw new Exception(
                        "No se pudo consultar el stock."
                    );
                }

                $resultadoStock =
                    $stmtStock->get_result();

                if ($resultadoStock->num_rows === 0) {
                    throw new Exception(
                        "El producto ID {$productoId} no existe."
                    );
                }

                $filaStock =
                    $resultadoStock->fetch_assoc();

                $stockActual =
                    (int)$filaStock['stock'];

                if ($stockActual < $cantidad) {
                    throw new Exception(
                        "Stock insuficiente para el producto ID " .
                        $productoId .
                        ". Disponible: " .
                        $stockActual .
                        ". Solicitado: " .
                        $cantidad .
                        "."
                    );
                }

                $nuevoStock =
                    $stockActual - $cantidad;

                /* =================================================
                   CALCULAR SUBTOTAL
                ================================================= */

                $subtotalItem =
                    $cantidad * $precioUnitario;

                /* =================================================
                   INSERTAR DETALLE
                ================================================= */

                $stmtDetalle->bind_param(
                    "iiidd",
                    $ventaId,
                    $productoId,
                    $cantidad,
                    $precioUnitario,
                    $subtotalItem
                );

                if (!$stmtDetalle->execute()) {
                    throw new Exception(
                        "Error al insertar detalle: " .
                        $stmtDetalle->error
                    );
                }

                /* =================================================
                   ACTUALIZAR STOCK
                ================================================= */

                $stmtUpdateStock->bind_param(
                    "ii",
                    $nuevoStock,
                    $productoId
                );

                if (!$stmtUpdateStock->execute()) {
                    throw new Exception(
                        "Error al actualizar stock: " .
                        $stmtUpdateStock->error
                    );
                }

                /* =================================================
                   REGISTRAR MOVIMIENTO
                ================================================= */

                $motivo =
                    "Venta Factura: " .
                    $codigoFactura;

                $stmtMovimiento->bind_param(
                    "iiiiis",
                    $productoId,
                    $usuarioId,
                    $cantidad,
                    $stockActual,
                    $nuevoStock,
                    $motivo
                );

                if (!$stmtMovimiento->execute()) {
                    throw new Exception(
                        "Error al registrar movimiento: " .
                        $stmtMovimiento->error
                    );
                }
            }

            /* =====================================================
               VALIDAR CANTIDAD TOTAL
            ===================================================== */

            if ($cantidadTotalProductos <= 0) {
                throw new Exception(
                    "La venta debe contener productos."
                );
            }

            /* =====================================================
               CERRAR STATEMENTS
            ===================================================== */

            $stmtDetalle->close();
            $stmtStock->close();
            $stmtUpdateStock->close();
            $stmtMovimiento->close();

            /* =====================================================
               CONFIRMAR TRANSACCIÓN
            ===================================================== */

            $this->conn->commit();

            /* =====================================================
               AUDITORÍA
            ===================================================== */

            Auditoria::registrar(
                'Ventas',
                'Registrar Venta',
                "Factura: {$codigoFactura} | " .
                "Total: {$total} | " .
                "Método: {$metodoPago}"
            );

            return [
                'success' => true,
                'mensaje' =>
                    'Venta procesada con éxito.',
                'venta_id' =>
                    $ventaId,
                'codigo_factura' =>
                    $codigoFactura
            ];

        } catch (Throwable $e) {

            try {
                $this->conn->rollback();
            } catch (Throwable $rollbackError) {
                // No ocultar el error original
            }

            return [
                'success' => false,
                'mensaje' =>
                    $e->getMessage()
            ];
        }
    }


    /**
     * =========================================================
     * OBTENER HISTORIAL DE VENTAS
     * =========================================================
     */
    public function obtenerHistorial(
        int $limite = 100,
        ?int $usuarioId = null
    ): array {

        $limite =
            max(
                1,
                min($limite, 500)
            );

        $filtroUsuario = $usuarioId !== null ? 'WHERE v.usuario_id = ' . (int)$usuarioId : '';
        $sql = "
            SELECT
                v.id,
                v.codigo_factura,
                v.usuario_id,
                v.cliente_id,
                v.subtotal,
                v.total,
                v.metodo_pago
            FROM ventas v
            {$filtroUsuario}
            ORDER BY v.id DESC
            LIMIT {$limite}
        ";

        $resultado =
            $this->conn->query($sql);

        if (!$resultado) {
            return [];
        }

        $ventas = [];

        while (
            $fila =
            $resultado->fetch_assoc()
        ) {
            $ventas[] = $fila;
        }

        return $ventas;
    }


    /**
     * =========================================================
     * OBTENER UNA VENTA
     * =========================================================
     */
    public function obtenerVenta(
        int $ventaId
    ): ?array {

        if ($ventaId <= 0) {
            return null;
        }

        $stmt =
            $this->conn->prepare("
                SELECT
                    id,
                    codigo_factura,
                    usuario_id,
                    cliente_id,
                    subtotal,
                    total,
                    metodo_pago
                FROM ventas
                WHERE id = ?
                LIMIT 1
            ");

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param(
            "i",
            $ventaId
        );

        $stmt->execute();

        $resultado =
            $stmt->get_result();

        $venta =
            $resultado->fetch_assoc();

        $stmt->close();

        return $venta ?: null;
    }


    /**
     * =========================================================
     * OBTENER DETALLES DE UNA VENTA
     * =========================================================
     */
    public function obtenerDetallesVenta(
        int $ventaId
    ): array {

        if ($ventaId <= 0) {
            return [];
        }

        $stmt =
            $this->conn->prepare("
                SELECT
                    dv.venta_id,
                    dv.producto_id,
                    dv.cantidad,
                    dv.precio_unitario,
                    dv.subtotal,
                    p.nombre,
                    p.referencia,
                    p.color
                FROM detalle_ventas dv
                INNER JOIN productos p
                    ON p.id = dv.producto_id
                WHERE dv.venta_id = ?
                ORDER BY dv.producto_id ASC
            ");

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param(
            "i",
            $ventaId
        );

        $stmt->execute();

        $resultado =
            $stmt->get_result();

        $detalles = [];

        while (
            $fila =
            $resultado->fetch_assoc()
        ) {
            $detalles[] = $fila;
        }

        $stmt->close();

        return $detalles;
    }


    /**
     * =========================================================
     * ANULAR VENTA
     * =========================================================
     *
     * 1. Busca la venta.
     * 2. Busca sus productos.
     * 3. Devuelve las cantidades al inventario.
     * 4. Registra los movimientos.
     * 5. Elimina los detalles.
     * 6. Elimina la venta.
     * 7. Registra auditoría.
     */
    public function anularVenta(
        int $ventaId,
        int $usuarioId
    ): array {

        try {

            /* =====================================================
               VALIDACIONES
            ===================================================== */

            if ($ventaId <= 0) {
                throw new Exception(
                    "Venta no válida."
                );
            }

            if ($usuarioId <= 0) {
                throw new Exception(
                    "Usuario no válido."
                );
            }


            /* =====================================================
               INICIAR TRANSACCIÓN
            ===================================================== */

            $this->conn->begin_transaction();


            /* =====================================================
               BUSCAR VENTA
            ===================================================== */

            $stmtVenta =
                $this->conn->prepare("
                    SELECT
                        id,
                        codigo_factura,
                        total
                    FROM ventas
                    WHERE id = ?
                    FOR UPDATE
                ");

            if (!$stmtVenta) {
                throw new Exception(
                    "Error al consultar venta: " .
                    $this->conn->error
                );
            }

            $stmtVenta->bind_param(
                "i",
                $ventaId
            );

            $stmtVenta->execute();

            $resultadoVenta =
                $stmtVenta->get_result();

            if (
                $resultadoVenta->num_rows === 0
            ) {
                throw new Exception(
                    "La venta no existe."
                );
            }

            $venta =
                $resultadoVenta->fetch_assoc();

            $codigoFactura =
                $venta['codigo_factura'];

            $stmtVenta->close();


            /* =====================================================
               OBTENER DETALLES
            ===================================================== */

            $stmtDetalles =
                $this->conn->prepare("
                    SELECT
                        producto_id,
                        cantidad
                    FROM detalle_ventas
                    WHERE venta_id = ?
                ");

            if (!$stmtDetalles) {
                throw new Exception(
                    "Error al consultar detalles."
                );
            }

            $stmtDetalles->bind_param(
                "i",
                $ventaId
            );

            $stmtDetalles->execute();

            $resultadoDetalles =
                $stmtDetalles->get_result();

            if (
                $resultadoDetalles->num_rows === 0
            ) {
                throw new Exception(
                    "La venta no contiene productos."
                );
            }


            /* =====================================================
               PREPARAR STOCK
            ===================================================== */

            $stmtStock =
                $this->conn->prepare("
                    SELECT stock
                    FROM productos
                    WHERE id = ?
                    FOR UPDATE
                ");

            $stmtUpdate =
                $this->conn->prepare("
                    UPDATE productos
                    SET stock = ?
                    WHERE id = ?
                ");

            $stmtMovimiento =
                $this->conn->prepare("
                    INSERT INTO movimientos
                    (
                        producto_id,
                        usuario_id,
                        tipo,
                        cantidad,
                        stock_anterior,
                        stock_nuevo,
                        motivo
                    )
                    VALUES
                    (
                        ?,
                        ?,
                        'ENTRADA_ANULACION',
                        ?,
                        ?,
                        ?,
                        ?
                    )
                ");

            if (
                !$stmtStock ||
                !$stmtUpdate ||
                !$stmtMovimiento
            ) {
                throw new Exception(
                    "No se pudieron preparar las operaciones de inventario."
                );
            }


            /* =====================================================
               DEVOLVER PRODUCTOS AL STOCK
            ===================================================== */

            while (
                $detalle =
                $resultadoDetalles->fetch_assoc()
            ) {

                $productoId =
                    (int)$detalle['producto_id'];

                $cantidad =
                    (int)$detalle['cantidad'];

                if ($productoId <= 0) {
                    throw new Exception(
                        "Producto inválido en la venta."
                    );
                }

                if ($cantidad <= 0) {
                    throw new Exception(
                        "Cantidad inválida en la venta."
                    );


                }


                /* =================================================
                   OBTENER STOCK ACTUAL
                ================================================= */

                $stmtStock->bind_param(
                    "i",
                    $productoId
                );

                if (!$stmtStock->execute()) {
                    throw new Exception(
                        "No se pudo consultar el stock."
                    );
                }

                $resultadoStock =
                    $stmtStock->get_result();

                if (
                    $resultadoStock->num_rows === 0
                ) {
                    throw new Exception(
                        "El producto ID {$productoId} no existe."
                    );
                }

                $filaStock =
                    $resultadoStock->fetch_assoc();

                $stockAnterior =
                    (int)$filaStock['stock'];

                $stockNuevo =
                    $stockAnterior + $cantidad;


                /* =================================================
                   ACTUALIZAR STOCK
                ================================================= */

                $stmtUpdate->bind_param(
                    "ii",
                    $stockNuevo,
                    $productoId
                );

                if (!$stmtUpdate->execute()) {
                    throw new Exception(
                        "No se pudo devolver el producto al inventario."
                    );
                }


                /* =================================================
                   REGISTRAR MOVIMIENTO
                ================================================= */

                $motivo =
                    "Anulación de venta - Factura: " .
                    $codigoFactura;

                $stmtMovimiento->bind_param(
                    "iiiiis",
                    $productoId,
                    $usuarioId,
                    $cantidad,
                    $stockAnterior,
                    $stockNuevo,
                    $motivo
                );

                if (!$stmtMovimiento->execute()) {
                    throw new Exception(
                        "No se pudo registrar el movimiento."
                    );
                }
            }


            /* =====================================================
               CERRAR CONSULTAS
            ===================================================== */

            $stmtDetalles->close();
            $stmtStock->close();
            $stmtUpdate->close();
            $stmtMovimiento->close();


            /* =====================================================
               ELIMINAR DETALLES
            ===================================================== */

            $stmtEliminarDetalles =
                $this->conn->prepare("
                    DELETE FROM detalle_ventas
                    WHERE venta_id = ?
                ");

            if (!$stmtEliminarDetalles) {
                throw new Exception(
                    "No se pudo preparar eliminación de detalles."
                );
            }

            $stmtEliminarDetalles->bind_param(
                "i",
                $ventaId
            );

            if (!$stmtEliminarDetalles->execute()) {
                throw new Exception(
                    "No se pudieron eliminar los detalles."
                );
            }

            $stmtEliminarDetalles->close();


            /* =====================================================
               ELIMINAR CABECERA DE VENTA
            ===================================================== */

            $stmtEliminarVenta =
                $this->conn->prepare("
                    DELETE FROM ventas
                    WHERE id = ?
                ");

            if (!$stmtEliminarVenta) {
                throw new Exception(
                    "No se pudo preparar eliminación de venta."
                );
            }

            $stmtEliminarVenta->bind_param(
                "i",
                $ventaId
            );

            if (!$stmtEliminarVenta->execute()) {
                throw new Exception(
                    "No se pudo eliminar la venta."
                );
            }

            $stmtEliminarVenta->close();


            /* =====================================================
               CONFIRMAR TRANSACCIÓN
            ===================================================== */

            $this->conn->commit();


            /* =====================================================
               REGISTRAR AUDITORÍA
            ===================================================== */

            Auditoria::registrar(
                'Ventas',
                'Anular Venta',
                "Factura: {$codigoFactura} | " .
                "Venta ID: {$ventaId}"
            );


            return [
                'success' => true,
                'mensaje' =>
                    'La venta fue anulada correctamente.',
                'codigo_factura' =>
                    $codigoFactura
            ];


        } catch (Throwable $e) {

            /* =====================================================
               ROLLBACK
            ===================================================== */

            try {
                $this->conn->rollback();
            } catch (Throwable $rollbackError) {
                // No ocultar el error original
            }

            return [
                'success' => false,
                'mensaje' =>
                    $e->getMessage()
            ];
        }
    }
}