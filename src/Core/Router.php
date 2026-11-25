<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<int, array{method: string, regex: string, handler: array{0: class-string, 1: string}}> */
    private array $routes = [];

    /** @param array{0: class-string, 1: string} $handler */
    public function get(string $path, array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    /** @param array{0: class-string, 1: string} $handler */
    public function post(string $path, array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    /** @param array{0: class-string, 1: string} $handler */
    private function add(string $method, string $path, array $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'regex' => $this->toRegex($path),
            'handler' => $handler,
        ];
    }

    private function toRegex(string $path): string
    {
        $pattern = preg_replace('#\{(\w+)\}#', '(?P<$1>\d+)', $path);
        return '#^' . $pattern . '$#';
    }

    public static function basePath(): string
    {
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/');
        $dir = dirname($scriptName);
        if ($dir === '/' || $dir === '.') {
            return '';
        }
        return rtrim($dir, '/');
    }

    private function normalizePath(string $uri): string
    {
        $path = parse_url($uri, PHP_URL_PATH);
        $path = is_string($path) ? $path : '/';

        $base = self::basePath();
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }

        $path = '/' . ltrim($path, '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }

    public function dispatch(string $method, string $uri): void
    {
        $httpMethod = strtoupper($method);
        $path = $this->normalizePath($uri);
        $allowedMethods = [];

        foreach ($this->routes as $route) {
            if (!preg_match($route['regex'], $path, $matches)) {
                continue;
            }
            if ($route['method'] !== $httpMethod) {
                $allowedMethods[] = $route['method'];
                continue;
            }

            $namedParams = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            $params = array_map('intval', array_values($namedParams));

            [$class, $action] = $route['handler'];
            $controller = new $class();
            $controller->$action(...$params);
            return;
        }

        if ($allowedMethods !== []) {
            http_response_code(405);
            header('Allow: ' . implode(', ', array_unique($allowedMethods)));
            echo '405 Method Not Allowed';
            return;
        }

        http_response_code(404);
        $viewsDir = dirname(__DIR__, 2) . '/views';
        echo (new View($viewsDir))->render('errors/error', [
            'title' => 'Страница не найдена',
            'active' => '',
            'code' => 404,
            'message' => 'Запрошенный маршрут не существует. Проверьте адрес.',
        ]);
    }
}
