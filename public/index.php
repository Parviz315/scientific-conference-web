<?php

declare(strict_types=1);

use App\Controllers\ConferenceController;
use App\Controllers\SubmissionController;
use App\Core\Router;
use App\Core\View;

define('BASE_PATH', dirname(__DIR__));

$autoloadFile = BASE_PATH . '/vendor/autoload.php';
if (is_file($autoloadFile)) {
    require $autoloadFile;
} else {
    spl_autoload_register(static function (string $class): void {
        $prefix = 'App\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }
        $relative = substr($class, strlen($prefix));
        $file = BASE_PATH . '/src/' . str_replace('\\', '/', $relative) . '.php';
        if (is_file($file)) {
            require $file;
        }
    });
}

set_exception_handler(static function (Throwable $e): void {
    http_response_code(500);
    $viewsDir = BASE_PATH . '/views';
    if (is_file($viewsDir . '/errors/error.php') && is_file($viewsDir . '/layout.php')) {
        echo (new View($viewsDir))->render('errors/error', [
            'title' => 'Ошибка сервера',
            'active' => '',
            'code' => 500,
            'message' => 'Внутренняя ошибка приложения. Попробуйте позже.',
        ]);
        return;
    }
    header('Content-Type: text/plain; charset=UTF-8');
    echo '500 Internal Server Error';
});

$router = new Router();

$router->get('/', [ConferenceController::class, 'index']);
$router->get('/sections', [ConferenceController::class, 'sections']);
$router->get('/submissions', [SubmissionController::class, 'index']);
$router->get('/submissions/{id}', [SubmissionController::class, 'show']);

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
