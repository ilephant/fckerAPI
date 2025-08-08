<?php

namespace Fcker\Framework\Services;

use Exception;
use Fcker\Framework\Contracts\TokenServiceInterface;

class JwtService implements TokenServiceInterface
{
    private string $secret;
    private string $algorithm;
    private int $accessTokenExpiry;
    private int $refreshTokenExpiry;
    private string $issuer;
    private string $audience;

    public function __construct()
    {
        $this->secret = getenv('JWT_SECRET') ?: 'default-secret-key';
        $this->algorithm = 'HS256';
        $this->accessTokenExpiry = (int)(getenv('JWT_ACCESS_EXPIRY') ?: 3600);
        $this->refreshTokenExpiry = (int)(getenv('JWT_REFRESH_EXPIRY') ?: 86400);
        $this->issuer = getenv('JWT_ISSUER') ?: 'fcker-api';
        $this->audience = getenv('JWT_AUDIENCE') ?: 'fcker-clients';
    }

    public function generateAccessToken(array $payload): string
    {
        $header = [
            'typ' => 'JWT',
            'alg' => $this->algorithm
        ];

        $payload['iat'] = time();
        $payload['exp'] = time() + $this->accessTokenExpiry;
        $payload['iss'] = $this->issuer;
        $payload['aud'] = $this->audience;

        return $this->encode($header, $payload);
    }

    public function generateRefreshToken(array $payload): string
    {
        $header = [
            'typ' => 'JWT',
            'alg' => $this->algorithm
        ];

        $payload['iat'] = time();
        $payload['exp'] = time() + $this->refreshTokenExpiry;
        $payload['iss'] = $this->issuer;
        $payload['aud'] = $this->audience;
        $payload['type'] = 'refresh';

        return $this->encode($header, $payload);
    }

    public function verifyToken(string $token): ?array
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return null;
            }

            [$headerB64, $payloadB64, $signatureB64] = $parts;

            $header = json_decode($this->base64UrlDecode($headerB64), true);
            $payload = json_decode($this->base64UrlDecode($payloadB64), true);

            if (!$header || !$payload) {
                return null;
            }

            $expectedSignature = $this->base64UrlEncode(
                hash_hmac('sha256', $headerB64 . '.' . $payloadB64, $this->secret, true)
            );

            if (!hash_equals($signatureB64, $expectedSignature)) {
                return null;
            }

            if (isset($payload['exp']) && $payload['exp'] < time()) {
                return null;
            }

            return $payload;
        } catch (Exception $e) {
            return null;
        }
    }

    public function refreshTokens(string $refreshToken): ?array
    {
        $payload = $this->verifyToken($refreshToken);

        if (!$payload || !isset($payload['type']) || $payload['type'] !== 'refresh') {
            return null;
        }

        unset($payload['iat'], $payload['exp'], $payload['iss'], $payload['aud'], $payload['type']);

        return [
            'access_token' => $this->generateAccessToken($payload),
            'refresh_token' => $this->generateRefreshToken($payload)
        ];
    }

    private function encode(array $header, array $payload): string
    {
        $headerB64 = $this->base64UrlEncode(json_encode($header));
        $payloadB64 = $this->base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', $headerB64 . '.' . $payloadB64, $this->secret, true);
        $signatureB64 = $this->base64UrlEncode($signature);

        return $headerB64 . '.' . $payloadB64 . '.' . $signatureB64;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        $data = strtr($data, '-_', '+/');
        $remainder = strlen($data) % 4;

        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode($data);
    }
}
