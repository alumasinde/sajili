<?php

declare(strict_types=1);

use App\Core\Tenancy\TenantMiddleware;
use App\Middleware\SessionMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Modules\Admin\Controllers\AdminController;
use App\Modules\Auth\Controllers\AuthController;

$router->group('', function ($router): void {
    $router->get('/login', [AuthController::class, 'showLogin']);
    $router->post('/login', [AuthController::class, 'loginWeb']);
}, [
    TenantMiddleware::class,
    SessionMiddleware::class,
    CsrfMiddleware::class,
]);

$router->group('/admin', function ($router): void {
    $router->get('', [AdminController::class, 'dashboard'], ['permission:dashboard.view']);
    $router->post('/logout', [AuthController::class, 'logoutWeb']);
}, [
    TenantMiddleware::class,
    SessionMiddleware::class,
    \App\Modules\Auth\Middleware\AuthenticationMiddleware::class,
    CsrfMiddleware::class,
]);
