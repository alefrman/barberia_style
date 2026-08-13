<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Response
 *
 * Abstracción de la respuesta HTTP.
 * Soporta JSON, vistas HTML, redirecciones y códigos de estado.
 */
class Response
{
    public function __construct(
        private mixed $content = '',
        private int $statusCode = 200,
        private array $headers = []
    ) {}

    public static function make(mixed $content = '', int $statusCode = 200, array $headers = []): self
    {
        return new self($content, $statusCode, $headers);
    }

    /**
     * Respuesta JSON.
     */
    public static function json(mixed $data, int $statusCode = 200): self
    {
        return new self(json_encode($data), $statusCode, ['Content-Type' => 'application/json; charset=utf-8']);
    }

    /**
     * Redirección HTTP.
     */
    public static function redirect(string $url, int $statusCode = 302): self
    {
        return new self('', $statusCode, ['Location' => $url]);
    }

    /**
     * Respuesta 404.
     */
    public static function notFound(string $message = 'Recurso no encontrado.'): self
    {
        return new self($message, 404);
    }

    /**
     * Envía la respuesta al cliente.
     */
    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        if (is_string($this->content) || $this->content === null) {
            echo $this->content ?? '';
        } else {
            echo json_encode($this->content);
        }
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function content(): mixed
    {
        return $this->content;
    }
}
