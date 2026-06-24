<?php

namespace PAW\app\controllers;

use PAW\Core\Controller;
use PAW\app\services\ApiClient;

class PerfilEntrenadorController extends Controller
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
        $userId  = $_SESSION['user']['id'];
        $jwt     = $_SESSION['jwt'];

        $response = $this->api->get("/usuarios/{$userId}", $jwt);
        $userData = $response['ok'] ? $response['data'] : $_SESSION['user'];

        $entResponse = $this->api->get("/entrenadores/{$userId}", $jwt);
        if ($entResponse['ok']) {
            $userData = array_merge($userData, $entResponse['data']);
        }

        $this->render('perfilEntrenador.html.twig', ['userData' => $userData]);
    }
}
