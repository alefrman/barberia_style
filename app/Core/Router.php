<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Router
 *
 * Enrutador HTTP con:
 *   - Verbos GET, POST, PUT, PATCH, DELETE
 *   - Parámetros dinámicos en la URL  /services/{id}
 *   - Middlewares por ruta
 *   - Dispatch hacia controladores (método invocable o "Controller@método")
 */
class Router
{
    private array $routes = [];
    private array $globalMiddlewares = [];

    /** Prefijo de la URL donde está instalada la app. Ej: /barberia_style/public */
    private string $basePath = '';

    /**
     * Define el prefijo base de la aplicación para que las rutas
     * funcionen instaladas en un subdirectorio.
     */
    public function setBasePath(string $basePath): void
    {
        $this->basePath = rtrim($basePath, '/');
    }

    /**
     * Registra una ruta GET.
     */
    public function get(string $path, callable|string $handler, array $middlewares = []): void
    {
        $this->add('GET', $path, $handler, $middlewares);
    }

    /**
     * Registra una ruta POST.
     */
    public function post(string $path, callable|string $handler, array $middlewares = []): void
    {
        $this->add('POST', $path, $handler, $middlewares);
    }

    /**
     * Registra una ruta PUT.
     */
    public function put(string $path, callable|string $handler, array $middlewares = []): void
    {
        $this->add('PUT', $path, $handler, $middlewares);
    }

    /**
     * Registra una ruta PATCH.
     */
    public function patch(string $path, callable|string $handler, array $middlewares = []): void
    {
        $this->add('PATCH', $path, $handler, $middlewares);
    }

    /**
     * Registra una ruta DELETE.
     */
    public function delete(string $path, callable|string $handler, array $middlewares = []): void
    {
        $this->add('DELETE', $path, $handler, $middlewares);
    }

    /**
     * Registra un middleware que se ejecuta en todas las rutas.
     */
    public function middleware(string $middleware): void
    {
        $this->globalMiddlewares[] = $middleware;
    }

    /**
     * Añade una ruta a la colección.
     */
    private function add(string $method, string $path, callable|string $handler, array $middlewares): void
    {
        $this->routes[] = [
            'method'      => $method,
            'path'        => rtrim($path, '/') ?: '/',
            'handler'     => $handler,
            'middlewares' => $middlewares,
        ];
    }

    /**
     * Ejecuta el enrutamiento según el request actual.
     */
    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $uri = $this->normalizeUri($request->uri());

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = $this->match($route['path'], $uri);
            if ($params === null) {
                continue;
            }

            $allMiddlewares = array_merge($this->globalMiddlewares, $route['middlewares']);

            try {
                $this->runMiddlewares($allMiddlewares, $request);

                $response = $this->resolveHandler($route['handler'], $request, $params);
                if (!$response instanceof Response) {
                    $response = Response::make($response);
                }
            } catch (RuntimeException $e) {
                return Response::notFound($e->getMessage());
            }

            return $response;
        }

        return Response::notFound();
    }

    /**
     * Normaliza la URI: elimina el prefijo base si existe.
     */
    private function normalizeUri(string $uri): string
    {
        $uri = parse_url($uri, PHP_URL_PATH) ?: $uri;
        $uri = '/' . trim($uri, '/');

        if ($this->basePath !== '' && str_starts_with($uri, $this->basePath)) {
            $uri = substr($uri, strlen($this->basePath));
            $uri = '/' . trim($uri, '/');
        }

        return rtrim($uri, '/') ?: '/';
    }

    /**
     * Compara la ruta registrada contra la URI y extrae parámetros.
     *
     * @return array<string,string>|null Params o null si no coincide.
     */
    private function match(string $routePath, string $uri): ?array
    {
        $routeParts = explode('/', $routePath);
        $uriParts = explode('/', $uri);

        if (count($routeParts) !== count($uriParts)) {
            return null;
        }

        $params = [];
        foreach ($routeParts as $index => $part) {
            if (str_starts_with($part, '{') && str_ends_with($part, '}')) {
                $name = trim($part, '{}');
                $params[$name] = urldecode($uriParts[$index]);
            } elseif ($part !== $uriParts[$index]) {
                return null;
            }
        }

        return $params;
    }

    /**
     * Ejecuta la cadena de middlewares.
     * Formato: 'Clase' o ['Clase', [param1, param2]] para pasar argumentos al constructor.
     */
    private function runMiddlewares(array $middlewares, Request $request): void
    {
        foreach ($middlewares as $middleware) {
            $class = is_array($middleware) ? $middleware[0] : $middleware;
            $params = is_array($middleware) ? ($middleware[1] ?? []) : [];

            if (!is_string($class) || !class_exists($class)) {
                throw new RuntimeException("Middleware no encontrado: {$class}");
            }

            $instance = new $class(...$params);
            if (!method_exists($instance, 'handle')) {
                throw new RuntimeException("El middleware {$class} debe implementar handle().");
            }

            $instance->handle($request);
        }
    }

    /**
     * Resuelve el handler: callable o string "Namespace\Controller@método".
     */
    private function resolveHandler(callable|string $handler, Request $request, array $params): mixed
    {
        if (is_callable($handler)) {
            return $handler($request, $params);
        }

        if (!is_string($handler) || !str_contains($handler, '@')) {
            throw new RuntimeException('Handler inválido: se espera callable o "Controller@método".');
        }

        [$class, $method] = explode('@', $handler, 2);

        if (!class_exists($class)) {
            throw new RuntimeException("Controlador no encontrado: {$class}");
        }

        $controller = new $class();
        if (!method_exists($controller, $method)) {
            throw new RuntimeException("Método {$method} no existe en {$class}.");
        }

        return $controller->{$method}($request, $params);
    }
}
