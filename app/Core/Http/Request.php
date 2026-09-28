<?php

declare(strict_types=1);

namespace App\Core\Http;

final class Request
{
    private array $attributes = [];

    private function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly string $host,
        private readonly array $query,
        private readonly array $body,
        private readonly array $headers,
    ) {
    }

    public static function capture(): self
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        $headers = function_exists('getallheaders') ? getallheaders() : [];

        return new self(
            strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            $path,
            $_SERVER['HTTP_HOST'] ?? 'localhost',
            $_GET,
            $_POST,
            array_change_key_case($headers, CASE_LOWER),
        );
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function host(): string
    {
        return $this->host;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    public function all(): array
    {
        return $this->body;
    }

    public function header(string $key, mixed $default = null): mixed
    {
        return $this->headers[strtolower($key)] ?? $default;
    }

    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function attribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }
}
