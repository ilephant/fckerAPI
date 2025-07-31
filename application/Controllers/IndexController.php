<?php

namespace Fcker\Application\Controllers;

use Fcker\Framework\Core\Controller;
use Fcker\Framework\Core\Response;

class IndexController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(): void
    {
        Response::success([
            'endpoints' => [
                'auth' => [
                    'POST /auth/register' => 'Register new user',
                    'POST /auth/login' => 'Login user',
                    'POST /auth/refresh' => 'Refresh tokens',
                    'GET /auth/me' => 'Get current user',
                    'POST /auth/logout' => 'Logout user'
                ],
                'posts' => [
                    'GET /posts' => 'Get all posts',
                    'GET /posts/{id}' => 'Get specific post',
                    'POST /posts' => 'Create new post',
                    'PUT /posts/{id}' => 'Update post',
                    'DELETE /posts/{id}' => 'Delete post',
                    'GET /posts/my' => 'Get user posts'
                ]
            ]
        ], 'FckerMVC API is running');
    }
}
