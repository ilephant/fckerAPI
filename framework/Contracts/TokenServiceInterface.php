<?php

namespace Fcker\Framework\Contracts;

interface TokenServiceInterface
{
    public function generateAccessToken(array $payload): string;

    public function generateRefreshToken(array $payload): string;

    public function verifyToken(string $token): ?array;

    public function refreshTokens(string $refreshToken): ?array;
}
