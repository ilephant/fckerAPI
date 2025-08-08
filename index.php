<?php

// Устанавливаем кодировку
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// Определяем корневую директорию
define('__ROOT__', dirname(__FILE__) . '/');

// Подключаем автозагрузчик
require_once __ROOT__ . 'vendor/autoload.php';
require_once __ROOT__ . 'framework/helpers.php';

use Fcker\Framework\Core\Router;
use Fcker\Framework\Core\Response;
use Fcker\Framework\Middleware\CorsMiddleware;
use Fcker\Framework\Exceptions\HttpException;

// Централизованная обработка CORS preflight
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Max-Age: 86400');
    exit;
}

// Временная зона
date_default_timezone_set(getenv('TIMEZONE') ?: 'Europe/Moscow');

try {
    $router = Router::getInstance();

    // Глобальное CORS middleware
    $router->use(CorsMiddleware::class);

    // Подключаем маршруты из конфига
    $routes = require __ROOT__ . 'config/routes.php';
    is_callable($routes) && $routes($router);

    // Обработка запроса
    $router->getRoute();
} catch (HttpException $e) {
    $e->toResponse()->send();
} catch (Throwable $e) {
    Response::error('Server Error', 500, ['exception' => $e->getMessage()])->send();
}
