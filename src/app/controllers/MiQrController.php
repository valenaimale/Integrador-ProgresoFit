<?php

namespace PAW\app\controllers;

use PAW\app\services\ApiClient;

class MiQrController
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

        if ($_SESSION['user']['rol'] !== 'ALUMNO') {
            header('Location: /');
            exit;
        }

        $qrData = null;
        $error  = null;

        $response = $this->api->post('/accesos/mi-qr', [], $_SESSION['jwt']);

        if ($response['ok']) {
            $qrData = $response['data'];
        } else {
            $error = $response['data']['error'] ?? 'Error al generar el QR';
        }

        require $this->viewsDir . 'miQr.view.php';
    }

    public function regenerar()
    {
        if (empty($_SESSION['user'])) {
            header('Location: /inicio-sesion');
            exit;
        }

        if ($_SESSION['user']['rol'] !== 'ALUMNO') {
            header('Location: /');
            exit;
        }

        // Mismo flujo: generar un nuevo QR y mostrarlo
        $this->mostrar();
    }
}
