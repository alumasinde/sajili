<?php

declare(strict_types=1);

namespace App\Core\Routing;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Support\Logger;
use Closure;
use Throwable;

final class Router
{
    private array $routes = [];
    private string $prefix = '';

    public function __construct(
        private readonly Request $request,
        private readonly Response $response,
        private readonly Logger $logger,
    ) {
    }

    public function get(string $path, callable|array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function group(string $prefix, Closure $callback): void
    {
        $previous = $this->prefix;
        $this->prefix .= rtrim($prefix, '/');

        $callback($this);

        $this->prefix = $previous;
    }

    private function add(string $method, string $path, callable|array $handler): void
    {
        $fullPath = '/' . trim($this->prefix . '/' . trim($path, '/'), '/');
        $fullPath = $fullPath === '/' ? '/' : rtrim($fullPath, '/');

        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'handler' => $handler,
        ];
    }

    public function dispatch(): void
    {
        try {
            foreach ($this->routes as $route) {
                if ($route['method'] !== $this->request->method()) {
                    continue;
                }

                $pattern = $this->compile($route['path']);

                if (!preg_match($pattern, $this->request->path(), $matches)) {
                    continue;
                }

                array_shift($matches);

                $handler = $route['handler'];

                if (is_array($handler)) {
                    [$class, $method] = $handler;
                    $controller = new $class(
                        $this->request,
                        $this->response,
                        $this->logger
                    );

         $controller->$method(...$matches);
                    return;
                }

        $handler($this->request, $this->response, ...$matches);
        return;
            }

            $this->response->json([
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Route not found.',
                ],
            ], 404);
        } catch (Throwable $e) {
            $this->logger->error('Unhandled request exception', [
                'exception' => $e::class,
                'message' => $e->getMessage(),
                'path' => $this->request->path(),
            ]);

            $this->response->json([
                'error' => [
                    'code' => 'INTERNAL_SERVER_ERROR',
                    'message' => 'An unexpected error occurred.',
                ],
            ], 500);
        }
    }

    private function compile(string $path): string
    {
        $pattern = preg_replace(
            '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
            '([^/]+)',
            $path
        );

        return '#^' . $pattern . '/?$#';
    }
}
