<?php

namespace PAW\app\controllers;

use PAW\Core\Controller;
use PAW\app\services\ApiClient;

class EjercicioController extends Controller
{
    private ApiClient $api;

    public function __construct()
    {
        parent::__construct();
        $this->api = new ApiClient();
    }

    public function listar()
    {
        $offset    = max(0, (int)($_GET['offset'] ?? 0));
        $response  = $this->api->get("/ejercicios?limit=20&offset={$offset}", $_SESSION['jwt'] ?? null);
        $resultado = $response['ok'] ? $response['data'] : ['total' => 0, 'ejercicios' => []];
        $ejercicios = $resultado['ejercicios'] ?? [];
        $total      = $resultado['total'] ?? 0;

        $this->render('ejercicios.html.twig', [
            'ejercicios' => $ejercicios,
            'total'      => $total,
            'offset'     => $offset,
        ]);
    }

    public function mostrar()
    {
        $apiId     = $_GET['id'] ?? null;
        $response  = $this->api->get("/ejercicios/{$apiId}", $_SESSION['jwt'] ?? null);
        $ejercicio = $response['ok'] ? $response['data'] : null;

        $this->render('ejercicio.html.twig', ['ejercicio' => $ejercicio]);
    }

    public function mostrarLocal()
    {
        $id        = $_GET['id'] ?? null;
        $response  = $this->api->get("/ejercicios/local/{$id}", $_SESSION['jwt'] ?? null);
        $ejercicio = $response['ok'] ? $response['data'] : null;

        $this->render('ejercicio.html.twig', ['ejercicio' => $ejercicio]);
    }
}
