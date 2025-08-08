<?php

use Fcker\Framework\Core\Response;
use Fcker\Framework\Exceptions\HttpException;

if (!function_exists('response')) {
    function response() {
        return new class {
            public function json(array $data, int $status = 200, array $headers = []): Response
            {
                return Response::json($data, $status, $headers);
            }
            public function noContent(int $status = 204): Response
            {
                return Response::noContent($status);
            }
            public function success(array $data = [], string $message = 'Success', int $status = 200): Response
            {
                return Response::success($data, $message, $status);
            }
            public function error(string $message = 'Error', int $status = 400, array $errors = []): Response
            {
                return Response::error($message, $status, $errors);
            }
        };
    }
}

if (!function_exists('abort')) {
    function abort(int $status, string $message = '', array $payload = []): never
    {
        throw new HttpException($status, $message, $payload);
    }
}
