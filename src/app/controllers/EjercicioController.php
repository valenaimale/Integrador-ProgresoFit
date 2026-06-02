<?php

namespace PAW\app\controllers;

use PAW\app\services\ApiClient;

class EjercicioController
{
    public string $viewsDir;
    private ApiClient $api;

    public function __construct()
    {
        $this->viewsDir = __DIR__ . '/../views/';
        $this->api = new ApiClient();
    }

    public function listar()
    {
        $offset    = max(0, (int)($_GET['offset'] ?? 0));
        $response  = $this->api->get("/ejercicios?limit=20&offset={$offset}", $_SESSION['jwt'] ?? null);
        $resultado = $response['ok'] ? $response['data'] : ['total' => 0, 'ejercicios' => []];
        $ejercicios = $resultado['ejercicios'] ?? [];
        $total      = $resultado['total'] ?? 0;

        require $this->viewsDir . 'ejercicios.view.php';
    }

    public function mostrar()
    {
        $apiId     = $_GET['id'] ?? null;
        $response  = $this->api->get("/ejercicios/{$apiId}", $_SESSION['jwt'] ?? null);
        $ejercicio = $response['ok'] ? $response['data'] : null;

        require $this->viewsDir . 'ejercicio.view.php';
    }

    public function mostrarLocal()
    {
        $id        = $_GET['id'] ?? null;
        $response  = $this->api->get("/ejercicios/local/{$id}", $_SESSION['jwt'] ?? null);
        $ejercicio = $response['ok'] ? $response['data'] : null;

        require $this->viewsDir . 'ejercicio.view.php';
    }
}
