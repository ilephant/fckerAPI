<?php

namespace Fcker\Framework\Middleware;

use Fcker\Framework\Core\Response;
use Fcker\Framework\Services\JwtService;

class AuthMiddleware
{
    private JwtService $jwtService;

    public function __construct()
    {
        $this->jwtService = new JwtService();
    }

    public function handle(): ?array
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            Response::unauthorized('Bearer token required');
        }

        $token = substr($authHeader, 7);
        $payload = $this->jwtService->verifyToken($token);
        
        if (!$payload || isset($payload['type'])) {
            Response::unauthorized('Invalid or expired token');
        }

        return $payload;
    }
} 