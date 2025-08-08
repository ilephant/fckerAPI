<?php

namespace Fcker\Framework\Core;

class Request
{
    private static ?self $_instance = null;
    private static ?string $_controller = null;
    private static ?string $_method = null;
    private static array $_params = [];
    private static string $_path = '';
    private array $_attributes = [];

    public static function getInstance(): self
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct()
    {
        $uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
        $parts = explode('/', $uri);
        $parts = array_filter($parts);
        $this->setRequests($parts);
    }

    private function setRequests(array $parts): void
    {
        $parts = array_values(array_filter($parts));
        self::$_path = '/' . implode('/', $parts);
        self::$_controller = array_shift($parts) ?: 'index';
        self::$_method = array_shift($parts) ?: 'index';
        self::$_params = $parts;
    }

    public function getController(): string { return self::$_controller; }
    public function getMethod(): string { return self::$_method; }
    public function getParams(): array { return self::$_params; }
    public function getPath(): string { return self::$_path; }
    public function getHttpMethod(): string { return $_SERVER['REQUEST_METHOD'] ?? 'GET'; }

    public function getHeaders(): array { return function_exists('getallheaders') ? getallheaders() : []; }
    public function getHeader(string $name): ?string
    {
        $headers = $this->getHeaders();
        return $headers[$name] ?? null;
    }

    // Атрибуты запроса (для прокидывания user и т.п.)
    public function setAttribute(string $key, mixed $value): void { $this->_attributes[$key] = $value; }
    public function getAttribute(string $key, mixed $default = null): mixed { return $this->_attributes[$key] ?? $default; }
} 