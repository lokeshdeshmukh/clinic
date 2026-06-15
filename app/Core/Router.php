<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->add(['GET'], $path, $handler, $middleware);
    }

    public function post(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->add(['POST'], $path, $handler, $middleware);
    }

    public function put(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->add(['PUT'], $path, $handler, $middleware);
    }

    public function delete(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->add(['DELETE'], $path, $handler, $middleware);
    }

    private function add(array $methods, string $path, callable|array $handler, array $middleware): void
    {
        $path = rtrim($path, '/') ?: '/';
        $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $path);

        $this->routes[] = [
            'methods' => $methods,
            'path' => $path,
            'pattern' => '#^' . $pattern . '$#',
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(Request $request): never
    {
        foreach ($this->routes as $route) {
            if (!in_array($request->method, $route['methods'], true)) {
                continue;
            }

            if (!preg_match($route['pattern'], $request->path, $matches)) {
                continue;
            }

            $params = array_filter($matches, static fn ($key): bool => !is_int($key), ARRAY_FILTER_USE_KEY);

            foreach ($route['middleware'] as $middleware) {
                Middleware::handle($middleware, $request);
            }

            $handler = $route['handler'];

            if (is_array($handler)) {
                [$class, $method] = $handler;
                $instance = new $class();
                $instance->{$method}($request, ...array_values($params));
                exit;
            }

            $handler($request, ...array_values($params));
            exit;
        }

        Response::abort(404, 'Page not found.');
    }
}
