-- Punto de venta / Facturas
CREATE TABLE invoices (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_number   VARCHAR(20)      NOT NULL UNIQUE,
    client_name      VARCHAR(150)     NOT NULL,
    client_phone     VARCHAR(20),
    payment_method   VARCHAR(50)      DEFAULT 'Efectivo',
    subtotal         DECIMAL(10,2)    DEFAULT 0,
    total            DECIMAL(10,2)    DEFAULT 0,
    notes            TEXT,
    created_by       INT UNSIGNED,
    created_at       TIMESTAMP        DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE invoice_products (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id    INT UNSIGNED   NOT NULL,
    product_id    INT UNSIGNED   NOT NULL,
    quantity      INT            DEFAULT 1,
    price         DECIMAL(10,2)  NOT NULL,
    created_at    TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id)     ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)     ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings de numeración de facturas
INSERT INTO settings (setting_key, setting_value, description) VALUES
('invoice_next_number', '1', 'Próximo número de factura (auto-increment)'),
('invoice_prefix', 'FAC', 'Prefijo de numeración de facturas');
