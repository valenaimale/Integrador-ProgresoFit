<?php

namespace PAW\app\controllers;

use PAW\Core\Controller;
use PAW\app\services\ApiClient;

class MiQrController extends Controller
{
    private ApiClient $api;

    public function __construct()
    {
        parent::__construct();
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

        $this->render('miQr.html.twig', ['qrData' => $qrData, 'error' => $error]);
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

        $this->mostrar();
    }
}
