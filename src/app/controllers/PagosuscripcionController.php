<?php

namespace PAW\app\controllers;

use PAW\Core\Controller;

class PagosuscripcionController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function mostrar()
    {
        $this->render('pagosuscripcion.html.twig');
    }
}
