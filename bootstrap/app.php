<?php

declare(strict_types=1);

use App\Core\Container;
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
header('X-Request-ID: ' . $request->requestId());

$container = new Container();
$container->instance(Request::class, $request);
$container->instance(Response::class, $response);
$container->instance(Logger::class, $logger);
$container->instance(\App\Core\View::class, new \App\Core\View(dirname(__DIR__) . '/resources/views'));

$databases = new \App\Core\Database\DatabaseManager();
$tenantContext = new \App\Core\Tenancy\TenantContext($databases);
$tenantResolver = new \App\Core\Tenancy\TenantResolver($databases->platform());
$container->instance(\App\Core\Database\DatabaseManager::class, $databases);
$container->instance(\PDO::class, $databases->platform());
$container->instance(\App\Core\Tenancy\TenantContext::class, $tenantContext);
$container->instance(\App\Core\Tenancy\TenantResolver::class, $tenantResolver);

$router = new Router($request, $response, $logger, $container);

return [
    'request' => $request,
    'response' => $response,
    'router' => $router,
    'logger' => $logger,
    'container' => $container,
    'databases' => $databases,
    'tenantContext' => $tenantContext,
];
