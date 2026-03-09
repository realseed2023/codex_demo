<?php

declare(strict_types=1);

use App\Core\Router;

require_once __DIR__ . '/../app/Core/helpers.php';
require_once __DIR__ . '/../app/Core/autoload.php';

loadEnv(__DIR__ . '/../.env');

$router = new Router();

$routes = require __DIR__ . '/../routes/web.php';
foreach ($routes as $route) {
    [$method, $path, $handler] = $route;
    $router->add($method, $path, $handler);
}

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
