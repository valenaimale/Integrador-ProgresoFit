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
        if (empty($_SESSION['user'])) {
            header('Location: /inicio-sesion');
            exit;
        }

        $rutinaId = $_GET['id'] ?? null;
        if (!$rutinaId) {
            header('Location: /rutinas');
            exit;
        }

        $response = $this->api->get("/rutinas/{$rutinaId}", $_SESSION['jwt']);
        
        if (!$response['ok']) {
            header('Location: /rutinas?error=No se pudo cargar la rutina');
            exit;
        }

        $rutina = $response['data'];

        require $this->viewsDir . 'rutina_ind.view.php';
    }
}
