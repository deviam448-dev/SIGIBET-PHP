# Trazabilidad de Historias de Usuario

Este documento consolida las historias compartidas para Bella Tela/SIGIBET y relaciona cada requisito con el código que lo implementa.

## Criterio de estado

- **Implementada:** existe el flujo y fue validado con lint y carga HTTP o prueba funcional.
- **Parcial:** existe una parte del flujo, pero requiere una dependencia externa o una corrección adicional.
- **Pendiente:** no se debe presentar como funcional hasta implementar y probar el criterio.

## Historias comunes de autenticación y seguridad

| ID | Requisito | Implementación | Estado | Validación |
|---|---|---|---|---|
| HU-001 | Iniciar sesión con usuario o correo y contraseña | `AuthController::login()` | Implementada | Campos vacíos, credenciales y redirección por rol |
| HU-002 | Limitar usuarios activos a dos por rol | `Usuario::contarPorRol()` y `Usuario::registrar()` | Implementada | Regla aplicada en registro y actualización |
| HU-003 | Guardar usuarios y permitir acceso con sus datos | `Usuario::registrar()` | Implementada | Contraseña con `password_hash()` |
| HU-004 | Rechazar credenciales incorrectas y campos vacíos | `AuthController::login()` | Implementada | Mensajes de sesión y retorno al login |
| HU-005 | Bloquear después de tres intentos fallidos | `Usuario::registrarIntentoFallido()` | Implementada | Bloqueo temporal de 15 minutos |
| HU-006 | Recuperar contraseña | `recuperacion_password`, `AuthController` y vistas Auth | Parcial | Requiere configurar `mail()` para entregar el enlace |
| HU-021 | Restringir información según rol | `AuthController::esAdmin()` y `requerirRol()` | Parcial | Debe probarse cada ruta administrativa con sesión de empleado |

## Historias de administrador

| ID | Requisito | Implementación | Estado |
|---|---|---|---|
| HU-007 | Buscar productos por nombre, color o referencia | `ProductoController::index()` y `VentaController::buscarAjax()` | Implementada |
| HU-008 | Evitar búsquedas vacías | Validación de formularios de productos | Parcial |
| HU-010 | Rechazar cantidades menores que uno | `ProductoController` y `Venta` | Implementada |
| HU-011 | Rechazar precios negativos | `ProductoController` y `Venta` | Implementada |
| HU-012 | Evitar productos duplicados | `Producto::referenciaExiste()` | Implementada |
| HU-013 | Consultar el estado del inventario | `InventarioController` | Implementada |
| HU-014 | Reportes, bajo stock, movimientos y valor de inventario | `Movimiento`, `Reporte` e `InventarioController` | Parcial |
| HU-015 | No eliminar productos con stock | `ProductoController` | Parcial |
| HU-016 | Configurar datos del negocio y facturación | `ConfiguracionController` | Implementada |
| HU-017 | Guardar cambios de configuración | `Configuracion::actualizar()` | Implementada |
| HU-019 | Gestionar ventas e historial | `Venta` y vistas POS/reportes | Implementada |
| HU-020 | Auditar acciones por módulo y usuario | `Auditoria::registrar()` | Implementada |
| HU-022 | Crear copias de seguridad | `BackupController` | Implementada |
| HU-023 | Restaurar copias de seguridad | `BackupController` | Implementada |
| HU-024 | Elegir y aplicar colores de apariencia | `Configuracion::normalizarColor()` y layout | Parcial |

## Historias de empleado

| ID | Requisito | Implementación | Estado |
|---|---|---|---|
| EMP-001 | Iniciar sesión | Login compartido | Implementada |
| EMP-002 | Registrar ventas | `views/seller/mis_ventas.php` y `VentaController` | Implementada |
| EMP-003 | Buscar productos disponibles | `VentaController::buscarAjax()` | Implementada |
| EMP-004 | Consultar y actualizar inventario autorizado | Catálogo e inventario enlazados desde sidebar | Parcial |
| EMP-005 | Consultar historial propio | `views/seller/historial.php` y filtro `usuario_id` | Implementada |
| EMP-006 | Gestionar clientes | Vista de clientes y `ClienteController` | Parcial |
| EMP-007 | Recibir alerta de bajo stock | Dashboard de inventario | Parcial |
| EMP-008 | Identificar acciones mediante colores | Variables de tema del layout | Parcial |

## Dependencias externas

La recuperación por correo necesita que el servidor PHP de Laragon tenga configurado `mail()` o un SMTP. La recuperación por teléfono requiere integrar un proveedor SMS; no se puede considerar funcional sin credenciales y servicio de envío.

## Regla de aceptación

Una historia sólo se marcará como completamente implementada cuando el flujo pueda ejecutarse desde la interfaz, respete el rol correspondiente, persista los datos necesarios y tenga una validación reproducible.