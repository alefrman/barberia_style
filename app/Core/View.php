<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * View
 *
 * Motor de vistas simple con layouts.
 * Las vistas son archivos PHP en app/Views/ que reciben datos como variables.
 */
class View
{
    /**
     * Renderiza una vista dentro de un layout.
     *
     * @param string $view   Ruta relativa a app/Views sin extensión. Ej: 'public/home/index'
     * @param array  $data   Variables disponibles en la vista.
     * @param string $layout Layout a usar (null = sin layout).
     */
    public static function render(string $view, array $data = [], ?string $layout = null): string
    {
        $viewPath = BASE_PATH . '/app/Views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            throw new RuntimeException("Vista no encontrada: {$view}");
        }

        // Extrae los datos como variables locales
        extract($data, EXTR_SKIP);

        ob_start();
        include $viewPath;
        $content = ob_get_clean();

        if ($layout === null) {
            return $content;
        }

        $layoutPath = BASE_PATH . '/app/Views/layouts/' . $layout . '.php';
        if (!file_exists($layoutPath)) {
            throw new RuntimeException("Layout no encontrado: {$layout}");
        }

        ob_start();
        include $layoutPath;
        return (string) ob_get_clean();
    }

    /**
     * Escapa una cadena para HTML seguro (anti XSS).
     */
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
