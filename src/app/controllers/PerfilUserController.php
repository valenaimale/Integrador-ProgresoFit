<?php

namespace PAW\app\controllers;

use PAW\app\services\ApiClient;

class PerfilUserController
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
        if (empty($_SESSION['user'])) {
            header('Location: /inicio-sesion');
            exit;
        }

        $userId   = $_SESSION['user']['id'];
        $response = $this->api->get("/usuarios/{$userId}", $_SESSION['jwt']);

        $userData = $response['ok'] ? $response['data'] : $_SESSION['user'];

        require $this->viewsDir . 'perfilUser.view.php';
    }
}
