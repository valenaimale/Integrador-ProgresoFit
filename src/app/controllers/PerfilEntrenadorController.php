<?php

namespace PAW\app\controllers;

use PAW\Core\Controller;

class PerfilEntrenadorController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function mostrar()
    {
        $this->render('perfilEntrenador.html.twig');
    }
}
