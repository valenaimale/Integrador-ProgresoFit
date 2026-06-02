<?php

namespace PAW\app\controllers;

use PAW\app\services\ApiClient;

class RutinaIndController
{
    public string $viewsDir;
    private ApiClient $api;

    public function __construct()
    {
        $this->viewsDir = __DIR__ . '/../views/';
        $this->api = new ApiClient();
    }

    public function mostrar()
    {
        $id       = $_GET['id'] ?? null;
        $response = $this->api->get("/rutinas/{$id}", $_SESSION['jwt'] ?? null);
        $rutina   = $response['ok'] ? $response['data'] : null;

        require $this->viewsDir . 'rutina_ind.view.php';
    }
}
