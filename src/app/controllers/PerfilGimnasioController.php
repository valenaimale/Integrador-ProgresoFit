<?php

namespace PAW\app\controllers;

use PAW\Core\Controller;
use PAW\app\services\ApiClient;

class PerfilGimnasioController extends Controller
{
    private ApiClient $api;

    public function __construct()
    {
        parent::__construct();
        $this->api = new ApiClient();
    }

    public function mostrar()
    {
        $id       = (int) ($_GET['id'] ?? 1);
        $token    = $_SESSION['jwt'] ?? null;
        $response = $this->api->get("/gimnasios/{$id}", $token);
        $gimnasio = $response['ok'] ? $response['data'] : null;

        $aforo      = null;
        $porcentaje = 0;
        $estado     = 'libre';

        if ($token) {
            $aforoResp = $this->api->get("/accesos/aforo/{$id}", $token);
            if ($aforoResp['ok']) {
                $aforo      = $aforoResp['data'];
                $ocupacion  = (int) $aforo['ocupacion_actual'];
                $capacidad  = (int) $aforo['capacidad_maxima'];
                $porcentaje = $capacidad > 0 ? round($ocupacion / $capacidad * 100) : 0;
                $estado     = match(true) {
                    $porcentaje >= 90 => 'lleno',
                    $porcentaje >= 60 => 'moderado',
                    default           => 'libre',
                };
            }
        }

        $this->render('perfilGimnasio.html.twig', [
            'gimnasio'   => $gimnasio,
            'aforo'      => $aforo,
            'porcentaje' => $porcentaje,
            'estado'     => $estado,
        ]);
    }
}
