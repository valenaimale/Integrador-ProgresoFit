<?php

namespace PAW\app\controllers;

use PAW\Core\Controller;
use PAW\app\services\ApiClient;

class GymController extends Controller
{
    private ApiClient $api;

    public function __construct()
    {
        parent::__construct();
        $this->api = new ApiClient();
    }

    public function listar()
    {
        if (empty($_SESSION['user'])) {
            header('Location: /inicio-sesion');
            exit;
        }

        $token     = $_SESSION['jwt'] ?? null;
        $response  = $this->api->get('/gimnasios', $token);
        $gimnasios = $response['ok'] ? $response['data'] : [];

        $this->render('gimnasios.html.twig', ['gimnasios' => $gimnasios]);
    }
}
