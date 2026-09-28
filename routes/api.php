<?php

declare(strict_types=1);

use App\Core\Tenancy\TenantMiddleware;
use App\Modules\Health\Controllers\HealthController;
use App\Modules\Organizations\Controllers\TenantContextController;

$router->group('/api/v1', function ($router): void {
    $router->get('/health', [HealthController::class, 'api']);

    $router->group('/tenant', function ($router): void {
        $router->get('/context', [TenantContextController::class, 'show']);
    }, [TenantMiddleware::class]);
});
