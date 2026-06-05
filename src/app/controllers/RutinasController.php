<?php

namespace PAW\app\controllers;

use PAW\Core\Controller;
use PAW\app\services\ApiClient;

class RutinasController extends Controller
{
    private ApiClient $api;

    public function __construct()
    {
        parent::__construct();
        $this->api = new ApiClient();
    }

    public function listar()
    {
        $response = $this->api->get('/rutinas', $_SESSION['jwt'] ?? null);
        $rutinas  = $response['ok'] ? $response['data'] : [];

        $this->render('rutinas.html.twig', ['rutinas' => $rutinas]);
    }
}
