<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    /** @var array<int, array{method:string,path:string,handler:mixed}> */
    private array $routes = [];

    public function add(string $method, string $path, mixed $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method || $route['path'] !== $uri) {
                continue;
            }

            $handler = $route['handler'];

            if (is_callable($handler)) {
                $handler();
                return;
            }

            if (is_string($handler) && str_contains($handler, '@')) {
                [$controllerClass, $action] = explode('@', $handler, 2);
                if (!class_exists($controllerClass)) {
                    break;
                }

                $controller = new $controllerClass();
                $controller->{$action}();
                return;
            }
        }

        http_response_code(404);
        jsonResponse([
            'message' => 'Route not found.',
            'method' => $method,
            'uri' => $uri,
        ], 404);
    }
}
