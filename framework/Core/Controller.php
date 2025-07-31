<?php

namespace Fcker\Framework\Core;

use Fcker\Framework\Services\JwtService;

abstract class Controller
{
    protected JwtService $jwtService;
    protected array $user = [];

    public function __construct()
    {
        $this->jwtService = new JwtService();
        $this->handleCors();
        $this->authenticate();
    }

    protected function handleCors(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
            header('Access-Control-Max-Age: 86400');
            exit;
        }
    }

    protected function authenticate(): void
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return;
        }

        $token = substr($authHeader, 7);
        $payload = $this->jwtService->verifyToken($token);
        
        if ($payload && !isset($payload['type'])) {
            $this->user = $payload;
        }
    }

    protected function requireAuth(): void
    {
        if (empty($this->user)) {
            Response::unauthorized('Authentication required');
        }
    }

    protected function getRequestData(): array
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        return $data ?: [];
    }

    protected function getQueryParams(): array
    {
        return $_GET;
    }

    protected function validate(array $data, array $rules): array
    {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            if (str_contains($rule, 'required') && empty($value)) {
                $errors[$field][] = "The {$field} field is required.";
            }
            
            if (!empty($value)) {
                if (str_contains($rule, 'email') && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field][] = "The {$field} must be a valid email address.";
                }
                
                if (str_contains($rule, 'min:') && preg_match('/min:(\d+)/', $rule, $matches)) {
                    $min = (int) $matches[1];
                    if (strlen($value) < $min) {
                        $errors[$field][] = "The {$field} must be at least {$min} characters.";
                    }
                }
            }
        }
        
        return $errors;
    }
}
