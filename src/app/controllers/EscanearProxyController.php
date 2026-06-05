<?php

namespace PAW\app\controllers;

use PAW\app\services\ApiClient;

class EscanearProxyController
{
    private ApiClient $api;

    public function __construct()
    {
        $this->api = new ApiClient();
    }

    public function procesar()
    {
        if (empty($_SESSION['user']) || $_SESSION['user']['rol'] !== 'ADMIN') {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No autorizado']);
            exit;
        }

        $raw  = file_get_contents('php://input');
        $body = json_decode($raw, true);

        if (empty($body['token']) || empty($body['gimnasio_id'])) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Faltan campos requeridos']);
            exit;
        }

        $jwt      = $_SESSION['jwt'] ?? '';
        $response = $this->api->post('/accesos/escanear', $body, $jwt);

        http_response_code($response['status']);
        header('Content-Type: application/json');
        echo json_encode($response['data']);
        exit;
    }
}
