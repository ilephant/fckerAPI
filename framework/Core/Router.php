<?php

namespace Fcker\Framework\Core;

use Fcker\Framework\Core\Response;
use Fcker\Framework\Exceptions\HttpException;

class Router
{
    private static ?self $_instance = null;
    private Request $request;

    private array $routes = [];
    private array $globalMiddleware = [];

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

    public function use(string $middlewareClass): void
    {
        $this->globalMiddleware[] = $middlewareClass;
    }

    public function addRoute(string $method, string $path, array $handler, array $middleware = []): void
    {
        [$regex, $paramNames] = $this->compilePath($path);
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'regex' => $regex,
            'paramNames' => $paramNames,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function get(string $path, array $handler, array $middleware = []): void { $this->addRoute('GET', $path, $handler, $middleware); }
    public function post(string $path, array $handler, array $middleware = []): void { $this->addRoute('POST', $path, $handler, $middleware); }
    public function put(string $path, array $handler, array $middleware = []): void { $this->addRoute('PUT', $path, $handler, $middleware); }
    public function delete(string $path, array $handler, array $middleware = []): void { $this->addRoute('DELETE', $path, $handler, $middleware); }

    public function getRoute(): void
    {
        $method = $this->request->getHttpMethod();
        $path = $this->normalizePath($this->request->getPath());

        $destination = function () use ($method, $path) {
            foreach ($this->routes as $route) {
                if ($route['method'] !== $method) continue;
                if (preg_match($route['regex'], $path, $matches)) {
                    $params = $this->buildParams($route['paramNames'], $matches);
                    $this->executeRouteWithMiddleware($route['handler'], $route['middleware'], $params);
                    return;
                }
            }
            $this->fallbackDispatch($method, $path);
        };

        $this->runGlobalMiddleware($destination);
    }

    private function runGlobalMiddleware(callable $destination): void
    {
        $next = $destination;
        foreach (array_reverse($this->globalMiddleware) as $middlewareClass) {
            $next = function () use ($middlewareClass, $next) {
                $mw = new $middlewareClass();
                $mw->handle($this->request, $next);
            };
        }
        $next();
    }

    private function executeRouteWithMiddleware(array $handler, array $middleware, array $params = []): void
    {
        [$controllerClass, $method] = $handler;

        if (!class_exists($controllerClass)) {
            throw new HttpException(404, "Controller {$controllerClass} not found");
        }
        $controller = new $controllerClass();

        if (!method_exists($controller, $method)) {
            throw new HttpException(404, "Method {$method} not found");
        }

        $destination = function () use ($controller, $method, $params) {
            $result = !empty($params)
                ? call_user_func_array([$controller, $method], $params)
                : call_user_func([$controller, $method]);

            if ($result instanceof Response) {
                $result->send();
            } elseif (is_array($result)) {
                Response::json($result)->send();
            } elseif (is_string($result)) {
                (new Response($result))->send();
            } elseif ($result === null) {
                // Ничего не вернули — считаем 204
                Response::noContent()->send();
            }
        };

        $next = $destination;
        foreach (array_reverse($middleware) as $middlewareClass) {
            $next = function () use ($middlewareClass, $next) {
                $mw = new $middlewareClass();
                $mw->handle($this->request, $next);
            };
        }

        $next();
    }

    private function compilePath(string $path): array
    {
        $path = $this->normalizePath($path);
        $segments = explode('/', trim($path, '/'));
        $paramNames = [];

        $regexParts = array_map(function ($seg) use (&$paramNames) {
            if (preg_match('/^\{([a-zA-Z_][a-zA-Z0-9_]*)\}$/', $seg, $m)) {
                $paramNames[] = $m[1];
                return '([^/]+)';
            }
            return preg_quote($seg, '#');
        }, $segments);

        $regex = '#^/' . implode('/', $regexParts) . '$#';
        return [$regex, $paramNames];
    }

    private function buildParams(array $paramNames, array $matches): array
    {
        array_shift($matches);
        $params = [];
        foreach ($paramNames as $idx => $name) {
            $params[$name] = $matches[$idx] ?? null;
        }
        return $params;
    }

    private function normalizePath(string $path): string
    {
        if ($path === '') return '/';
        return rtrim($path, '/') ?: '/';
    }

    private function fallbackDispatch(string $httpMethod, string $path): void
    {
        $parts = array_values(array_filter(explode('/', trim($path, '/'))));
        $controllerName = $parts[0] ?? 'index';
        $second = $parts[1] ?? 'index';
        $rest = array_slice($parts, 2);

        $class = 'Fcker\\Application\\Controllers\\' . ucfirst($controllerName) . 'Controller';

        if (!class_exists($class)) {
            throw new HttpException(404, "Controller " . ucfirst($controllerName) . "Controller not found");
        }

        $controller = new $class();

        if ($controllerName && $second !== 'index' && is_numeric($second) && count($rest) === 0) {
            $id = (int)$second;
            $method = match ($httpMethod) {
                'GET' => 'show',
                'PUT' => 'update',
                'DELETE' => 'destroy',
                default => null,
            };
            if ($method && method_exists($controller, $method)) {
                $res = call_user_func([$controller, $method], $id);
                if ($res instanceof Response) $res->send();
                return;
            }
        }

        if ($httpMethod === 'GET' && $second === 'index' && method_exists($controller, 'index')) {
            $res = call_user_func([$controller, 'index']);
            if ($res instanceof Response) $res->send();
            return;
        }

        if ($httpMethod === 'POST' && $second === 'index' && method_exists($controller, 'store')) {
            $res = call_user_func([$controller, 'store']);
            if ($res instanceof Response) $res->send();
            return;
        }

        $action = $second ?: 'index';
        if (!method_exists($controller, $action)) {
            throw new HttpException(404, "Method {$action} not found in " . get_class($controller));
        }

        $res = !empty($rest)
            ? call_user_func_array([$controller, $action], $rest)
            : call_user_func([$controller, $action]);

        if ($res instanceof Response) $res->send();
    }
}
