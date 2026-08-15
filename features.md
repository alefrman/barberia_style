# Barbería Style — Características del Proyecto (features.md)

Sistema web completo para una barbería con **sitio público** (vitrina) y **panel de administración** (gestión interna). Documento de inventario de funcionalidades implementadas.

---

## 1. Resumen general

| Área | Descripción |
|---|---|
| **Vista pública** | Página de inicio, servicios, productos, equipo y galería. Contenido 100% desde la base de datos. |
| **Panel admin** | Dashboard, citas, inventario, servicios, barberos, gastos, galería y usuarios. Acceso protegido por sesión y roles. |
| **Autenticación** | Login/logout con bcrypt, tokens CSRF, protección contra session fixation. |
| **Roles** | `Superadmin` (acceso total) y `Administrador` (gestión operativa). |

---

## 2. Sitio público

### 2.1 Inicio (`/`)
- **Hero** de presentación con titular, llamados a la acción y foto de portada.
- **Marquee** animado con palabras clave del negocio.
- **Sección Servicios**: tarjetas desde BD (imagen, nombre, descripción, precio, duración).
- **Sección Productos**: preview de hasta 4 productos desde BD.
- **Sección Equipo**: tarjetas de barberos desde BD.
- **Sección Galería**: preview de hasta 4 fotos desde BD.
- **Estadísticas**: contadores de servicios, productos, barberos y estilos (solo se muestran si hay barberos o fotos).
- **CTA final**: "Agendar ahora" + teléfono de contacto desde la tabla `settings`.
- **Footer**: contacto (teléfono, email, dirección) desde `settings`.

### 2.2 Servicios (`/services`)
- Lista completa de servicios activos, ordenados por `sort_order`.

### 2.3 Productos (`/products`)
- Lista completa de productos activos, ordenados por `sort_order`.

### 2.4 Equipo (`/team`)
- Lista de barberos activos con foto, cargo y descripción, ordenados por `sort_order`.

### 2.5 Galería (`/gallery`)
- Grid de fotos del portafolio con **lightbox (GLightbox)**.
- Al hacer clic se amplía la imagen con zoom, navegación entre fotos y gestos táctiles.
- Título y descripción de cada corte se muestran en el overlay (hover) y dentro del lightbox.

### 2.6 Configuración dinámica
- Contacto (teléfono/email/dirección) se lee de la tabla `settings`, no está hardcodeado.

---

## 3. Panel de administración (`/admin.php`)

### 3.1 Autenticación
- Login con email + contraseña, verificación CSRF.
- Contraseñas almacenadas con `password_hash` (bcrypt).
- Regeneración de ID de sesión al iniciar sesión.
- Middleware `AuthMiddleware` protege todas las rutas del panel.
- `RoleMiddleware` restringe módulos sensibles (ej. Usuarios solo `Superadmin`).

### 3.2 Dashboard
- Tarjetas de resumen: **citas de hoy**, **total de servicios**, **total de productos**, **total de gastos**.
- **KPIs financieros del mes**: ingresos, gastos, ganancia neta (ingresos − gastos) y ticket promedio.
- **Gráficas con Chart.js (CDN)**:
  - Barras: ingresos vs gastos de los últimos 12 meses.
  - Línea: ganancia neta mensual.
  - Donas: gastos del mes por categoría y por método de pago.
  - Barras: estados de cita (**Completada / No asistió / Cancelada**) con **filtro por mes o semana actual** (`?period=month|week`).
- **Regla financiera**: el ingreso solo considera citas con estado **"Completada"**.
- Bienvenida con nombre y rol del usuario autenticado.

### 3.3 Citas (turnos)
- Listado con **filtros**: estado, rango de fechas, búsqueda por cliente (nombre/teléfono/email).
- **Tarjetas de conteo** por estado: hoy, pendientes, confirmadas, completadas y total.
- Crear/editar/ver/eliminar citas.
- **Cabecera de cita**: tipo (Ahora/Programada), estado (Pendiente/Confirmada/Completada/Cancelada/No asistió), datos del cliente, fecha y hora, notas.
- **Detalle de cita**: uno o más servicios (con barbero asignado) y/o productos (con cantidad).
- **Totales calculados desde BD**: subtotal = suma de precios de servicios + (precio × cantidad) de productos.
- **Control de stock automático**: al agregar/editar/eliminar productos en una cita se descuenta/restaura el inventario.
- Guardado con **transacciones SQL** (todo o nada).

### 3.4 Servicios
- CRUD completo de servicios del catálogo.
- **Categorías** con creación inline ("Nueva categoría" sin salir del formulario).
- **Imagen** opcional con preview (JPG/PNG/WEBP/GIF, máx 2 MB).
- Campos: categoría, nombre, descripción, precio (USD), duración (min), orden, activo/inactivo.
- La eliminación se bloquea si el servicio está asociado a una cita (FK RESTRICT) con mensaje amigable.

### 3.5 Inventario (productos)
- CRUD de productos en venta.
- **Categorías** con creación inline.
- Campos: categoría, nombre, descripción, precio (USD), costo (USD), **stock**, **stock mínimo**, imagen, orden, activo/inactivo.
- **Filtros**: búsqueda, categoría, estado de stock (bajo / agotado).
- **Tarjetas de resumen**: total de productos, unidades en stock, stock bajo, agotados.
- La eliminación se bloquea si el producto está en una cita.

### 3.6 Barberos (equipo)
- CRUD de miembros del equipo.
- Campos: nombre, cargo, descripción, imagen, orden, activo/inactivo.

### 3.7 Gastos
- CRUD de salidas de efectivo.
- **Categorías** con creación inline.
- Campos: categoría, descripción, **monto** (USD, mayor a 0), fecha, **método de pago** (Efectivo/Tarjeta/Transferencia/Otro), notas.
- Registra automáticamente el usuario que creó el gasto (`created_by`).
- **Filtros**: búsqueda, categoría, rango de fechas.
- **Tarjetas de resumen**: gastos de hoy, total del mes, cantidad de gastos del mes, total acumulado.

### 3.8 Galería (portafolio)
- CRUD de fotos del portafolio.
- Campos: título (obligatorio), descripción, imagen, orden, activo/inactivo.
- Al eliminar una foto se borra también el archivo del disco.

### 3.9 Usuarios (solo Superadmin)
- CRUD de usuarios del panel.
- Roles asignables, email único, contraseña mínima 6 caracteres.
- **Auto-protección**: no puedes eliminar, desactivar ni cambiar el rol de tu propia cuenta.

---

## 4. Características transversales

- **Imágenes**: subida con validación de tipo/tamaño, nombres únicos (`svc_`, `prd_`, `gal_`, `team_`), almacenadas en `public/assets/uploads/<módulo>/`, se eliminan del disco al borrar el registro.
- **Ordenamiento**: todos los catálogos públicos respetan `sort_order` (mismo orden definido en el panel).
- **Mensajes flash**: éxito/error en la parte superior del panel.
- **Confirmación** antes de eliminar registros (JavaScript `confirm`).
- **Prevención de SQL Injection**: 100% sentencias preparadas (PDO).
- **Prevención XSS**: salida escapada con `htmlspecialchars` en todas las vistas.
- **Prevención CSRF**: token por sesión verificado en toda mutación.
- **Responsive**: panel con sidebar fija en desktop y menú móvil deslizante.

---

## 5. Módulos NO implementados (pendientes / futuros)

- Reserva de citas **desde el sitio público** (el CTA "Agendar ahora" aún es `#`).
- Facturación/impresión de recibo.
- Multi-sucursal / agenda por barbero con validación de choque de horarios.
- Notificaciones (email/SMS).
- Galería del admin con múltiples imágenes por corte.
