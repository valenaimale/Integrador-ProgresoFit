<?php

namespace PAW\app\controllers;

use PAW\app\services\ApiClient;

class PerfilGimnasioController
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
        $id       = (int) ($_GET['id'] ?? 1);
        $token    = $_SESSION['jwt'] ?? null;
        $response = $this->api->get("/gimnasios/{$id}", $token);
        $gimnasio = $response['ok'] ? $response['data'] : null;

        require $this->viewsDir . 'perfilGimnasio.view.php';
    }
}
