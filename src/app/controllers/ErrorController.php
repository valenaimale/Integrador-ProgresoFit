<?php

namespace PAW\App\Controllers;

use PAW\Core\Controller;

class ErrorController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function notFound()
    {
        http_response_code(404);
        $this->render('error.html.twig', ['mensaje_error' => 'Page Not Found: Error 404']);
    }

    public function internalError()
    {
        http_response_code(500);
        $this->render('error.html.twig', ['mensaje_error' => 'Internal Error: 500']);
    }
}
