<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Security\Headers;

final class SecurityHeadersMiddleware
{
    public function handle(Request $request, Response $response, callable $next): mixed
    {
        Headers::send();

        return $next($request, $response);
    }
}
