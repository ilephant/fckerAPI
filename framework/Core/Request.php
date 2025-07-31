<?php

namespace Fcker\Framework\Core;

class Request
{
    private static ?self $_instance = null;
    private static ?string $_controller = null;
    private static ?string $_method = null;
    private static array $_params = [];
    private static string $_path = '';

    public static function getInstance(): self
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct()
    {
        $uri = urldecode(
            parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
        );

        $parts = explode('/', $uri);
        $parts = array_filter($parts);

        $this->setRequests($parts);
    }

    private function setRequests(array $parts): void
    {
        // Убираем первые пустые элементы
        $parts = array_values(array_filter($parts));
        
        self::$_path = '/' . implode('/', $parts);
        self::$_controller = array_shift($parts) ?: 'index';
        self::$_method = array_shift($parts) ?: 'index';
        self::$_params = $parts;
    }

    public function getController(): string
    {
        return self::$_controller;
    }

    public function getMethod(): string
    {
        return self::$_method;
    }

    public function getParams(): array
    {
        return self::$_params;
    }

    public function getPath(): string
    {
        return self::$_path;
    }

    public function getHttpMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function getHeaders(): array
    {
        return getallheaders();
    }

    public function getHeader(string $name): ?string
    {
        $headers = $this->getHeaders();
        return $headers[$name] ?? null;
    }
} 