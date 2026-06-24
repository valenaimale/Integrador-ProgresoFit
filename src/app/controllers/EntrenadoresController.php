<?php

namespace PAW\app\controllers;

use PAW\app\services\ApiClient;
use PAW\app\services\TwigRenderer;

class EntrenadoresController
{
    private ApiClient $api;
    private TwigRenderer $twig;

    public function __construct()
    {
        $this->api  = new ApiClient();
        $this->twig = new TwigRenderer();
    }

    private function requireLogin(): void
    {
        if (empty($_SESSION['user'])) {
            header('Location: /inicio-sesion');
            exit;
        }
    }

    private function token(): string
    {
        return $_SESSION['jwt'] ?? '';
    }
    public function mostrarEntrenador(): void
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: /entrenadores');
            exit;
        }

        $response = $this->api->get("/entrenadores/{$id}", $this->token());
        if (!$response['ok']) {
            header('Location: /entrenadores');
            exit;
        }

        $this->twig->render('entrenadores/perfil.twig', [
            'entrenador' => $response['data'],
        ]);
    }
    public function listar(): void
    {
        $this->requireLogin();
        $entrenadoresRes   = $this->api->get('/entrenadores', $this->token());
        $misEntrenadoresIds = [];

        if ($_SESSION['user']['rol'] === 'ALUMNO') {
            // IDs de entrenadores a los que ya está suscripto
            $misRes = $this->api->get('/usuarios/mis-entrenadores', $this->token());
            if ($misRes['ok']) {
                $misEntrenadoresIds = array_column($misRes['data'], 'id');
            }
        }

        $this->twig->render('entrenadores/lista.twig', [
            'entrenadores'       => $entrenadoresRes['ok'] ? $entrenadoresRes['data'] : [],
            'mis_entrenadores_ids' => $misEntrenadoresIds,
            'es_alumno'          => $_SESSION['user']['rol'] === 'ALUMNO',
        ]);
    }

    public function suscribirse(): void
    {
        $this->requireLogin();
        $entrenadorId = (int) ($_POST['entrenador_id'] ?? 0);
        $this->api->post("/entrenadores/{$entrenadorId}/suscribirse", [], $this->token());
        header('Location: /entrenadores');
        exit;
    }

    public function desuscribirse(): void
    {
        $this->requireLogin();
        $entrenadorId = (int) ($_POST['entrenador_id'] ?? 0);
        $this->api->delete("/entrenadores/{$entrenadorId}/suscribirse", $this->token());
        header('Location: /entrenadores');
        exit;
    }
}
