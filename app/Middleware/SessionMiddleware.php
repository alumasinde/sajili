<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Http\Middleware\MiddlewareInterface;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Security\Session;

final class SessionMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Response $response, callable $next): void
    {
        Session::start();
        $next();
    }
}
