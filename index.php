<?php

// Устанавливаем кодировку
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// Определяем корневую директорию
define('__ROOT__', dirname(__FILE__) . '/');

// Подключаем автозагрузчик
require_once __ROOT__ . 'vendor/autoload.php';

use Fcker\Framework\Core\Router;
use Fcker\Framework\Core\Request;
use Fcker\Framework\Core\Response;

// Устанавливаем временную зону из переменных окружения
date_default_timezone_set(getenv('TIMEZONE') ?: 'Europe/Moscow');

try {
    $router = Router::getInstance();
    $router->getRoute();
} catch (Exception $e) {
    Response::error($e->getMessage(), 500);
}
