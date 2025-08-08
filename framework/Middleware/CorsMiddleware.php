<?php

namespace Fcker\Framework\Middleware;

use Fcker\Framework\Core\Request;

class CorsMiddleware implements MiddlewareInterface
{
    private array $config;

    public function __construct(?array $config = null)
    {
        $path = __ROOT__ . 'config/cors.php';
        $fileCfg = is_file($path) ? require $path : [];
        $this->config = array_merge([
            'paths' => ['*'],
            'allowed_methods' => ['GET','POST','PUT','DELETE','OPTIONS'],
            'allowed_origins' => ['*'],
            'allowed_headers' => ['*'],
            'exposed_headers' => [],
            'max_age' => 86400,
            'supports_credentials' => false,
        ], $config ?? $fileCfg);
    }

    public function handle(Request $request, callable $next): void
    {
        $path = $request->getPath();
        if (!$this->pathMatches($path)) {
            $next();
            return;
        }

        $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
        $this->setCommonHeaders($origin);

        if ($request->getHttpMethod() === 'OPTIONS') {
            header('Access-Control-Allow-Methods: ' . implode(', ', $this->config['allowed_methods']));
            $reqHeaders = $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'] ?? null;
            header('Access-Control-Allow-Headers: ' . $this->headersLine($this->config['allowed_headers'], $reqHeaders));
            header('Access-Control-Max-Age: ' . $this->config['max_age']);
            http_response_code(204);
            exit;
        }

        $next();
    }

    private function setCommonHeaders(string $origin): void
    {
        header('Vary: Origin');
        $allowOrigin = $this->originAllowed($origin) ? $origin : '*';
        header('Access-Control-Allow-Origin: ' . $allowOrigin);
        if (!empty($this->config['exposed_headers'])) {
            header('Access-Control-Expose-Headers: ' . implode(', ', $this->config['exposed_headers']));
        }
        if ($this->config['supports_credentials']) {
            header('Access-Control-Allow-Credentials: true');
        }
    }

    private function originAllowed(string $origin): bool
    {
        $allowed = $this->config['allowed_origins'] ?? ['*'];
        return in_array('*', $allowed, true) || in_array($origin, $allowed, true);
    }

    private function headersLine(array $allowed, ?string $requested): string
    {
        if (in_array('*', $allowed, true)) {
            return $requested ?: '*';
        }
        return implode(', ', $allowed);
    }

    private function pathMatches(string $path): bool
    {
        foreach ($this->config['paths'] as $pattern) {
            if ($pattern === '*') return true;
            $regex = '#^' . str_replace('\*', '.*', preg_quote(ltrim($pattern, '/'), '#')) . '$#';
            if (preg_match($regex, ltrim($path, '/'))) return true;
        }
        return false;
    }
}
