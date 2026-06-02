<?php

namespace PAW\app\controllers;

use PAW\app\services\ApiClient;

class AforoController
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

        $rol = $_SESSION['user']['rol'] ?? '';
        if ($rol !== 'ADMIN' && $rol !== 'ENTRENADOR') {
            header('Location: /');
            exit;
        }

        // Por ahora se consulta el gimnasio con id=1 (Gimnasio Central)
        // En una futura iteración se puede obtener el gimnasio del entrenador logueado
        $gimnasioId = 1;
        $aforo      = null;
        $error      = null;

        $response = $this->api->get("/accesos/aforo/{$gimnasioId}", $_SESSION['jwt']);

        if ($response['ok']) {
            $aforo = $response['data'];
        } else {
            $error = $response['data']['error'] ?? 'Error al obtener el aforo';
        }

        require $this->viewsDir . 'aforo.view.php';
    }
}
