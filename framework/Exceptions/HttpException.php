<?php

namespace Fcker\Framework\Exceptions;

use Exception;
use Fcker\Framework\Core\Response;

class HttpException extends Exception
{
    protected int $status;
    protected array $payload;

    public function __construct(int $status, string $message = '', array $payload = [])
    {
        $this->status = $status;
        $this->payload = $payload;
        parent::__construct($message ?: self::defaultMessage($status), $status);
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function toResponse(): Response
    {
        // Единый JSON-ответ об ошибке
        return Response::error($this->getMessage(), $this->status, $this->payload);
    }

    private static function defaultMessage(int $status): string
    {
        return match ($status) {
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            500 => 'Server Error',
            default => 'Error',
        };
    }
}
