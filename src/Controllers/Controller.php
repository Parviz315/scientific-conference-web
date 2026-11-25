<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;

abstract class Controller
{
    protected View $view;

    public function __construct()
    {
        $this->view = new View(dirname(__DIR__, 2) . '/views');
    }

    /** @param array<string, mixed> $data */
    protected function render(string $template, array $data = [], int $status = 200, ?string $layout = 'layout'): void
    {
        http_response_code($status);
        echo $this->view->render($template, $data, $layout);
    }

    protected function redirect(string $path): never
    {
        header('Location: ' . $this->view->url($path));
        exit;
    }

    protected function abort(int $code, string $message = ''): never
    {
        $defaultMessages = [
            403 => 'Доступ запрещён.',
            404 => 'Запрашиваемый объект не найден.',
            405 => 'Метод запроса не поддерживается.',
            422 => 'Ошибка проверки данных.',
            500 => 'Внутренняя ошибка сервера.',
        ];

        $this->render('errors/error', [
            'title' => "Ошибка {$code}",
            'active' => '',
            'code' => $code,
            'message' => $message !== '' ? $message : ($defaultMessages[$code] ?? 'Произошла ошибка.'),
        ], $code);
        exit;
    }

    /** @param array<string, mixed> $payload */
    protected function json(array $payload, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
