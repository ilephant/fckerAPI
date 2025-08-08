<?php

use Fcker\Framework\Core\Router;
use Fcker\Framework\Middleware\AuthMiddleware;
use Fcker\Application\Controllers\IndexController;
use Fcker\Application\Controllers\AuthController;
use Fcker\Application\Controllers\PostsController;

return function (Router $router): void {
    // Index
    $router->get('/', [IndexController::class, 'index']);

    // Auth
    $router->post('/auth/register', [AuthController::class, 'register']);
    $router->post('/auth/login', [AuthController::class, 'login']);
    $router->post('/auth/refresh', [AuthController::class, 'refresh']);
    $router->get('/auth/me', [AuthController::class, 'me'], [AuthMiddleware::class]);
    $router->post('/auth/logout', [AuthController::class, 'logout'], [AuthMiddleware::class]);

    // Posts
    $router->get('/posts', [PostsController::class, 'index']);
    $router->get('/posts/{id}', [PostsController::class, 'show']);
    $router->post('/posts', [PostsController::class, 'store'], [AuthMiddleware::class]);
    $router->put('/posts/{id}', [PostsController::class, 'update'], [AuthMiddleware::class]);
    $router->delete('/posts/{id}', [PostsController::class, 'destroy'], [AuthMiddleware::class]);
};
