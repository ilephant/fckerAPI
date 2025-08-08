<?php

namespace Fcker\Framework\Core;

use Fcker\Framework\Contracts\TokenServiceInterface;
use Fcker\Framework\Services\JwtService;
use Fcker\Framework\Core\Response;

abstract class Controller
{
    protected TokenServiceInterface $jwtService;
    protected array $user = [];
    protected Request $request;

    public function __construct(?TokenServiceInterface $jwtService = null)
    {
        $this->jwtService = $jwtService ?? new JwtService();
        $this->request = Request::getInstance();
    }

    protected function requireAuth(): void
    {
        $user = $this->request->getAttribute('user');
        if (empty($user)) {
            Response::unauthorized('Authentication required');
        }
        $this->user = $user;
    }

    protected function getRequestData(): array
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        return $data ?: [];
    }

    protected function getQueryParams(): array
    {
        return $_GET;
    }
}
