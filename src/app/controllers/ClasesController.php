<?php

namespace PAW\app\controllers;

use PAW\Core\Controller;
use PAW\app\services\ApiClient;

class ClasesController extends Controller
{
    private ApiClient $api;

    public function __construct()
    {
        parent::__construct();
        $this->api = new ApiClient();
        $this->requireAlumno();
    }

    // ── Guards ────────────────────────────────────────────────────────

    private function requireAlumno(): void
    {
        if (empty($_SESSION['user']) || $_SESSION['user']['rol'] !== 'ALUMNO') {
            header('Location: /inicio-sesion');
            exit;
        }
    }

    private function token(): string
    {
        return $_SESSION['jwt'] ?? '';
    }

    // ── Listado de clases disponibles ─────────────────────────────────

    public function listar(): void
    {
        $response   = $this->api->get('/clases/disponibles', $this->token());
        $misResp    = $this->api->get('/clases/mis-inscripciones', $this->token());

        // IDs de clases en las que ya está anotado, para marcar el estado en la vista.
        $inscriptoIds = [];
        if ($misResp['ok']) {
            foreach ($misResp['data'] as $c) {
                $inscriptoIds[] = (int) $c['id'];
            }
        }

        $this->render('clases/listado.html.twig', [
            'clases'        => $response['ok'] ? $response['data'] : [],
            'inscriptoIds'  => $inscriptoIds,
            'error'         => $response['ok'] ? null : 'No se pudieron cargar las clases.',
            'flash'         => $_GET['msg']  ?? null,
            'flashError'    => $_GET['err']  ?? null,
        ]);
    }

    public function misClases(): void
    {
        $response = $this->api->get('/clases/mis-inscripciones', $this->token());

        $this->render('clases/mis-clases.html.twig', [
            'clases' => $response['ok'] ? $response['data'] : [],
            'error'  => $response['ok'] ? null : 'No se pudieron cargar tus clases.',
            'flash'  => $_GET['msg'] ?? null,
        ]);
    }

    // ── Inscribir / cancelar ──────────────────────────────────────────

    public function inscribir(): void
    {
        $id       = (int) ($_POST['id'] ?? 0);
        $response = $this->api->post("/clases/{$id}/inscribir", [], $this->token());

        if ($response['ok']) {
            header('Location: /clases?msg=' . urlencode('Te anotaste en la clase.'));
            exit;
        }

        $error = $response['data']['error'] ?? 'No se pudo completar la inscripción.';
        header('Location: /clases?err=' . urlencode($error));
        exit;
    }

    public function cancelar(): void
    {
        $id     = (int) ($_POST['id'] ?? 0);
        $origen = $_POST['origen'] ?? 'clases';
        $this->api->delete("/clases/{$id}/inscribir", $this->token());

        $destino = $origen === 'mis-clases' ? '/mis-clases' : '/clases';
        header('Location: ' . $destino . '?msg=' . urlencode('Cancelaste tu inscripción.'));
        exit;
    }
}
