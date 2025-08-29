<?php declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = ['GET' => [], 'POST' => []];

    public function get(string $path, $action): void { $this->routes['GET'][$path] = $action; }
    public function post(string $path, $action): void { $this->routes['POST'][$path] = $action; }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        $action = $this->routes[$method][$uri] ?? null;

        if ($action === null) {
            (new \App\Controllers\ErrorController())->notFound();
            return;
        }

        if (is_callable($action)) {
            $action();
            return;
        }

        if (is_string($action) && strpos($action, '@') !== false) {
            [$controllerFile, $methodName] = explode('@', $action, 2);
            $class = $this->resolveControllerClass($controllerFile);
            $fqcn  = '\\App\\Controllers\\' . $class;
            if (!class_exists($fqcn)) {
                // class autoload will try to include app/controllers/{lower}.php
            }
            if (!class_exists($fqcn)) {
                throw new \RuntimeException("Controller class {$fqcn} not found");
            }
            $controller = new $fqcn();
            if (!method_exists($controller, $methodName)) {
                throw new \RuntimeException("Method {$methodName} not found on {$fqcn}");
            }
            $controller->{$methodName}();
            return;
        }

        throw new \RuntimeException('Invalid route action');
    }

    private function resolveControllerClass(string $file): string
    {
        $file = strtolower($file);
        if (str_ends_with($file, 'controller')) {
            $base = substr($file, 0, -10);
            return ucfirst($base) . 'Controller';
        }
        return ucfirst($file);
    }
}
