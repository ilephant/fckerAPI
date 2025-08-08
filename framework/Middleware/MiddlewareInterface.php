<?php

namespace Fcker\Framework\Middleware;

use Fcker\Framework\Core\Request;

interface MiddlewareInterface
{
    public function handle(Request $request, callable $next): void;
}
