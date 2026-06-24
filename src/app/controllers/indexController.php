<?php

namespace PAW\app\controllers;

use PAW\Core\Controller;
use PAW\app\services\ApiClient;

class IndexController extends Controller
{
    private ApiClient $api;

    public function __construct()
    {
        parent::__construct();
        $this->api = new ApiClient();
    }

    public function index()
    {
        $token = $_SESSION['jwt'] ?? '';
        $response = $this->api->get('/entrenadores', $token);
        $entrenadores = $response['ok'] ? array_slice($response['data'], 0, 5) : [];
        $this->render('index.html.twig', ['entrenadores' => $entrenadores]);
    }
}
