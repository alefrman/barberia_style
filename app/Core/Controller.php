<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Controller
 *
 * Controlador base. Los controladores concretos lo extienden
 * y reciben Request + params del Router en cada acción.
 */
abstract class Controller
{
    /**
     * Renderiza una vista con layout y retorna la respuesta.
     */
    protected function view(string $view, array $data = [], string $layout = 'public'): Response
    {
        return Response::make(View::render($view, $data, $layout));
    }

    /**
     * Renderiza una vista sin layout.
     */
    protected function viewRaw(string $view, array $data = []): Response
    {
        return Response::make(View::render($view, $data));
    }

    /**
     * Retorna una respuesta JSON.
     */
    protected function json(mixed $data, int $statusCode = 200): Response
    {
        return Response::json($data, $statusCode);
    }

    /**
     * Redirige a una ruta.
     */
    protected function redirect(string $path): Response
    {
        return Response::redirect(APP_URL . $path);
    }
}
