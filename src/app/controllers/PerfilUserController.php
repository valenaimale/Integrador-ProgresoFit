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
        $jwt      = $_SESSION['jwt'];

        // Obtener datos básicos del perfil
        $response = $this->api->get("/usuarios/{$userId}", $jwt);
        $userData = $response['ok'] ? $response['data'] : $_SESSION['user'];

        $suscripcionActiva = null;
        $planes = [];

        if ($userData['rol'] === 'ALUMNO') {
            // Obtener suscripción activa
            $resSub = $this->api->get('/suscripciones/mi-suscripcion', $jwt);
            if ($resSub['ok']) {
                $suscripcionActiva = $resSub['data'];
            }

            // Obtener planes disponibles
            $resPlanes = $this->api->get('/suscripciones/planes', $jwt);
            if ($resPlanes['ok']) {
                $planes = $resPlanes['data'];
            }
        }

        require $this->viewsDir . 'perfilUser.view.php';
    }
}
