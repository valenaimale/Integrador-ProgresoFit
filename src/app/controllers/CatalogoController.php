<?php

namespace PAW\app\controllers;

use PAW\Core\Controller;

class CatalogoController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function listar()
    {
        $this->render('rutinas.html.twig', ['rutinas' => []]);
    }
}
