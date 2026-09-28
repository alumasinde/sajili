<?php

declare(strict_types=1);

use App\Modules\Health\Controllers\HealthController;

$router->get('/', [HealthController::class, 'index']);
$router->get('/health', [HealthController::class, 'index']);
