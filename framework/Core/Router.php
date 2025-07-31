<?php

namespace Fcker\Framework\Core;

class Router
{
    private static ?self $_instance = null;
    private array $routes = [];
    private Request $request;

    public function __construct()
    {
        $this->request = Request::getInstance();
    }

    public static function getInstance(): self
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function getRoute(): void
    {
        $method = $this->request->getHttpMethod();
        $path = $this->request->getPath();
        
        // Проверяем, есть ли точное совпадение маршрута
        $routeKey = $method . ':' . $path;
        
        if (isset($this->routes[$routeKey])) {
            $this->executeRoute($this->routes[$routeKey]);
            return;
        }
        
        // Если нет точного совпадения, используем старую логику
        $controller = ucfirst($this->request->getController()) . 'Controller';
        $action = $this->request->getMethod();
        $params = $this->request->getParams();

        $class = 'Fcker\Application\Controllers\\' . $controller;
        
        if (!class_exists($class)) {
            Response::notFound("Controller {$controller} not found");
        }

        $controllerInstance = new $class();
        
        if (!method_exists($controllerInstance, $action)) {
            Response::notFound("Method {$action} not found in {$controller}");
        }

        if (!empty($params)) {
            call_user_func_array([$controllerInstance, $action], $params);
        } else {
            call_user_func([$controllerInstance, $action]);
        }
    }

    public function addRoute(string $method, string $path, array $handler): void
    {
        $this->routes[$method . ':' . $path] = $handler;
    }

    public function get(string $path, array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, array $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, array $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    private function executeRoute(array $handler): void
    {
        [$controllerClass, $method] = $handler;
        
        if (!class_exists($controllerClass)) {
            Response::notFound("Controller {$controllerClass} not found");
        }

        $controller = new $controllerClass();
        
        if (!method_exists($controller, $method)) {
            Response::notFound("Method {$method} not found");
        }

        call_user_func([$controller, $method]);
    }
}
