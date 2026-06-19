<?php

namespace PAW\app\controllers;

use PAW\Core\Controller;
use PAW\app\services\ApiClient;

class RutinaIndController extends Controller
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

        $this->render('rutina_ind.html.twig', ['rutina' => $rutina]);
    }
}
