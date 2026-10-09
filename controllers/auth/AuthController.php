<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/GmailMailer.php';
require_once __DIR__ . '/../../models/Usuario.php';

class AuthController
{
    /**
     * Obtener el usuario actualmente autenticado.
     */
    public static function usuarioActual()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return $_SESSION['usuario'] ?? null;
    }

    /**
     * Verificar si el usuario actual es administrador.
     */
    public static function esAdmin()
    {
        $usuario = self::usuarioActual();

        return (
            $usuario &&
            isset($usuario['id_rol']) &&
            (int)$usuario['id_rol'] === 1
        );
    }

    /**
     * Verificar que el usuario tenga uno de los roles permitidos.
     */
    public static function requerirRol(array $rolesPermitidos)
    {
        $usuario = self::usuarioActual();

        if (
            !$usuario ||
            !isset($usuario['id_rol']) ||
            !in_array((int)$usuario['id_rol'], $rolesPermitidos, true)
        ) {
            header('HTTP/1.0 403 Forbidden');
            header('Content-Type: application/json; charset=utf-8');

            echo json_encode([
                'success' => false,
                'mensaje' => 'Acceso denegado. No tienes permisos para esta acción.'
            ]);

            exit;
        }
    }

    /**
     * Login del sistema.
     *
     * HU-004:
     * - Validar campos vacíos.
     * - Validar usuario/contraseña.
     *
     * HU-005:
     * - Máximo 3 intentos fallidos.
     * - Bloqueo durante 15 minutos.
     */
    public function login()
    {
        /*
         * Si ya existe una sesión iniciada,
         * no volver a mostrar el login.
         */
        if (isset($_SESSION['usuario'])) {

            $idRol = (int)($_SESSION['usuario']['id_rol'] ?? 0);

            if ($idRol === 1) {
                header('Location: ../../views/admin/gproductos.php');
                exit;
            }

            if ($idRol === 2) {
                header('Location: ../../views/seller/mis_ventas.php');
                exit;
            }

            // Si el rol no es válido, cerrar sesión.
            session_unset();
            session_destroy();

            header('Location: ../../views/Auth/login.php');
            exit;
        }

        /*
         * El login solamente debe procesar solicitudes POST.
         */
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../../views/Auth/login.php');
            exit;
        }

        /*
         * Permitir login mediante correo o nombre de usuario.
         */
        $identificador = trim(
            $_POST['email']
            ?? $_POST['usuario']
            ?? ''
        );

        $password = trim($_POST['password'] ?? '');

        /*
         * HU-004:
         * Validar campos obligatorios.
         */
        if ($identificador === '' || $password === '') {

            $_SESSION['alert'] = [
                'icon' => 'warning',
                'title' => 'Campos incompletos',
                'text' => 'Debe ingresar su correo/usuario y contraseña.'
            ];

            header('Location: ../../views/Auth/login.php');
            exit;
        }

        /*
         * Crear modelo de usuario.
         */
        $usuarioModel = new Usuario();

        /*
         * Buscar usuario por correo o nombre de usuario.
         */
        $usuario = $usuarioModel->obtenerPorEmail($identificador);

        /*
         * Usuario inexistente o cuenta inactiva.
         */
        if (!$usuario || $usuario['estado'] === 'inactivo') {

            $_SESSION['alert'] = [
                'icon' => 'error',
                'title' => 'Usuario no encontrado',
                'text' => 'Los datos son incorrectos o la cuenta está inactiva.'
            ];

            header('Location: ../../views/Auth/login.php');
            exit;
        }

        /*
         * =========================================================
         * HU-005 - VERIFICAR BLOQUEO
         * =========================================================
         *
         * Se utiliza directamente "bloqueado_hasta".
         *
         * No utilizamos "ultimo_acceso", porque ese dato no representa
         * correctamente el momento en que termina el bloqueo.
         */
        if (!empty($usuario['bloqueado_hasta'])) {

            $bloqueadoHasta = strtotime($usuario['bloqueado_hasta']);
            $ahora = time();

            /*
             * El bloqueo todavía está vigente.
             */
            if ($bloqueadoHasta !== false && $bloqueadoHasta > $ahora) {

                $segundosRestantes = $bloqueadoHasta - $ahora;

                /*
                 * Redondear hacia arriba para que nunca se muestre
                 * "0 minutos" mientras todavía existe bloqueo.
                 */
                $minutosRestantes = (int)ceil($segundosRestantes / 60);

                if ($minutosRestantes < 1) {
                    $minutosRestantes = 1;
                }

                $_SESSION['alert'] = [
                    'icon' => 'error',
                    'title' => 'Cuenta bloqueada',
                    'text' => 'Demasiados intentos fallidos. Intente nuevamente en '
                        . $minutosRestantes
                        . ' minuto(s).'
                ];

                header('Location: ../../views/Auth/login.php');
                exit;
            }

            /*
             * Si el bloqueo ya terminó, limpiar los intentos.
             */
            $usuarioModel->resetearIntentosYActualizarAcceso(
                (int)$usuario['id_usuario']
            );

            $usuario['intentos_fallidos'] = 0;
            $usuario['bloqueado_hasta'] = null;
            $usuario['estado'] = 'activo';
        }

        /*
         * =========================================================
         * VERIFICAR CONTRASEÑA
         * =========================================================
         */
        if (!password_verify($password, $usuario['password_hash'])) {

            /*
             * Cantidad de intentos que tenía ANTES de este intento.
             */
            $intentosActuales = (int)(
                $usuario['intentos_fallidos'] ?? 0
            );

            /*
             * Registrar el nuevo intento fallido.
             *
             * Usuario.php se encarga de:
             * - aumentar el contador.
             * - bloquear al llegar a 3.
             * - establecer 15 minutos de bloqueo.
             */
            $usuarioModel->registrarIntentoFallido(
                (int)$usuario['id_usuario'],
                $intentosActuales
            );

            /*
             * Calcular el nuevo número de intentos.
             */
            $nuevosIntentos = $intentosActuales + 1;

            /*
             * =====================================================
             * HU-005 - TERCER INTENTO FALLIDO
             * =====================================================
             */
            if ($nuevosIntentos >= 3) {

                $mensaje = 'Su cuenta ha sido bloqueada por seguridad. '
                    . 'Espere 15 minutos antes de volver a intentarlo.';

            } else {

                /*
                 * Intentos que todavía le quedan al usuario.
                 */
                $intentosRestantes = 3 - $nuevosIntentos;

                $mensaje = 'Contraseña incorrecta. Le quedan '
                    . $intentosRestantes
                    . ' intento(s).';
            }

            $_SESSION['alert'] = [
                'icon' => 'error',
                'title' => 'Error de autenticación',
                'text' => $mensaje
            ];

            header('Location: ../../views/Auth/login.php');
            exit;
        }

        /*
         * =========================================================
         * LOGIN CORRECTO
         * =========================================================
         *
         * Si la contraseña es correcta:
         * - Reiniciar intentos fallidos.
         * - Quitar cualquier bloqueo.
         */
        $usuarioModel->resetearIntentosYActualizarAcceso(
            (int)$usuario['id_usuario']
        );

        /*
         * Regenerar el ID de sesión por seguridad.
         */
        session_regenerate_id(true);

        /*
         * Guardar solamente la información necesaria
         * en la sesión.
         */
        $_SESSION['usuario'] = [
            'id_usuario' => (int)$usuario['id_usuario'],
            'nombre' => $usuario['nombre'],
            'usuario' => $usuario['usuario'],
            'email' => $usuario['email'],
            'id_rol' => (int)$usuario['id_rol']
        ];

        /*
         * =========================================================
         * REDIRECCIÓN SEGÚN ROL
         * =========================================================
         */
        switch ((int)$usuario['id_rol']) {

            /*
             * Administrador.
             */
            case 1:
                header('Location: ../../views/admin/gproductos.php');
                exit;

            /*
             * Empleado / vendedor.
             */
            case 2:
                header('Location: ../../views/seller/mis_ventas.php');
                exit;

            /*
             * Rol desconocido.
             */
            default:

                session_unset();
                session_destroy();

                session_start();

                $_SESSION['alert'] = [
                    'icon' => 'error',
                    'title' => 'Rol no válido',
                    'text' => 'No se pudo determinar el acceso del usuario.'
                ];

                header('Location: ../../views/Auth/login.php');
                exit;
        }
    }

    /**
     * Cerrar sesión.
     */
    public function logout()
    {
        /*
         * Limpiar todas las variables de sesión.
         */
        $_SESSION = [];

        /*
         * Eliminar la cookie de sesión si existe.
         */
        if (ini_get('session.use_cookies')) {

            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        /*
         * Destruir la sesión.
         */
        session_destroy();

        /*
         * Regresar al login.
         */
        header('Location: ../../views/Auth/login.php');
        exit;
    }

    public function solicitarRecuperacion(): void
    {
        $email = trim($_POST['email'] ?? '');
        $redirect = '../../views/Auth/recuperar.php';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['recovery_feedback'] = [
                'type' => 'error',
                'message' => 'Escribe una dirección de correo electrónico válida.'
            ];
            header('Location: ' . $redirect);
            exit;
        }

        $usuarioModel = new Usuario();
        $token = $usuarioModel->crearTokenRecuperacion($email);

        if ($token !== null) {
            $usuario = $usuarioModel->obtenerPorEmail($email);
            try {
                if (!$usuario || empty($usuario['email'])) {
                    throw new RuntimeException('No se encontró una dirección de correo para la cuenta.');
                }
                GmailMailer::enviarRecuperacion($usuario['email'], $token);
            } catch (RuntimeException $exception) {
                $usuarioModel->invalidarTokenRecuperacion($token);
                $cause = $exception->getPrevious();
                error_log(
                    'Error al enviar recuperación de contraseña: ' . $exception->getMessage()
                    . ($cause ? ' Detalle SMTP: ' . $cause->getMessage() : '')
                );
                $feedbackMessage = str_contains($exception->getMessage(), 'Falta configurar la contraseña de aplicación')
                    ? 'Falta configurar una contraseña de aplicación de Gmail en config/mail.local.php. La contraseña normal de Gmail no funciona para SMTP.'
                    : 'No se pudo enviar el correo de recuperación. Revisa config/mail.local.php y el registro de errores de PHP.';
                $_SESSION['recovery_feedback'] = [
                    'type' => 'error',
                    'message' => $feedbackMessage
                ];
                header('Location: ' . $redirect);
                exit;
            }
        }

        $_SESSION['recovery_feedback'] = [
            'type' => 'success',
            'message' => 'Si el correo está asociado a una cuenta activa, recibirás un enlace para restablecer la contraseña.'
        ];
        header('Location: ' . $redirect);
        exit;
    }

    public function restablecerPassword(): void
    {
        $token = trim($_POST['token'] ?? '');
        $nuevaPassword = (string)($_POST['password'] ?? '');
        $confirmacion = (string)($_POST['password_confirmacion'] ?? '');

        if (strlen($nuevaPassword) < 8 || $nuevaPassword !== $confirmacion) {
            $_SESSION['alert'] = ['icon' => 'warning', 'title' => 'Datos inválidos', 'text' => 'La contraseña debe tener al menos 8 caracteres y coincidir con la confirmación.'];
            header('Location: ../../views/Auth/restablecer.php?token=' . urlencode($token));
            exit;
        }

        $ok = (new Usuario())->restablecerPasswordConToken($token, $nuevaPassword);
        $_SESSION['alert'] = $ok
            ? ['icon' => 'success', 'title' => 'Contraseña actualizada', 'text' => 'Ya puedes iniciar sesión con tu nueva contraseña.']
            : ['icon' => 'error', 'title' => 'Enlace inválido', 'text' => 'El enlace expiró o ya fue utilizado.'];
        header('Location: ../../views/Auth/login.php');
        exit;
    }
}


/*
 * =============================================================
 * EJECUCIÓN DIRECTA DEL CONTROLADOR
 * =============================================================
 *
 * Permite utilizar:
 *
 * AuthController.php
 *
 * y:
 *
 * AuthController.php?accion=logout
 */
if (basename($_SERVER['PHP_SELF']) === 'AuthController.php') {

    $controller = new AuthController();

    $accion = $_GET['accion'] ?? 'login';

    if ($accion === 'logout') {
        $controller->logout();
    } elseif ($accion === 'solicitarRecuperacion') {
        $controller->solicitarRecuperacion();
    } elseif ($accion === 'restablecerPassword') {
        $controller->restablecerPassword();
    } else {
        $controller->login();
    }
}
?>