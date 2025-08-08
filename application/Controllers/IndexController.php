<?php

namespace Fcker\Application\Controllers;

use Fcker\Framework\Core\Controller;
use Fcker\Framework\Core\Response;

class IndexController extends Controller
{
    public function index(): Response
    {
        return Response::noContent();
    }
}
