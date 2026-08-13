<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Request
 *
 * Abstracción del request HTTP entrante.
 * Provee acceso seguro a query, body (JSON/form), headers y método.
 */
class Request
{
    public function __construct(
        private readonly string $method,
        private readonly string $uri,
        private readonly array $query,
        private readonly array $body,
        private readonly array $files,
        private readonly array $headers
    ) {}

    /**
     * Crea una instancia desde los superglobals de PHP.
     */
    public static function createFromGlobals(): self
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Soporte para _method (override en formularios HTML: PUT/DELETE)
        $parsedBody = $_POST;
        $override = $parsedBody['_method'] ?? null;
        if (in_array(strtoupper((string) $override), ['PUT', 'PATCH', 'DELETE'], true)) {
            $method = strtoupper((string) $override);
        }

        // Body JSON (para fetch/AJAX con Content-Type: application/json)
        $raw = file_get_contents('php://input');
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') !== false && $raw !== '' && $raw !== false) {
            $json = json_decode($raw, true);
            if (is_array($json)) {
                $parsedBody = array_merge($parsedBody, $json);
            }
        }

        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $headers[$header] = $value;
            }
        }

        return new self(
            method: strtoupper($method),
            uri: parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/',
            query: $_GET,
            body: $parsedBody,
            files: $_FILES,
            headers: $headers
        );
    }

    public function method(): string
    {
        return $this->method;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function isGet(): bool
    {
        return $this->method === 'GET';
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    /**
     * Valor de un parámetro query (GET).
     */
    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    /**
     * Valor de un campo del body (POST/PUT/etc).
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    /**
     * Retorna todos los campos del body.
     */
    public function all(): array
    {
        return $this->body;
    }

    /**
     * Verifica si un campo existe en el body.
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->body);
    }

    /**
     * Archivo subido por su nombre de campo.
     */
    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function header(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    /**
     * Verifica si el request espera JSON (via AJAX/fetch).
     */
    public function wantsJson(): bool
    {
        $accept = $this->header('Accept') ?? '';
        return str_contains($accept, 'application/json')
            || str_contains($this->header('X-Requested-With') ?? '', 'XMLHttpRequest');
    }
}
