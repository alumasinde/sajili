<?php

declare(strict_types=1);

use App\Core\Tenancy\TenantMiddleware;
use App\Middleware\SessionMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Modules\Auth\Middleware\AuthenticationMiddleware;
use App\Modules\Auth\Controllers\AuthController;
use App\Modules\Admin\Controllers\AdminController;

$router->group('/api/v1', function ($router): void {
    $router->get('/health', [\App\Modules\Health\Controllers\HealthController::class, 'api']);

    $router->group('/auth', function ($router): void {
        $router->post('/login', [AuthController::class, 'loginApi']);
        $router->get('/me', [AuthController::class, 'me'], [
            AuthenticationMiddleware::class,
        ]);
        $router->post('/logout', [AuthController::class, 'logoutApi'], [
            AuthenticationMiddleware::class,
        ]);
    }, [
        TenantMiddleware::class,
        SessionMiddleware::class,
        CsrfMiddleware::class,
    ]);

    $router->group('/admin', function ($router): void {
        $router->get('/users', [AdminController::class, 'users'], [
            'permission:users.view',
        ]);
        $router->get('/departments', [AdminController::class, 'departments'], [
            'permission:departments.view',
        ]);
        $router->get('/roles', [AdminController::class, 'roles'], [
            'permission:roles.view',
        ]);
        $router->post('/users', [AdminController::class, 'createUser'], [
            'permission:users.create',
        ]);
        $router->patch('/users/{userId}/status', [AdminController::class, 'updateUserStatus'], [
            'permission:users.deactivate',
        ]);
        $router->post('/departments', [AdminController::class, 'createDepartment'], [
            'permission:departments.manage',
        ]);
        $router->post('/departments/{departmentId}/hod', [AdminController::class, 'assignHod'], [
            'permission:departments.assign_hod',
        ]);
        $router->post('/roles', [AdminController::class, 'createRole'], [
            'permission:roles.manage',
        ]);
    }, [
        TenantMiddleware::class,
        SessionMiddleware::class,
        AuthenticationMiddleware::class,
        CsrfMiddleware::class,
    ]);
}, []);
