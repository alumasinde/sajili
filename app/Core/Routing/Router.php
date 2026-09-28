<?php

declare(strict_types=1);

namespace App\Core\Routing;

use App\Core\Container;
use App\Core\Http\Middleware\MiddlewareInterface;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Support\Logger;
use Closure;
use RuntimeException;
use Throwable;

final class Router
{
    private array $routes = [];
    private string $prefix = '';
    private array $groupMiddleware = [];

    public function __construct(
        private readonly Request $request,
        private readonly Response $response,
        private readonly Logger $logger,
        private readonly Container $container,
    ) {
    }

    public function get(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    public function put(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->add('PUT', $path, $handler, $middleware);
    }

    public function patch(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->add('PATCH', $path, $handler, $middleware);
    }

    public function delete(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->add('DELETE', $path, $handler, $middleware);
    }

    public function options(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->add('OPTIONS', $path, $handler, $middleware);
    }

    public function group(string $prefix, Closure $callback, array $middleware = []): void
    {
        $previousPrefix = $this->prefix;
        $previousMiddleware = $this->groupMiddleware;

        $this->prefix .= rtrim($prefix, '/');
        $this->groupMiddleware = array_merge($this->groupMiddleware, $middleware);

        try {
            $callback($this);
        } finally {
            $this->prefix = $previousPrefix;
            $this->groupMiddleware = $previousMiddleware;
        }
    }

    private function add(string $method, string $path, callable|array $handler, array $middleware): void
    {
        $fullPath = '/' . trim($this->prefix . '/' . trim($path, '/'), '/');
        $fullPath = $fullPath === '/' ? '/' : rtrim($fullPath, '/');

        foreach (array_merge($this->groupMiddleware, $middleware) as $item) {
            if (!is_string($item) || !is_a($item, MiddlewareInterface::class, true)) {
                throw new RuntimeException('Invalid route middleware.');
            }
        }

        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $fullPath,
            'handler' => $handler,
            'middleware' => array_merge($this->groupMiddleware, $middleware),
        ];
    }

    public function dispatch(): void
    {
        try {
            $allowedMethods = [];

            foreach ($this->routes as $route) {
                $pattern = $this->compile($route['path']);

                if (!preg_match($pattern, $this->request->path(), $matches)) {
                    continue;
                }

                $allowedMethods[] = $route['method'];

                if ($route['method'] !== $this->request->method()) {
                    continue;
                }

                array_shift($matches);
                $this->runMiddleware($route['middleware'], function () use ($route, $matches): void {
                    $this->invokeHandler($route['handler'], $matches);
                });

                return;
            }

            if ($allowedMethods !== []) {
                $allowedMethods = array_values(array_unique($allowedMethods));
                header('Allow: ' . implode(', ', $allowedMethods));
                $this->response->json([
                    'error' => [
                        'code' => 'METHOD_NOT_ALLOWED',
                        'message' => 'The HTTP method is not allowed for this resource.',
                    ],
                ], 405);
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
                'request_id' => $this->request->requestId(),
            ]);

            if ($this->request->pathStartsWith('/api/')) {
                $this->response->json([
                    'error' => [
                        'code' => 'INTERNAL_SERVER_ERROR',
                        'message' => 'An unexpected error occurred.',
                    ],
                ], 500);
            }

            $this->response->html('<h1>Internal Server Error</h1>', 500);
        }
    }

    private function runMiddleware(array $middleware, callable $destination): void
    {
        $index = 0;

        $next = function () use (&$index, $middleware, $destination, &$next): void {
            if (!isset($middleware[$index])) {
                $destination();
                return;
            }

            $class = $middleware[$index++];
            $instance = $this->container->make($class);
            $instance->handle($this->request, $this->response, $next);
        };

        $next();
    }

    private function invokeHandler(callable|array $handler, array $matches): void
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            $controller = $this->container->make($class);
            $controller->$method(...$matches);
            return;
        }

        $handler($this->request, $this->response, ...$matches);
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
