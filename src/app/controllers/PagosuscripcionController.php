<?php

namespace PAW\app\controllers;

use PAW\Core\Controller;
use PAW\app\services\ApiClient;

class PagosuscripcionController extends Controller
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

        $resPlanes = $this->api->get('/suscripciones/planes', $_SESSION['jwt']);
        $planes = $resPlanes['ok'] ? $resPlanes['data'] : [];

        $this->render('pagosuscripcion.html.twig', [
            'planes' => $planes,
            'mensaje' => $_GET['mensaje'] ?? null,
            'error'   => $_GET['error'] ?? null
        ]);
    }

    public function suscribir()
    {
        if (empty($_SESSION['user'])) {
            header('Location: /inicio-sesion');
            exit;
        }

        $plan = $_POST['plan'] ?? null;

        if (!$plan) {
            header('Location: /pago-suscripcion?error=Debe seleccionar un plan');
            exit;
        }

        $response = $this->api->post('/suscripciones', ['plan' => $plan], $_SESSION['jwt']);

        if ($response['ok']) {
            header('Location: /perfil?mensaje=Suscripción exitosa');
        } else {
            $error = $response['data']['error'] ?? 'Error al procesar la suscripción';
            header("Location: /pago-suscripcion?error=" . urlencode($error));
        }
        exit;
    }
}
