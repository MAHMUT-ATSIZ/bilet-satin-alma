<?php
namespace App\Core;

final class Router
{
    /** @var array<string, array<string, callable|array{string,string}>> */
    private array $routes = ['GET' => [], 'POST' => []];

    public function get(string $path, callable|array $handler): void
    {
        $this->routes['GET'][$this->normalize($path)] = $handler;
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->routes['POST'][$this->normalize($path)] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = $this->normalize(parse_url($uri, PHP_URL_PATH) ?: '/');
        $handler = $this->routes[$method][$path] ?? null;

        if (!$handler) {
            http_response_code(404);
            echo '404 Not Found';
            return;
        }

        
        if (is_array($handler)) {
            [$class, $methodName] = $handler;

            $instance = new $class();

            if (!method_exists($instance, $methodName)) {
                http_response_code(500);
                echo 'Handler method not found: ' . get_class($instance) . '::' . $methodName;
                return;
            }

            $instance->{$methodName}();
            return;
        }

        
        $handler();
    }

    private function normalize(string $path): string
    {
        $path = rtrim($path, '/');
        return $path === '' ? '/' : $path;
    }
}
