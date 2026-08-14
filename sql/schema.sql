-- ============================================================
-- BASE DE DATOS: Barbería Style
-- Descripción: Esquema completo y normalizado
-- ============================================================

CREATE DATABASE IF NOT EXISTS barberia_style
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE barberia_style;

-- ============================================================
-- TABLAS DE REFERENCIA (Catálogos)
-- ============================================================

-- Roles de usuarios del sistema
CREATE TABLE roles (
    id          TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(50)     NOT NULL UNIQUE,
    description VARCHAR(255)    NOT NULL,
    is_active   TINYINT(1)      DEFAULT 1,
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tipos de cita
CREATE TABLE appointment_types (
    id          TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(50)     NOT NULL UNIQUE,
    description VARCHAR(255),
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estados de cita
CREATE TABLE appointment_statuses (
    id           TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(50)     NOT NULL UNIQUE,
    description  VARCHAR(255),
    is_active    TINYINT(1)      DEFAULT 1,
    created_at   TIMESTAMP       DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categorías de servicios
CREATE TABLE service_categories (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)    NOT NULL UNIQUE,
    description TEXT,
    is_active   TINYINT(1)      DEFAULT 1,
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categorías de productos
CREATE TABLE product_categories (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)    NOT NULL UNIQUE,
    description TEXT,
    is_active   TINYINT(1)      DEFAULT 1,
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categorías de gastos
CREATE TABLE expense_categories (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)    NOT NULL UNIQUE,
    description TEXT,
    is_active   TINYINT(1)      DEFAULT 1,
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLAS PRINCIPALES
-- ============================================================

-- Usuarios del panel de administración
CREATE TABLE users (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id     TINYINT UNSIGNED NOT NULL,
    name        VARCHAR(100)     NOT NULL,
    email       VARCHAR(150)     NOT NULL UNIQUE,
    password    VARCHAR(255)     NOT NULL,
    avatar      VARCHAR(255),
    is_active   TINYINT(1)       DEFAULT 1,
    last_login  TIMESTAMP        NULL,
    created_at  TIMESTAMP        DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Servicios de barbería
CREATE TABLE services (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id   INT UNSIGNED,
    name          VARCHAR(100)     NOT NULL,
    description   TEXT,
    price         DECIMAL(10,2)    NOT NULL,
    duration      SMALLINT         DEFAULT 30,
    image         VARCHAR(255),
    is_active     TINYINT(1)       DEFAULT 1,
    sort_order    SMALLINT         DEFAULT 0,
    created_at    TIMESTAMP        DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES service_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Productos en venta
CREATE TABLE products (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id   INT UNSIGNED,
    name          VARCHAR(150)     NOT NULL,
    description   TEXT,
    price         DECIMAL(10,2)    NOT NULL,
    cost          DECIMAL(10,2)    DEFAULT 0,
    stock         INT              DEFAULT 0,
    min_stock     INT              DEFAULT 5,
    image         VARCHAR(255),
    is_active     TINYINT(1)       DEFAULT 1,
    sort_order    SMALLINT         DEFAULT 0,
    created_at    TIMESTAMP        DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES product_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Equipo (barberos)
CREATE TABLE team (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100)     NOT NULL,
    position      VARCHAR(100)     NOT NULL,
    description   TEXT,
    image         VARCHAR(255),
    is_active     TINYINT(1)       DEFAULT 1,
    sort_order    SMALLINT         DEFAULT 0,
    created_at    TIMESTAMP        DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Galería / Portafolio
CREATE TABLE gallery (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title         VARCHAR(100)     NOT NULL,
    image         VARCHAR(255)     NOT NULL,
    description   TEXT,
    is_active     TINYINT(1)       DEFAULT 1,
    sort_order    SMALLINT         DEFAULT 0,
    created_at    TIMESTAMP        DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Citas / Turnos
CREATE TABLE appointments (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type_id         TINYINT UNSIGNED NOT NULL,
    status_id       TINYINT UNSIGNED NOT NULL,
    client_name     VARCHAR(150)     NOT NULL,
    client_phone    VARCHAR(20),
    client_email    VARCHAR(150),
    appointment_date DATE            NOT NULL,
    appointment_time TIME            NOT NULL,
    notes           TEXT,
    subtotal        DECIMAL(10,2)    DEFAULT 0.00,
    total           DECIMAL(10,2)    DEFAULT 0.00,
    created_by      INT UNSIGNED,
    created_at      TIMESTAMP        DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (type_id)     REFERENCES appointment_types(id)     ON DELETE RESTRICT,
    FOREIGN KEY (status_id)   REFERENCES appointment_statuses(id)   ON DELETE RESTRICT,
    FOREIGN KEY (created_by)  REFERENCES users(id)                  ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Detalle de servicios en cada cita (historiza precios)
CREATE TABLE appointment_services (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT UNSIGNED NOT NULL,
    service_id    INT UNSIGNED NOT NULL,
    barber_id     INT UNSIGNED,
    price         DECIMAL(10,2)  NOT NULL,
    created_at    TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id)     REFERENCES services(id)     ON DELETE RESTRICT,
    FOREIGN KEY (barber_id)      REFERENCES team(id)         ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Detalle de productos vendidos en cada cita (historiza precios)
CREATE TABLE appointment_products (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT UNSIGNED NOT NULL,
    product_id    INT UNSIGNED NOT NULL,
    quantity      INT            DEFAULT 1,
    price         DECIMAL(10,2)  NOT NULL,
    created_at    TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id)     REFERENCES products(id)    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Gastos / Salidas de efectivo
CREATE TABLE expenses (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id     INT UNSIGNED,
    description     VARCHAR(255)     NOT NULL,
    amount          DECIMAL(10,2)    NOT NULL,
    expense_date    DATE             NOT NULL,
    payment_method  VARCHAR(50)      DEFAULT 'Efectivo',
    notes           TEXT,
    created_by      INT UNSIGNED,
    created_at      TIMESTAMP        DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id)  REFERENCES expense_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by)   REFERENCES users(id)              ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Configuración de la barbería (para la vista pública)
CREATE TABLE settings (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key   VARCHAR(100)     NOT NULL UNIQUE,
    setting_value TEXT,
    description   VARCHAR(255),
    created_at    TIMESTAMP        DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Redes sociales del sitio (iconos del footer)
CREATE TABLE social_links (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    platform    VARCHAR(30)     NOT NULL UNIQUE,
    url         VARCHAR(255)    NOT NULL DEFAULT '',
    sort_order  INT UNSIGNED    NOT NULL DEFAULT 0,
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DATOS SEMILLA (Seed Data)
-- ============================================================

-- Roles
INSERT INTO roles (name, description) VALUES
('Superadmin', 'Acceso completo al sistema: usuarios, configuración y finanzas'),
('Administrador', 'Gestión de citas, inventario, personal y gastos');

-- Tipos de cita
INSERT INTO appointment_types (name, description) VALUES
('Ahora', 'Atención inmediata - cliente en piso'),
('Programada', 'Cita con fecha y hora específica');

-- Estados de cita
INSERT INTO appointment_statuses (name, description) VALUES
('Pendiente', 'Cita pendiente de confirmación'),
('Confirmada', 'Cita confirmada por el cliente'),
('Completada', 'Servicio/producto entregado - contabilizada como ingreso'),
('Cancelada', 'Cita cancelada'),
('No asistió', 'Cliente no se presentó');

-- Configuración inicial
INSERT INTO settings (setting_key, setting_value, description) VALUES
('phone', '+503 0000-0000', 'Teléfono de contacto'),
('email', 'contacto@barberiastyle.com', 'Email de contacto'),
('address', 'Av. Principal 123, San Salvador', 'Dirección física'),
('latitude', '13.6929', 'Coordenadas para mapa'),
('longitude', '-89.2182', 'Coordenadas para mapa'),
('business_hours', '{"monday":{"open":"09:00","close":"20:00"},"tuesday":{"open":"09:00","close":"20:00"},"wednesday":{"open":"09:00","close":"20:00"},"thursday":{"open":"09:00","close":"20:00"},"friday":{"open":"09:00","close":"21:00"},"saturday":{"open":"10:00","close":"18:00"},"sunday":{"open":"","close":""}}', 'Horario de atención JSON'),
('tax_rate', '0', 'Impuesto (0%)'),
('currency', 'USD', 'Moneda del sistema'),
('whatsapp', '+503 0000-0000', 'Número de WhatsApp para link directo'),
('site_name', 'Barbería Style', 'Nombre del sitio / marca'),
('site_tagline', 'Estética masculina', 'Lema del sitio'),
('site_description', 'Calidad, precisión y estilo en cada corte. La barbería clásica con un toque moderno que marca la diferencia.', 'Descripción del sitio'),
('newsletter_title', 'Boletín', 'Título del bloque boletín'),
('newsletter_text', 'Recibe novedades, promociones y tips de estilo.', 'Texto del bloque boletín'),
('newsletter_enabled', '1', 'Mostrar bloque boletín (1=si, 0=no)');
