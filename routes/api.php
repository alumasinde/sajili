<?php

declare(strict_types=1);

use App\Modules\Health\Controllers\HealthController;

$router->group('/api/v1', function ($router): void {
    $router->get('/health', [HealthController::class, 'api']);
});
