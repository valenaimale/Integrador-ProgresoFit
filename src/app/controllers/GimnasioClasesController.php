<?php

namespace PAW\app\controllers;

use PAW\Core\Controller;
use PAW\app\services\ApiClient;

class GimnasioClasesController extends Controller
{
    private ApiClient $api;

    public function __construct()
    {
        parent::__construct();
        $this->api = new ApiClient();
        $this->requireGimnasio();
    }

    // ── Guards ────────────────────────────────────────────────────────

    private function requireGimnasio(): void
    {
        $rol = $_SESSION['user']['rol'] ?? null;
        if (empty($_SESSION['user']) || !in_array($rol, ['GIMNASIO', 'ADMIN'], true)) {
            header('Location: /inicio-sesion');
            exit;
        }
    }

    private function token(): string
    {
        return $_SESSION['jwt'] ?? '';
    }

    // ── Listado ───────────────────────────────────────────────────────

    public function listar(): void
    {
        $response = $this->api->get('/clases/mias', $this->token());

        $this->render('clases/gimnasio-listado.html.twig', [
            'clases' => $response['ok'] ? $response['data'] : [],
            'error'  => $response['ok'] ? null : ($response['data']['error'] ?? 'No se pudieron cargar las clases.'),
            'flash'  => $_GET['msg'] ?? null,
        ]);
    }

    // ── Crear ─────────────────────────────────────────────────────────

    public function nueva(): void
    {
        $this->render('clases/gimnasio-form.html.twig');
    }

    public function crear(): void
    {
        $response = $this->api->post('/clases', [
            'nombre'      => $_POST['nombre']      ?? '',
            'descripcion' => $_POST['descripcion'] ?? '',
            'fecha'       => $_POST['fecha']       ?? '',
            'hora_inicio' => $_POST['hora_inicio'] ?? '',
            'hora_fin'    => $_POST['hora_fin']    ?? '',
            'cupo_maximo' => (int) ($_POST['cupo_maximo'] ?? 0),
        ], $this->token());

        if ($response['ok']) {
            header('Location: /gimnasio/clases?msg=' . urlencode('Clase creada.'));
            exit;
        }

        $this->render('clases/gimnasio-form.html.twig', [
            'error' => $response['data']['error'] ?? 'No se pudo crear la clase.',
            'clase' => $_POST,
        ]);
    }

    // ── Cancelar clase ────────────────────────────────────────────────

    public function cancelar(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $this->api->delete("/clases/{$id}", $this->token());
        header('Location: /gimnasio/clases?msg=' . urlencode('Clase cancelada.'));
        exit;
    }

    // ── Ver inscriptos ────────────────────────────────────────────────

    public function inscriptos(): void
    {
        $id        = (int) ($_GET['id'] ?? 0);
        $clasesRes = $this->api->get('/clases/mias', $this->token());
        $inscRes   = $this->api->get("/clases/{$id}/inscriptos", $this->token());

        // Buscamos la clase para mostrar su título/fecha en la cabecera.
        $clase = null;
        if ($clasesRes['ok']) {
            foreach ($clasesRes['data'] as $c) {
                if ((int) $c['id'] === $id) { $clase = $c; break; }
            }
        }

        $this->render('clases/inscriptos.html.twig', [
            'clase'      => $clase,
            'inscriptos' => $inscRes['ok'] ? $inscRes['data'] : [],
            'error'      => $inscRes['ok'] ? null : ($inscRes['data']['error'] ?? 'No se pudieron cargar los inscriptos.'),
        ]);
    }
}
