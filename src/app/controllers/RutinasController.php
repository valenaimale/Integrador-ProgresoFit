<?php

namespace PAW\app\controllers;

use PAW\app\services\ApiClient;

class RutinasController
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
        $response = $this->api->get('/rutinas', $_SESSION['jwt'] ?? null);
        $rutinas  = $response['ok'] ? $response['data'] : [];

        require $this->viewsDir . 'rutinas.view.php';
    }
}
