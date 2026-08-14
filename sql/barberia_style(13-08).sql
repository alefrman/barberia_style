-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 13-08-2026 a las 22:24:34
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `barberia_style`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `appointments`
--

CREATE TABLE `appointments` (
  `id` int(10) UNSIGNED NOT NULL,
  `type_id` tinyint(3) UNSIGNED NOT NULL,
  `status_id` tinyint(3) UNSIGNED NOT NULL,
  `client_name` varchar(150) NOT NULL,
  `client_phone` varchar(20) DEFAULT NULL,
  `client_email` varchar(150) DEFAULT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `notes` text DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) DEFAULT 0.00,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `appointments`
--

INSERT INTO `appointments` (`id`, `type_id`, `status_id`, `client_name`, `client_phone`, `client_email`, `appointment_date`, `appointment_time`, `notes`, `subtotal`, `total`, `created_by`, `created_at`, `updated_at`) VALUES
(6, 1, 3, 'Alejandro Nuila', '+503 7823-7782', '', '2026-08-13', '14:53:00', '', 8.50, 8.50, 1, '2026-08-13 20:53:31', '2026-08-13 20:54:10'),
(8, 1, 3, 'Prueba', '', '', '2026-08-13', '15:04:00', '', 14.45, 14.45, 4, '2026-08-13 21:05:03', '2026-08-13 21:06:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `appointment_products`
--

CREATE TABLE `appointment_products` (
  `id` int(10) UNSIGNED NOT NULL,
  `appointment_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `appointment_products`
--

INSERT INTO `appointment_products` (`id`, `appointment_id`, `product_id`, `quantity`, `price`, `created_at`) VALUES
(7, 8, 11, 1, 5.95, '2026-08-13 19:06:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `appointment_services`
--

CREATE TABLE `appointment_services` (
  `id` int(10) UNSIGNED NOT NULL,
  `appointment_id` int(10) UNSIGNED NOT NULL,
  `service_id` int(10) UNSIGNED NOT NULL,
  `barber_id` int(10) UNSIGNED DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `appointment_services`
--

INSERT INTO `appointment_services` (`id`, `appointment_id`, `service_id`, `barber_id`, `price`, `created_at`) VALUES
(7, 6, 9, 10, 8.50, '2026-08-13 18:54:10'),
(11, 8, 9, 10, 8.50, '2026-08-13 19:06:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `appointment_statuses`
--

CREATE TABLE `appointment_statuses` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `appointment_statuses`
--

INSERT INTO `appointment_statuses` (`id`, `name`, `description`, `is_active`, `created_at`) VALUES
(1, 'Pendiente', 'Cita pendiente de confirmación', 1, '2026-08-13 16:07:27'),
(2, 'Confirmada', 'Cita confirmada por el cliente', 1, '2026-08-13 16:07:27'),
(3, 'Completada', 'Servicio/producto entregado - contabilizada como ingreso', 1, '2026-08-13 16:07:27'),
(4, 'Cancelada', 'Cita cancelada', 1, '2026-08-13 16:07:27'),
(5, 'No asistió', 'Cliente no se presentó', 1, '2026-08-13 16:07:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `appointment_types`
--

CREATE TABLE `appointment_types` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `appointment_types`
--

INSERT INTO `appointment_types` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Ahora', 'Atención inmediata - cliente en piso', '2026-08-13 16:07:27'),
(2, 'Programada', 'Cita con fecha y hora específica', '2026-08-13 16:07:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `expenses`
--

CREATE TABLE `expenses` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `expense_date` date NOT NULL,
  `payment_method` varchar(50) DEFAULT 'Efectivo',
  `notes` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `expenses`
--

INSERT INTO `expenses` (`id`, `category_id`, `description`, `amount`, `expense_date`, `payment_method`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(2, 2, 'Compra de Cera', 10.00, '2026-08-13', 'Efectivo', '', 4, '2026-08-13 21:11:09', '2026-08-13 21:11:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `expense_categories`
--

CREATE TABLE `expense_categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `expense_categories`
--

INSERT INTO `expense_categories` (`id`, `name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 'Servicios', NULL, 1, '2026-08-13 21:11:09', '2026-08-13 21:11:09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gallery`
--

CREATE TABLE `gallery` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(100) NOT NULL,
  `image` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` smallint(6) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `gallery`
--

INSERT INTO `gallery` (`id`, `title`, `image`, `description`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(5, 'Corte degradado', 'gallery/corte1.webp', 'Degradado clásico con línea marcada.', 1, 1, '2026-08-13 19:34:04', '2026-08-13 19:34:04'),
(6, 'Corte + barba', 'gallery/corte2.webp', 'Corte texturizado con perfilado de barba.', 1, 2, '2026-08-13 19:34:04', '2026-08-13 19:34:04'),
(7, 'Corte degradado', 'gallery/gal_20260813_153602_1bca0743.webp', 'Corte degradado', 1, 3, '2026-08-13 21:36:02', '2026-08-13 21:36:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `products`
--

CREATE TABLE `products` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `cost` decimal(10,2) DEFAULT 0.00,
  `stock` int(11) DEFAULT 0,
  `min_stock` int(11) DEFAULT 5,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` smallint(6) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `cost`, `stock`, `min_stock`, `image`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(11, 2, 'Cera  BarberTime', 'Cera  BarberTimeCera  BarberTime', 5.95, 3.00, 4, 1, 'products/prd_20260813_150345_b27956d2.webp', 1, 0, '2026-08-13 21:03:46', '2026-08-13 21:06:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `product_categories`
--

CREATE TABLE `product_categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `product_categories`
--

INSERT INTO `product_categories` (`id`, `name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 'Cera para cabello', NULL, 1, '2026-08-13 21:03:45', '2026-08-13 21:03:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Superadmin', 'Acceso completo al sistema: usuarios, configuración y finanzas', 1, '2026-08-13 16:07:27', '2026-08-13 16:07:27'),
(2, 'Administrador', 'Gestión de citas, inventario, personal y gastos', 1, '2026-08-13 16:07:27', '2026-08-13 16:07:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `services`
--

CREATE TABLE `services` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration` smallint(6) DEFAULT 30,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` smallint(6) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `services`
--

INSERT INTO `services` (`id`, `category_id`, `name`, `description`, `price`, `duration`, `image`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(9, 3, 'Corte clasico', 'Corte clasico', 8.50, 40, 'services/svc_20260813_143236_350c62a1.webp', 1, 0, '2026-08-13 20:28:19', '2026-08-13 18:40:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `service_categories`
--

CREATE TABLE `service_categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `service_categories`
--

INSERT INTO `service_categories` (`id`, `name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(3, 'Corte de cabello', NULL, 1, '2026-08-13 20:28:19', '2026-08-13 20:28:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `settings`
--

CREATE TABLE `settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `description`, `created_at`, `updated_at`) VALUES
(1, 'phone', '+503 0000-0000', 'Teléfono de contacto', '2026-08-13 16:07:27', '2026-08-13 19:54:09'),
(2, 'email', 'contacto@barberiastyle.com', 'Email de contacto', '2026-08-13 16:07:27', '2026-08-13 19:54:09'),
(3, 'address', 'Av. Principal 123, San Salvador', 'Dirección física', '2026-08-13 16:07:27', '2026-08-13 19:54:09'),
(4, 'latitude', '13.6929', 'Coordenadas para mapa', '2026-08-13 16:07:27', '2026-08-13 19:54:09'),
(5, 'longitude', '-89.2182', 'Coordenadas para mapa', '2026-08-13 16:07:27', '2026-08-13 19:54:09'),
(6, 'business_hours', '{\"monday\":{\"open\":\"09:00\",\"close\":\"20:00\"},\"tuesday\":{\"open\":\"09:00\",\"close\":\"20:00\"},\"wednesday\":{\"open\":\"09:00\",\"close\":\"20:00\"},\"thursday\":{\"open\":\"09:00\",\"close\":\"20:00\"},\"friday\":{\"open\":\"09:00\",\"close\":\"21:00\"},\"saturday\":{\"open\":\"10:00\",\"close\":\"18:00\"},\"sunday\":{\"open\":\"\",\"close\":\"\"}}', 'Horario de atención JSON', '2026-08-13 16:07:27', '2026-08-13 16:07:27'),
(7, 'tax_rate', '0', 'IVA/Impuesto (9%)', '2026-08-13 16:07:27', '2026-08-13 19:54:09'),
(8, 'currency', 'USD', 'Moneda del sistema', '2026-08-13 16:07:27', '2026-08-13 19:54:09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `team`
--

CREATE TABLE `team` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `position` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` smallint(6) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `team`
--

INSERT INTO `team` (`id`, `name`, `position`, `description`, `image`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(10, 'Barbero Uno', 'Barbero Maestro', 'descripciondescripciondescripcion descripciondescripcion descripcion', 'team/team_20260813_145038_3cf00709.webp', 1, 0, '2026-08-13 20:50:38', '2026-08-13 20:51:31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `role_id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `role_id`, `name`, `email`, `password`, `avatar`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 1, 'Alejandro Nuila', 'superadmin@barberiastyle.cl', '$2y$10$1HkQdNWrWV.0Y7zUHZwz4.nU9iteCKHi8XVWRW6TLJc.fLgunwM0K', NULL, 1, '2026-08-13 20:10:19', '2026-08-13 17:08:10', '2026-08-13 20:10:19'),
(4, 2, 'Nuila Admin', 'na@gmail.com', '$2y$10$9e4ivGgg15TRCP7TGcNYwOf6SzR3NBCVHFaCmeu//I3I0lBmWWDAu', NULL, 1, '2026-08-13 19:35:04', '2026-08-13 19:20:25', '2026-08-13 19:35:04');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type_id` (`type_id`),
  ADD KEY `status_id` (`status_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indices de la tabla `appointment_products`
--
ALTER TABLE `appointment_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appointment_id` (`appointment_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indices de la tabla `appointment_services`
--
ALTER TABLE `appointment_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appointment_id` (`appointment_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `barber_id` (`barber_id`);

--
-- Indices de la tabla `appointment_statuses`
--
ALTER TABLE `appointment_statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indices de la tabla `appointment_types`
--
ALTER TABLE `appointment_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indices de la tabla `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indices de la tabla `expense_categories`
--
ALTER TABLE `expense_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indices de la tabla `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indices de la tabla `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indices de la tabla `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indices de la tabla `service_categories`
--
ALTER TABLE `service_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indices de la tabla `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indices de la tabla `team`
--
ALTER TABLE `team`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `appointment_products`
--
ALTER TABLE `appointment_products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `appointment_services`
--
ALTER TABLE `appointment_services`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `appointment_statuses`
--
ALTER TABLE `appointment_statuses`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `appointment_types`
--
ALTER TABLE `appointment_types`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `expense_categories`
--
ALTER TABLE `expense_categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `products`
--
ALTER TABLE `products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `services`
--
ALTER TABLE `services`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `service_categories`
--
ALTER TABLE `service_categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `team`
--
ALTER TABLE `team`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `appointment_types` (`id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`status_id`) REFERENCES `appointment_statuses` (`id`),
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `appointment_products`
--
ALTER TABLE `appointment_products`
  ADD CONSTRAINT `appointment_products_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointment_products_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Filtros para la tabla `appointment_services`
--
ALTER TABLE `appointment_services`
  ADD CONSTRAINT `appointment_services_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointment_services_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  ADD CONSTRAINT `appointment_services_ibfk_3` FOREIGN KEY (`barber_id`) REFERENCES `team` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `expense_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `expenses_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
