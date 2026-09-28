<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Http\Middleware\MiddlewareInterface;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Security\Csrf;

final class CsrfMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Response $response, callable $next): void
    {
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            $next();
            return;
        }

        // Login establishes the session, so it cannot require a pre-existing CSRF token.
        if ($request->path() === '/api/v1/auth/login') {
            $next();
            return;
        }

        $token = $request->header('x-csrf-token')
            ?? $request->input('_csrf');

        if (!Csrf::verify(is_string($token) ? $token : null)) {
            $response->json([
                'error' => [
                    'code' => 'CSRF_TOKEN_INVALID',
                    'message' => 'The CSRF token is missing or invalid.',
                ],
            ], 419);
        }

        $next();
    }
}
