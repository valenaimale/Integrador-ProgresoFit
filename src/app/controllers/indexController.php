<?php

namespace PAW\app\controllers;

use PAW\Core\Controller;

class IndexController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->render('index.html.twig');
    }
}
