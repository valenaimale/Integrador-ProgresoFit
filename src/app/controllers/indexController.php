<?php

namespace PAW\app\controllers;


class IndexController
{
    public string $viewsDir;

    public function __construct()
    {
        $this->viewsDir = __DIR__ . '/../views/';

    }

    public function index()
    {
        require $this->viewsDir . 'index.view.php';
    }
}