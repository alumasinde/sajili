<?php

declare(strict_types=1);

namespace App\Core\Http\Middleware;

use App\Core\Http\Request;
use App\Core\Http\Response;

interface MiddlewareInterface
{
    public function handle(Request $request, Response $response, callable $next): void;
}
