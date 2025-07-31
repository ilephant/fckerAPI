<?php

namespace Fcker\Application\Controllers;

use Fcker\Framework\Core\Controller;
use Fcker\Framework\Core\Response;
use Fcker\Application\Models\UserModel;
use Fcker\Framework\Utils\Validator;

class AuthController extends Controller
{
    private UserModel $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel();
    }

    public function register(): void
    {
        $data = $this->getRequestData();
        
        $validator = new Validator($data);
        $validator
            ->required('name')
            ->min('name', 3)
            ->required('email')
            ->email('email')
            ->unique('email', 'users')
            ->required('password')
            ->min('password', 6);
        
        if ($validator->fails()) {
            Response::validationError($validator->getErrors());
        }
        
        // Хешируем пароль
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Создаем пользователя
        $userId = $this->userModel->create($data);
        
        if (!$userId) {
            Response::error('Failed to create user');
        }
        
        // Получаем созданного пользователя
        $user = $this->userModel->find($userId);
        
        // Генерируем токены
        $tokens = $this->generateTokens($user);
        
        Response::success([
            'user' => $user,
            'tokens' => $tokens
        ], 'User registered successfully', 201);
    }

    public function login(): void
    {
        $data = $this->getRequestData();
        
        $rules = [
            'email' => 'required|email',
            'password' => 'required'
        ];
        
        $errors = $this->validate($data, $rules);
        
        if (!empty($errors)) {
            Response::validationError($errors);
        }
        
        // Ищем пользователя
        $user = $this->userModel->findBy('email', $data['email']);
        
        if (!$user || !password_verify($data['password'], $user['password'])) {
            Response::error('Invalid credentials', 401);
        }
        
        // Генерируем токены
        $tokens = $this->generateTokens($user);
        
        Response::success([
            'user' => $user,
            'tokens' => $tokens
        ], 'Login successful');
    }

    public function refresh(): void
    {
        $data = $this->getRequestData();
        
        if (empty($data['refresh_token'])) {
            Response::error('Refresh token is required', 400);
        }
        
        $tokens = $this->jwtService->refreshTokens($data['refresh_token']);
        
        if (!$tokens) {
            Response::error('Invalid refresh token', 401);
        }
        
        Response::success([
            'tokens' => $tokens
        ], 'Tokens refreshed successfully');
    }

    public function me(): void
    {
        $this->requireAuth();
        
        Response::success([
            'user' => $this->user
        ]);
    }

    public function logout(): void
    {
        $this->requireAuth();
        
        // В реальном приложении здесь можно добавить токен в черный список
        Response::success([], 'Logged out successfully');
    }

    private function generateTokens(array $user): array
    {
        $payload = [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'name' => $user['name']
        ];
        
        return [
            'access_token' => $this->jwtService->generateAccessToken($payload),
            'refresh_token' => $this->jwtService->generateRefreshToken($payload)
        ];
    }
} 