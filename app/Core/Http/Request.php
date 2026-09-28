<?php

declare(strict_types=1);

namespace App\Core\Http;

final class Request
{
    private array $attributes = [];

    private readonly string $requestId;

    private function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly string $host,
        private readonly array $query,
        private readonly array $body,
        private readonly array $headers,
        private readonly string $rawBody,
    ) {
        $this->requestId = self::normalizeRequestId($headers);
    }

    public static function capture(): self
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $headers = array_change_key_case($headers, CASE_LOWER);
        $rawBody = (string) file_get_contents('php://input');
        $body = $_POST;

        $contentType = strtolower((string) ($headers['content-type'] ?? ''));

        if (str_contains($contentType, 'application/json') && $rawBody !== '') {
            try {
                $decoded = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
                if (is_array($decoded)) {
                    $body = $decoded;
                }
            } catch (\JsonException) {
                $body = [];
            }
        }

        return new self(
            strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            $path,
            $_SERVER['HTTP_HOST'] ?? 'localhost',
            $_GET,
            $body,
            $headers,
            $rawBody,
        );
    }

    public function requestId(): string
    {
        return $this->requestId;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function pathStartsWith(string $prefix): bool
    {
        return str_starts_with($this->path, $prefix);
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

    public function rawBody(): string
    {
        return $this->rawBody;
    }

    public function isJson(): bool
    {
        return str_contains(
            strtolower((string) $this->header('content-type', '')),
            'application/json'
        );
    }

    public function header(string $key, mixed $default = null): mixed
    {
        return $this->headers[strtolower($key)] ?? $default;
    }

    private static function normalizeRequestId(array $headers): string
    {
        $candidate = $headers['x-request-id'] ?? null;

        if (is_string($candidate) && preg_match('/^[A-Za-z0-9._:-]{1,100}$/', $candidate)) {
            return $candidate;
        }

        return bin2hex(random_bytes(16));
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
