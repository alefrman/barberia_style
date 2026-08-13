# Barbería Style — Especificación Técnica (technical-spec.md)

Arquitectura, stack, estructura de código y base de datos del sistema.

---

## 1. Stack tecnológico

| Capa | Tecnología |
|---|---|
| Lenguaje | PHP >= 8.1 (tipado estricto `declare(strict_types=1)`) |
| Base de datos | MySQL / MariaDB, charset `utf8mb4` / collation `utf8mb4_unicode_ci` |
| Acceso a datos | **PDO** con sentencias preparadas obligatorias, `ATTR_EMULATE_PREPARES => false` |
| Framework | **Framework MVC propio** (sin dependencias externas de runtime) |
| Frontend | Tailwind CSS (CDN), Font Awesome 6.5.2, CSS propio, JavaScript vanilla |
| Lightbox | GLightbox vía CDN (jsdelivr) |
| Autoload | Composer (PSR-4: `App\` → `app/`) |
| Testing | PHPUnit instalado como dev-dependency; verificación manual mediante scripts CLI |

**Entorno actual**: XAMPP (Apache + MySQL) en Windows, accedido vía WSL. `APP_URL=http://localhost/barberia_style/public`.

---

## 2. Estructura de directorios

```
barberia_style/
├── .env                       # Variables de entorno (APP_URL, DB_*, SESSION_SECRET)
├── .htaccess                  # Bloqueo de .env y directorios privados (app, vendor, sql...)
├── composer.json              # PSR-4 autoload, PHP >= 8.1
├── app/
│   ├── Config/
│   │   ├── config.php         # Constantes globales (APP_URL, UPLOAD_DIR, CURRENCY...)
│   │   └── database.php       # Parámetros PDO (host, puerto, db, user, charset, opciones)
│   ├── Core/                  # Micro-framework
│   │   ├── Config.php         # Carga .env → $_ENV/putenv, acceso estático
│   │   ├── Controller.php     # Base: view(), viewRaw(), json(), redirect()
│   │   ├── Database.php       # Singleton PDO: execute/fetch/fetchAll/fetchValue + transacciones
│   │   ├── Model.php          # Active Record ligero (CRUD genérico)
│   │   ├── Request.php        # Abstracción HTTP (método, uri, query, body, files, headers)
│   │   ├── Response.php       # HTML/JSON/redirect/404 + send()
│   │   ├── Router.php         # Enrutador HTTP (GET/POST/PUT/PATCH/DELETE, {params}, middlewares)
│   │   └── View.php           # Motor de vistas con layouts + View::e() (escape XSS)
│   ├── Controllers/
│   │   ├── Admin/             # Auth, Dashboard, User, Appointment, Service, Team, Product, Expense, Gallery
│   │   └── Public/HomeController.php
│   ├── Helpers/
│   │   ├── Auth.php           # attempt/check/user/id/isRole/logout
│   │   ├── Money.php          # Formato USD: $1,234.56
│   │   └── Session.php        # start/set/get/flash/CSRF/regenerate/destroy
│   ├── Middleware/
│   │   ├── AuthMiddleware.php # Redirige al login si no hay sesión
│   │   └── RoleMiddleware.php # Restringe por rol (constructor recibe el rol)
│   ├── Models/                # 16 modelos (ver sección BD)
│   └── Views/
│       ├── layouts/admin.php  # Panel: sidebar + topbar + menú móvil + flashes
│       ├── layouts/public.php # Sitio: navbar + footer + reveal + scripts
│       ├── admin/             # auth, dashboard, appointments, users, services, products, team, expenses, gallery
│       └── public/home/       # index, services, products, team, gallery + partials
├── public/
│   ├── .htaccess              # Reescritura → index.php (excluye admin.php)
│   ├── index.php              # FRONT CONTROLLER del sitio público
│   ├── admin.php              # FRONT CONTROLLER del panel admin
│   ├── assets/
│   │   ├── css/style.css      # Componentes y animaciones propias
│   │   ├── js/gallery.js      # Inicializador GLightbox
│   │   └── uploads/{avatars,gallery,products,services,team}/
└── sql/schema.sql             # DDL + seed de roles, tipos, estados y settings
```

---

## 3. Front controllers

### 3.1 `public/index.php` (público)
1. Define `BASE_PATH`, carga Composer y configuración.
2. Crea `Router` con `basePath = parse_url(APP_URL, PHP_URL_PATH)`.
3. Registra rutas públicas: `/`, `/services`, `/products`, `/team`, `/gallery`.
4. Despacha y envía la respuesta.

### 3.2 `public/admin.php` (panel)
1. Igual que el público, pero inicia `Session::start()`.
2. Define `ADMIN_URL = APP_URL . '/admin.php'` y `basePath = parse_url(APP_URL, PHP_URL_PATH) . '/admin.php'`.
3. Registra rutas públicas de auth (`/login`) y el resto **protegidas** con `AuthMiddleware` (y `RoleMiddleware` para `/users*`).

### 3.3 Rewriting
- `public/.htaccess`: cualquier ruta inexistente → `index.php` (URLs limpias). Excluye `admin.php` (el panel se sirve directamente con su propio path).

---

## 4. Enrutador (`app/Core/Router.php`)

- Verbos: `GET`, `POST`, `PUT`, `PATCH`, `DELETE`.
- Parámetros dinámicos: `/services/edit/{id}`.
- Handlers: `'Controller@método'` o callable.
- Middlewares por ruta: `'Clase'` o `['Clase', [args...]]` (los args van al constructor).
- `normalizeUri()` elimina el `basePath` y normaliza `//`.
- `match()` compara segmento a segmento; no hay comodines múltiples ni orden por prioridad (itera en orden de registro).

---

## 5. Capa de datos

### 5.1 `Database` (Singleton PDO)
- DSN `mysql:host=...;port=...;dbname=...;charset=utf8mb4`.
- `ERRMODE_EXCEPTION`, `FETCH_ASSOC`, `EMULATE_PREPARES => false`, no persistente.
- API: `connect`, `execute` (rowCount), `fetch` (?array), `fetchAll`, `fetchValue`, `lastInsertId`, `beginTransaction`, `commit`, `rollBack`.

### 5.2 `Model` (Active Record ligero)
Los modelos concretos solo definen `protected string $table` y `protected array $fillable`.

| Método | Descripción |
|---|---|
| `all(orderBy, direction)` | Todas las filas. |
| `find(id)` | Por llave primaria → `?static`. |
| `where(assoc)` | WHERE con igualdades (placeholders `:w_columna`). |
| `whereFirst(assoc)` | Primer registro o null. |
| `count(where=[])` | Conteo. |
| `create(assoc)` | INSERT; devuelve instancia con ID. Auto `created_at`/`updated_at` si `$timestamps`. |
| `save()` | UPDATE del registro cargado (debe existir el ID). |
| `updateWhere(where, attrs)` | UPDATE masivo por condición. |
| `delete()` | DELETE del registro cargado. |
| `deleteWhere(where)` | DELETE masivo. |

> **Nota**: `save()` es solo UPDATE. Para insertar usar siempre `create()`.

### 5.3 Modelos (16)

| Modelo | Tabla |
|---|---|
| `Role` | `roles` |
| `User` | `users` (+ métodos `isRole`, `roleName`) |
| `Appointment` | `appointments` |
| `AppointmentType` | `appointment_types` |
| `AppointmentStatus` | `appointment_statuses` |
| `AppointmentService` | `appointment_services` |
| `AppointmentProduct` | `appointment_products` |
| `Service` / `ServiceCategory` | `services` / `service_categories` |
| `Product` / `ProductCategory` | `products` / `product_categories` |
| `Team` | `team` |
| `Gallery` | `gallery` |
| `Expense` / `ExpenseCategory` | `expenses` / `expense_categories` |
| `Setting` | `settings` |

---

## 6. Base de datos

### 6.1 Tablas (16)

**Catálogos (6)**: `roles`, `appointment_types`, `appointment_statuses`, `service_categories`, `product_categories`, `expense_categories`.

**Principales (10)**:
- `users` — cuenta de acceso al panel (`role_id` FK → roles RESTRICT).
- `services` — catálogo de cortes (categoría FK SET NULL, precio, duración, imagen, activo, orden).
- `products` — inventario (categoría FK SET NULL, precio/costo, stock/min_stock, imagen).
- `team` — barberos.
- `gallery` — fotos del portafolio.
- `appointments` — cabecera de cita (tipo, estado, cliente, fecha/hora, subtotal/total, `created_by`).
- `appointment_services` — servicios de la cita (historiza precio, `barber_id` opcional). FK: appointment CASCADE, service RESTRICT, barber SET NULL.
- `appointment_products` — productos de la cita (historiza precio y cantidad). FK: appointment CASCADE, product RESTRICT.
- `expenses` — gastos (categoría FK SET NULL, método de pago, `created_by`).
- `settings` — configuración clave/valor para la vista pública.

### 6.2 Integridad referencial clave

| Regla | Efecto |
|---|---|
| `appointment_services.service_id → services` (RESTRICT) | No se puede borrar un servicio usado en citas. |
| `appointment_products.product_id → products` (RESTRICT) | No se puede borrar un producto vendido. |
| Detalles de cita → `appointments` (CASCADE) | Borrar la cita borra sus detalles. |
| Categorías de servicios/productos/gastos | SET NULL al borrar. |
| `users.role_id → roles` | RESTRICT. |

### 6.3 Seed (datos iniciales)
- Roles: `Superadmin`, `Administrador`.
- Tipos de cita: `Ahora`, `Programada`.
- Estados de cita: `Pendiente`, `Confirmada`, `Completada`, `Cancelada`, `No asistió`.
- Settings: teléfono, email, dirección, latitud/longitud, horario JSON, `tax_rate`, `currency`.
- **Moneda y localización coherentes**: `currency = USD`, teléfono `+503 0000-0000`, dirección en San Salvador y `TIMEZONE = America/El_Salvador` (config.php). El seed de `schema.sql` y la BD en vivo ya coinciden.

---

## 7. Request / Response

### 7.1 `Request`
- `createFromGlobals()`: método (con soporte `_method` para PUT/PATCH/DELETE en formularios), body (`$_POST` + JSON de `php://input`), `$_GET`, `$_FILES`, headers `HTTP_*`.
- `input(key, default)` → **body primero, luego query** (comportamiento unificado para GET y POST).
- `query()`, `all()`, `has()`, `file()`, `header()`, `wantsJson()`.

### 7.2 `Response`
- `make`, `json`, `redirect`, `notFound`, `send()`.
- `send()` **no hace `exit`** (los middlewares sí, al redirigir).

---

## 8. Autenticación y seguridad

| Control | Implementación |
|---|---|
| Hash de contraseñas | `password_hash(PASSWORD_BCRYPT)` / `password_verify` |
| Sesión | Nombre `barberia_style_session`, `HttpOnly`, `SameSite=Lax`, `use_strict_mode` |
| Session fixation | `session_regenerate_id(true)` tras login (`Auth::attempt`) |
| CSRF | Token de 32 bytes en sesión; `verifyCsrf` con `hash_equals`; presente en todos los formularios de mutación |
| XSS | `View::e()` (htmlspecialchars ENT_QUOTES) en toda salida |
| SQL Injection | Solo PDO preparado; placeholders nombrados (sin emular → no repetir el mismo nombre) |
| Upload | Validación de MIME (`getimagesize`), extensión permitida, tamaño ≤ 2 MB, nombre aleatorio, `move_uploaded_file` con fallback `rename` (para entornos CLI) |
| Autorización | `AuthMiddleware` global del panel + `RoleMiddleware` por módulo |

---

## 9. Subida y almacenamiento de imágenes

- Constantes: `UPLOAD_DIR` (URL pública) y `UPLOAD_PATH` (ruta del sistema `public/assets/uploads/`).
- En BD se guarda la **ruta relativa con prefijo de módulo**: `services/<archivo>`, `products/<archivo>`, `gallery/<archivo>`, `team/<archivo>`. Las vistas construyen la URL como `UPLOAD_DIR . $image`.
- Prefijos de nombre: `svc_`, `prd_`, `gal_`, `team_` + fecha/hora + hex aleatorio.
- Al reemplazar o eliminar se borra el archivo anterior del disco (`deleteImageFile`).

---

## 10. Frontend

- **Tailwind CDN** con `tailwind.config` (colores y tipografía personalizados) en ambos layouts.
- **Font Awesome 6.5.2** CDN.
- **GLightbox** (CSS + JS) solo en el layout público; inicializado desde `assets/js/gallery.js` con `loop`, `zoomable`, `touchNavigation`, `draggable`.
- **JavaScript vanilla**:
  - Navbar con sombra al hacer scroll.
  - Menú móvil (público y admin).
  - `IntersectionObserver` para animaciones `reveal`.
  - Preview de imagen con `FileReader` en formularios.
  - Formulario de citas: agregar/eliminar filas de servicio/producto y recalcular totales (`updateTotals`).
  - `confirm()` antes de eliminar.

---

## 11. Validación de configuración (`.env`)

| Variable | Default | Uso |
|---|---|---|
| `APP_ENV` | `development` | Controla `APP_DEBUG` (muestra errores en dev) |
| `APP_URL` | `http://localhost` | URLs absolutas, base path del router, `ADMIN_URL`, `UPLOAD_DIR` |
| `DB_HOST/DB_PORT/DB_NAME/DB_USER/DB_PASS/DB_CHARSET` | `127.0.0.1/3306/-/root//utf8mb4` | Conexión PDO |
| `SESSION_SECRET` | default | Firma/contexto de sesión |

---

## 12. Limitaciones técnicas conocidas

- Sin validación de choque de horarios entre citas (agenda por barbero no bloquea solapamientos).
- El listado de citas está limitado a `LIMIT 500`.
- `Gallery::count` etc. dependen de `Model::where` (sin ORDER BY); el orden público se aplica con `usort` en el controlador.
- Placeholders PDO nombrados no pueden repetirse en una misma consulta (hay que usar nombres distintos, ej. `:q` y `:q2`).
- No hay sistema de migraciones: el esquema se aplica con `sql/schema.sql` directamente.
