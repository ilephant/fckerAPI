<?php

namespace Fcker\Framework\Middleware;

use Fcker\Framework\Core\Request;
use Fcker\Framework\Services\JwtService;

class AuthMiddleware implements MiddlewareInterface
{
    private JwtService $jwtService;

    public function __construct()
    {
        $this->jwtService = new JwtService();
    }

    public function handle(Request $request, callable $next): void
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            abort(401, 'Bearer token required');
        }

        $token = substr($authHeader, 7);
        $payload = $this->jwtService->verifyToken($token);

        if (!$payload || isset($payload['type'])) {
            abort(401, 'Invalid or expired token');
        }

        $request->setAttribute('user', $payload);
        $next();
    }
} 