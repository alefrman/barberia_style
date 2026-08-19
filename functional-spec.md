# Barbería Style — Especificación Funcional (functional-spec.md)

Requisitos funcionales, reglas de negocio y validaciones de cada módulo.

---

## 0. Actores y roles

| Rol | Permisos |
|---|---|
| **Superadmin** | Todo el panel: citas, inventario, servicios, barberos, gastos, galería y **usuarios**. |
| **Administrador** | Todo excepto el módulo de **Usuarios** (el enlace ni se muestra). |

- El módulo de Usuarios está protegido por `RoleMiddleware(['Superadmin'])`.
- El primer usuario del sistema (superadmin) se crea vía seed de BD.

---

## 1. Autenticación

**Reglas**
- El login requiere email + contraseña (ambos obligatorios).
- Token CSRF obligatorio en el POST de login.
- Usuario inactivo no puede iniciar sesión (login rechazado con mensaje genérico).
- Credenciales incorrectas → mensaje único: *"Credenciales incorrectas o usuario inactivo."* (no revela cuál falló).
- Tras login se regenera la sesión y se actualiza `last_login`.
- Sin sesión activa, cualquier ruta protegida redirige a `/login` con flash *"Debes iniciar sesión para acceder al panel."*.
- Logout limpia la sesión y redirige al login.

---

## 2. Dashboard

- Tarjetas: **Citas de hoy**, **Servicios** (total), **Productos** (total), **Gastos** (total).
- Bienvenida con nombre y rol del usuario.

### 2.1 KPIs financieros (mes en curso)

| KPI | Definición |
|---|---|
| Ingresos del mes | Suma de `appointments.total` de citas con estado **"Completada"** en el mes actual. |
| Gastos del mes | Suma de `expenses.amount` del mes actual. |
| Ganancia neta | Ingresos del mes − gastos del mes (se pinta en rojo si es negativa). |
| Ticket promedio | Ingresos del mes ÷ número de citas completadas del mes. |

### 2.2 Gráficas (Chart.js por CDN)

1. **Barras — Ingresos vs Gastos**: desde el **primer mes con datos** hasta el mes actual, series mensuales (meses sin datos se completan con 0).
2. **Línea — Ganancia neta mensual**: ingresos − gastos por mes (mismo rango dinámico).
3. **Dona — Gastos por categoría**: total por `expense_categories` del mes actual ("Sin categoría" para gastos sin categoría).
4. **Dona — Gastos por método de pago**: total por `payment_method` del mes actual.
5. **Barras — Estados de cita**: conteo de **Completada / No asistió / Cancelada** con **filtro**:
   - `?period=month` (default): mes calendario actual.
   - `?period=week`: semana actual (lunes a domingo).
   - El valor inválido cae a `month`.
6. **Barras horizontales — Top productos más vendidos**: top 10 por unidades vendidas en citas **Completadas** (todo el histórico), con el valor al final de cada barra y badge con el **total de unidades vendidas**. El **tooltip** muestra el **monto de ventas** en `$` (`SUM(precio × cantidad)` por producto).

> **Regla financiera**: solo las citas **"Completada"** generan ingreso. Cancelada y No asistió no suman.

---

## 3. Citas (turnos)

### 3.1 Listado
- Orden: fecha desc, hora desc. Límite: 500 registros.
- **Filtros combinables**: estado (`status_id`), rango de fechas (`date_from`/`date_to`), búsqueda (`q` en nombre/teléfono/email).
- Tarjetas de conteo: hoy, pendientes, confirmadas, completadas, total.

### 3.2 Crear / Editar
**Campos de cabecera**
| Campo | Regla |
|---|---|
| Tipo | Obligatorio, debe existir en `appointment_types` |
| Estado | Obligatorio, debe existir en `appointment_statuses` |
| Nombre del cliente | Obligatorio, máx 150 |
| Teléfono | Opcional; si va, formato `+503 0000-0000` |
| Email | Opcional; si va, email válido |
| Fecha | Obligatoria, formato `YYYY-MM-DD` |
| Hora | Obligatoria, formato `HH:MM` |
| Notas | Opcional |

**Detalle (líneas)**
- Al menos **un servicio o un producto** es obligatorio.
- Servicios: uno o más, cada uno con **barbero** opcional. Se cobra el precio vigente del servicio.
- Productos: uno o más, cada uno con cantidad (1–99). Se cobra `precio × cantidad`.
- **Totales**: `subtotal = total = Σ servicios + Σ (productos)` calculados desde BD.

**Reglas de inventario**
- Al guardar se descuenta stock de cada producto.
- Si la cantidad pedida supera el stock disponible, se limita a lo disponible (0 → se omite el producto).
- Al **editar**: se restaura el stock previo y se vuelve a descontar con los nuevos datos.
- Al **eliminar**: se restaura el stock de los productos de la cita.

**Transaccionalidad**
- Toda la operación (cabecera + detalles + stock + totales) se ejecuta en una transacción; si algo falla, se revierte todo.

**Reglas de agenda (choque de horarios por barbero)**
- La ocupación de un barbero en una cita = **suma de la duración** (`services.duration`) de los servicios que se le asignan (inicia en `appointment_time`).
- Solo bloquean las citas con estado **Pendiente** o **Confirmada**; Completada, Cancelada y No asistió no ocupan agenda.
- Servicios sin barbero no bloquean a nadie.
- Al guardar (crear/editar), el servidor detecta solapamientos: cita nueva en `[inicio, inicio + Σ duraciones)` vs citas existentes del mismo día y barbero. Si choca, se muestra el error *"El barbero X ya está ocupado el … (cita #N)"* y **el formulario se re-renderiza conservando los datos capturados** para poder cambiar de barbero/hora.
- El mensaje sugiere la **próxima hora libre** del barbero (franja de 15 min que quepa completa, dentro del horario de atención y tras `ahora+1h` si es hoy): *"… Cambia la hora o el barbero, o agenda a partir de las HH:MM."* Si el barbero está lleno ese día, omite la sugerencia.
- Al **editar**, la cita actual queda excluida del chequeo (`exclude_id`).
- **Verificación en vivo (AJAX)**: el formulario consulta `POST /appointments/availability` (JSON + CSRF) al cambiar fecha/hora/barbero/servicio (debounce 400 ms); marca en rojo los barberos ocupados con su mensaje y un **botón "Usar HH:MM"** que selecciona la hora sugerida y re-verifica, muestra un banner de resumen con "(disponible a partir de las HH:MM)" y **bloquea el envío** mientras haya conflictos. El servidor es la validación definitiva.

### 3.3 Detalle (`/show/{id}`)
- Muestra cabecera (con nombres de tipo/estado), lista de servicios (con barbero) y productos (con cantidades), total y usuario creador.

### 3.4 Eliminar
- Con `confirm` del navegador. Borra detalles por CASCADE y restaura stock.

---

## 4. Servicios

### 4.1 Campos y validación
| Campo | Regla |
|---|---|
| Categoría | Opcional; si se elige debe existir. Soporta **creación inline** |
| Nombre | Obligatorio, máx 100 |
| Descripción | Opcional |
| Precio (USD) | ≥ 0 |
| Duración (min) | Entero, rango 0–600 (default 30) |
| Orden | Entero ≥ 0 (default 0) |
| Activo | Checkbox (default activo) |
| Imagen | Opcional; JPG/PNG/WEBP/GIF, ≤ 2 MB |

### 4.2 Reglas de negocio
- Los servicios **activos** y ordenados por `sort_order` aparecen en el sitio público (`/` y `/services`).
- Al eliminar: si está referenciado en `appointment_services` (citado), se **bloquea** con el mensaje *"No se puede eliminar: el servicio está asociado a una o más citas."*. Se puede desactivar en su lugar.

### 4.3 Categorías inline
- En el formulario, el botón **"Nueva"** despliega un campo para escribir el nombre; al guardar se crea la categoría (o se reutiliza si ya existe con ese nombre) y se asigna.

---

## 5. Inventario (productos)

### 5.1 Campos y validación
| Campo | Regla |
|---|---|
| Categoría | Opcional; validada + creación inline |
| Nombre | Obligatorio, máx 150 |
| Descripción | Opcional |
| Precio (USD) | ≥ 0 |
| Costo (USD) | ≥ 0 |
| Stock | ≥ 0 (default 0) |
| Stock mínimo | ≥ 0 (default 5) |
| Orden | ≥ 0 |
| Activo | Checkbox |
| Imagen | Opcional; JPG/PNG/WEBP/GIF, ≤ 2 MB |

### 5.2 Filtros y resumen
- Filtros: búsqueda (`q` en nombre/descripción), categoría, estado de stock (**bajo** = `stock <= min_stock`, **agotado** = `stock <= 0`).
- Tarjetas de resumen: total de productos, unidades en stock, con stock bajo, agotados y **ganancia pendiente** total (`Σ (precio − costo) × stock`).

### 5.3 Historial de movimientos
- Cada producto tiene un historial (`/inventory/{id}/movements`) con los movimientos de stock en orden cronológico inverso.
- **Se registra automáticamente** al crear el producto (tipo `creation`) y al editar el stock desde el formulario (tipo `edit`, solo si cambia).
- **Reponer stock**: botón en la página del historial (POST `/inventory/{id}/restock`) que suma unidades (cantidad ≥ 1, nota opcional) y registra un movimiento tipo `restock`.
- Cada movimiento guarda: tipo, cantidad (con signo), stock antes → después, nota, usuario que lo registró (`created_by`) y fecha.
- **Ganancia pendiente** por producto = `(precio − costo) × stock`; si no hay costo se toma como $0.

### 5.4 Reglas de negocio
- El stock se modifica **automáticamente** al crear/editar/eliminar citas (ver módulo Citas).
- Si el producto está en alguna cita, la eliminación se bloquea (FK RESTRICT) con mensaje amigable.
- Los productos **activos** ordenados por `sort_order` se muestran en `/` (preview 4) y `/products`.

---

## 6. Barberos (equipo)

| Campo | Regla |
|---|---|
| Nombre | Obligatorio, máx 100 |
| Cargo | Obligatorio, máx 100 |
| Descripción | Opcional |
| Orden | ≥ 0 |
| Activo | Checkbox |
| Imagen | Opcional; JPG/PNG/WEBP/GIF, ≤ 2 MB |

- Los barberos **activos** ordenados por `sort_order` aparecen en `/` y `/team`.
- Se pueden asignar como `barber_id` en los servicios de una cita. Al eliminarse un barbero con historial, el detalle conserva el precio y queda `barber_id = NULL` (SET NULL).

---

## 7. Gastos

### 7.1 Campos y validación
| Campo | Regla |
|---|---|
| Categoría | Opcional; validada + creación inline |
| Descripción | Obligatoria, máx 255 |
| Monto (USD) | **> 0** |
| Fecha | Obligatoria, formato `YYYY-MM-DD` |
| Método de pago | `Efectivo`, `Tarjeta`, `Transferencia` u `Otro` |
| Notas | Opcional |

### 7.2 Comportamiento
- `created_by` se registra automáticamente con el usuario autenticado (SET NULL si el usuario se borra).
- **Filtros**: búsqueda (descripción/notas), categoría, rango de fechas (`from`/`to`).
- **Resumen**: gastos de hoy, total del mes, cantidad de gastos del mes y total acumulado histórico.
- Orden del listado: fecha desc, id desc.

---

## 8. Galería (portafolio)

### 8.1 Campos y validación
| Campo | Regla |
|---|---|
| Título | Obligatorio, máx 100 |
| Descripción | Opcional (se muestra en overlay y en el lightbox) |
| Orden | ≥ 0 |
| Activo | Checkbox |
| Imagen | Opcional (si falta, el público muestra placeholder); JPG/PNG/WEBP/GIF, ≤ 2 MB |

### 8.2 Reglas de negocio
- Las fotos **activas** ordenadas por `sort_order` se muestran en `/` (preview 4) y `/gallery`.
- La galería pública usa **lightbox**: clic → ampliación con título y descripción.
- Al eliminar una foto se borra también el archivo del disco.
- Si una foto no tiene título, el sitio usa el texto alternativo genérico *"Trabajo de Barbería Style"*.

---

## 9. Usuarios (solo Superadmin)

| Campo | Regla |
|---|---|
| Nombre | Obligatorio, máx 100 |
| Email | Obligatorio, **único** (validado al crear y editar, excluyendo el propio registro) |
| Rol | Obligatorio, debe existir |
| Contraseña | Al crear: obligatoria, mín 6. Al editar: opcional (si se deja vacía no cambia) |
| Activo | Checkbox |

**Reglas de protección**
- No puedes eliminar tu propia cuenta.
- No puedes desactivar ni cambiar el rol de tu propia cuenta (*"No puedes desactivar ni cambiar el rol de tu propia cuenta."*).
- Contraseñas siempre guardadas con bcrypt.

---

## 10. Configuración del sitio (settings)

- La vista pública lee de `settings`: teléfono, email y dirección (footer y CTA).
- Horarios, impuestos y moneda están definidos en el seed (JSON en `business_hours`), actualmente sin UI de edición en el panel.

---

## 11. Reglas de validación transversales

| Concepto | Regla |
|---|---|
| Formato de fechas | Siempre `YYYY-MM-DD` |
| Formato de horas | Siempre `HH:MM` |
| Teléfono | `+503 0000-0000` (cuando aplica) |
| Moneda | USD (helper `Money::format`: `$1,234.56`) |
| Orden (`sort_order`) | Entero ≥ 0; menor = primero en público |
| Upload de imagen | Formatos `jpg/jpeg/png/webp/gif`, ≤ 2 MB, validación de imagen real (`getimagesize`) |
| Mutaciones | Todas requieren token CSRF válido; falla → *"Token de seguridad inválido."* |
| CSRF inválido en login | *"Token de seguridad inválido. Intenta nuevamente."* |

---

## 12. Flujo de errores y feedback

- **Errores de validación**: flash de error con el primer mensaje y redirección de vuelta al formulario.
- **Éxito**: flash de éxito con mensaje descriptivo.
- **404**: `Response::notFound()` con mensaje *"Recurso no encontrado."*.
- **403**: `RoleMiddleware` → *"No tienes permisos para acceder a esta sección."* y redirección al dashboard.
- **Eliminación bloqueada por FK**: mensaje específico de la dependencia.

---

## 13. Requisitos futuros (fuera de alcance actual)

1. Reserva en línea desde el sitio público.
2. Impresión de recibos.
3. Notificaciones por email/SMS.
4. Edición de `settings` (horarios, impuesto, contacto) desde el panel.
