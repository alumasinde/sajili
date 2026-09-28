<?php

declare(strict_types=1);

$app = require dirname(__DIR__) . '/bootstrap/app.php';

require dirname(__DIR__) . '/routes/web.php';
require dirname(__DIR__) . '/routes/api.php';

$app['router']->dispatch();
