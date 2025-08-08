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

    public function register(): Response
    {
        $data = $this->getRequestData();

        $validator = new Validator($data);
        $validator
            ->required('name')->min('name', 3)
            ->required('email')->email('email')->unique('email', 'users')
            ->required('password')->min('password', 6);

        if ($validator->fails()) {
            return Response::validationError($validator->getErrors());
        }

        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        $userId = $this->userModel->create($data);
        if (!$userId) {
            return Response::error('Failed to create user');
        }

        $user = $this->userModel->find($userId);
        $tokens = $this->generateTokens($user);

        return Response::success([
            'user' => $user,
            'tokens' => $tokens
        ], 'User registered successfully', 201);
    }

    public function login(): Response
    {
        $data = $this->getRequestData();

        $validator = new Validator($data);
        $validator
            ->required('email')->email('email')
            ->required('password');

        if ($validator->fails()) {
            return Response::validationError($validator->getErrors());
        }

        $user = $this->userModel->findBy('email', $data['email']);

        if (!$user || !password_verify($data['password'], $user['password'])) {
            return Response::error('Invalid credentials', 401);
        }

        $tokens = $this->generateTokens($user);

        return Response::success([
            'user' => $user,
            'tokens' => $tokens
        ], 'Login successful');
    }

    public function refresh(): Response
    {
        $data = $this->getRequestData();

        if (empty($data['refresh_token'])) {
            return Response::error('Refresh token is required', 400);
        }

        $tokens = $this->jwtService->refreshTokens($data['refresh_token']);
        if (!$tokens) {
            return Response::error('Invalid refresh token', 401);
        }

        return Response::success(['tokens' => $tokens], 'Tokens refreshed successfully');
    }

    public function me(): Response
    {
        $this->requireAuth();

        return Response::success([
            'user' => $this->user
        ]);
    }

    public function logout(): Response
    {
        $this->requireAuth();
        return Response::success([], 'Logged out successfully');
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
