<?php

namespace PAW\app\controllers;

use PAW\Core\Controller;
use PAW\app\services\ApiClient;

class AforoController extends Controller
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

        $rol = $_SESSION['user']['rol'] ?? '';
        if ($rol !== 'ADMIN' && $rol !== 'ENTRENADOR') {
            header('Location: /');
            exit;
        }

        $gimnasioId = 1;
        $aforo      = null;
        $error      = null;
        $porcentaje = 0;
        $estado     = 'libre';

        $response = $this->api->get("/accesos/aforo/{$gimnasioId}", $_SESSION['jwt']);

        if ($response['ok']) {
            $aforo      = $response['data'];
            $ocupacion  = (int) $aforo['ocupacion_actual'];
            $capacidad  = (int) $aforo['capacidad_maxima'];
            $porcentaje = $capacidad > 0 ? round($ocupacion / $capacidad * 100) : 0;
            $estado     = match(true) {
                $porcentaje >= 90 => 'lleno',
                $porcentaje >= 60 => 'moderado',
                default           => 'libre',
            };
        } else {
            $error = $response['data']['error'] ?? 'Error al obtener el aforo';
        }

        $this->render('aforo.html.twig', [
            'aforo'      => $aforo,
            'error'      => $error,
            'porcentaje' => $porcentaje,
            'estado'     => $estado,
        ]);
    }
}
