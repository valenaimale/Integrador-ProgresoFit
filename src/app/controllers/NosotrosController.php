<?php

namespace PAW\app\controllers;

use PAW\Core\Controller;

class NosotrosController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function mostrar_nosotros()
    {
        $this->render('nosotros.html.twig');
    }
}
