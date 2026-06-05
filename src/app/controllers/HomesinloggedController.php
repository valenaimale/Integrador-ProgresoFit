<?php

namespace PAW\app\controllers;

use PAW\Core\Controller;

class HomesinloggedController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->render('homesinlogged.html.twig');
    }
}
