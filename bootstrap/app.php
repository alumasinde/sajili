<?php

declare(strict_types=1);

use App\Core\Support\Env;
use App\Core\Support\Logger;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Routing\Router;

require dirname(__DIR__) . '/vendor/autoload.php';

Env::load(dirname(__DIR__) . '/.env');

date_default_timezone_set(Env::get('APP_TIMEZONE', 'Africa/Nairobi'));

$logger = new Logger(dirname(__DIR__) . '/storage/logs/app.log');

$request = Request::capture();
$response = new Response();

$router = new Router($request, $response, $logger);

return [
    'request' => $request,
    'response' => $response,
    'router' => $router,
    'logger' => $logger,
];
