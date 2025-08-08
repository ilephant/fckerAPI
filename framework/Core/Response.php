<?php

namespace Fcker\Framework\Core;

class Response
{
    private int $status;
    private array $headers = [];
    private string $body = '';

    public function __construct(string $body = '', int $status = 200, array $headers = [])
    {
        $this->status = $status;
        $this->headers = $headers;
        $this->body = $body;
    }

    public static function json(array $data, int $status = 200, array $headers = []): self
    {
        $headers = array_merge([
            'Content-Type' => 'application/json; charset=utf-8',
        ], $headers);

        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        return new self($json, $status, $headers);
    }

    public static function success(array $data = [], string $message = 'Success', int $status = 200): self
    {
        return self::json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    public static function error(string $message = 'Error', int $status = 400, array $errors = []): self
    {
        return self::json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $status);
    }

    public static function unauthorized(string $message = 'Unauthorized'): self
    {
        return self::error($message, 401);
    }

    public static function forbidden(string $message = 'Forbidden'): self
    {
        return self::error($message, 403);
    }

    public static function notFound(string $message = 'Not found'): self
    {
        return self::error($message, 404);
    }

    public static function validationError(array $errors, string $message = 'Validation failed'): self
    {
        return self::error($message, 422, $errors);
    }

    public static function noContent(int $status = 200): self
    {
        return new self('', $status, []);
    }

    public function withHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function withHeaders(array $headers): self
    {
        foreach ($headers as $k => $v) {
            $this->headers[$k] = $v;
        }
        return $this;
    }

    public function withStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }
        echo $this->body;
        exit;
    }
} 