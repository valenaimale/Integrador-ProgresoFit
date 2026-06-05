<?php

namespace PAW\app\controllers;

use PAW\Core\Controller;
use PAW\app\services\ApiClient;

class RutinaIndController extends Controller
{
    private ApiClient $api;

    public function __construct()
    {
        parent::__construct();
        $this->api = new ApiClient();
    }

    public function mostrar()
    {
        $id       = $_GET['id'] ?? null;
        $response = $this->api->get("/rutinas/{$id}", $_SESSION['jwt'] ?? null);
        $rutina   = $response['ok'] ? $response['data'] : null;

        $this->render('rutina_ind.html.twig', ['rutina' => $rutina]);
    }
}
