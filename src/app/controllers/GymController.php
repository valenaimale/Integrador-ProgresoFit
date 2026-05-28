<?php

namespace PAW\app\controllers;

use PAW\app\services\ApiClient;

class GymController
{
    public string $viewsDir;
    private ApiClient $api;

    public function __construct()
    {
        $this->viewsDir = __DIR__ . '/../views/';
        $this->api = new ApiClient();
    }

    public function listar()
    {
        $token     = $_SESSION['jwt'] ?? null;
        $response  = $this->api->get('/gimnasios', $token);
        $gimnasios = $response['ok'] ? $response['data'] : [];

        require $this->viewsDir . 'gimnasios.view.php';
    }
}
