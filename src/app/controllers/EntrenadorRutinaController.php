<?php

namespace PAW\app\controllers;

use PAW\app\services\ApiClient;
use PAW\app\services\TwigRenderer;

class EntrenadorRutinaController
{
    private ApiClient $api;
    private TwigRenderer $twig;

    public function __construct()
    {
        $this->api  = new ApiClient();
        $this->twig = new TwigRenderer();
        $this->requireEntrenador();
    }

    // ── Guards ────────────────────────────────────────────────────────

    private function requireEntrenador(): void
    {
        if (empty($_SESSION['user']) || $_SESSION['user']['rol'] !== 'ENTRENADOR') {
            header('Location: /inicio-sesion');
            exit;
        }
    }

    private function token(): string
    {
        return $_SESSION['jwt'] ?? '';
    }

    // ── Listar rutinas ────────────────────────────────────────────────

    public function listar(): void
    {
        $response   = $this->api->get('/rutinas', $this->token());
        $alumnosRes = $this->api->get('/usuarios/mis-alumnos', $this->token());

        $this->twig->render('entrenador/rutinas.twig', [
            'rutinas' => $response['ok'] ? $response['data'] : [],
            'alumnos' => $alumnosRes['ok'] ? $alumnosRes['data'] : [],
            'error'   => $response['ok'] ? null : 'No se pudo cargar las rutinas.',
        ]);
    }

    // ── Crear rutina ──────────────────────────────────────────────────

    public function nueva(): void
    {
        $this->twig->render('entrenador/rutina-form.twig');
    }

    public function crear(): void
    {
        $response = $this->api->post('/rutinas', [
            'titulo'      => $_POST['titulo']      ?? '',
            'descripcion' => $_POST['descripcion'] ?? '',
            'objetivo'    => $_POST['objetivo']    ?? '',
        ], $this->token());

        if ($response['ok']) {
            $id = $response['data']['id'] ?? null;
            header($id ? "Location: /entrenador/rutinas/gestionar?id={$id}" : 'Location: /entrenador/rutinas');
            exit;
        }

        $this->twig->render('entrenador/rutina-form.twig', [
            'error'  => $response['data']['error'] ?? 'Error al crear la rutina.',
            'rutina' => $_POST,
        ]);
    }

    // ── Editar rutina ─────────────────────────────────────────────────

    public function editar(): void
    {
        $id       = (int) ($_GET['id'] ?? 0);
        $response = $this->api->get("/rutinas/{$id}", $this->token());

        if (!$response['ok']) {
            header('Location: /entrenador/rutinas');
            exit;
        }

        $this->twig->render('entrenador/rutina-form.twig', [
            'rutina' => $response['data'],
        ]);
    }

    public function actualizar(): void
    {
        $id = (int) ($_POST['id'] ?? 0);

        $response = $this->api->put("/rutinas/{$id}", [
            'titulo'      => $_POST['titulo']      ?? '',
            'descripcion' => $_POST['descripcion'] ?? '',
            'objetivo'    => $_POST['objetivo']    ?? '',
        ], $this->token());

        if ($response['ok']) {
            header('Location: /entrenador/rutinas');
            exit;
        }

        $this->twig->render('entrenador/rutina-form.twig', [
            'error'  => $response['data']['error'] ?? 'Error al actualizar la rutina.',
            'rutina' => array_merge($_POST, ['id' => $id]),
        ]);
    }

    // ── Eliminar rutina ───────────────────────────────────────────────

    public function eliminar(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $this->api->delete("/rutinas/{$id}", $this->token());
        header('Location: /entrenador/rutinas');
        exit;
    }

    // ── Gestionar días ────────────────────────────────────────────────

    public function gestionar(): void
    {
        $id        = (int) ($_GET['id'] ?? 0);
        $rutinaRes = $this->api->get("/rutinas/{$id}", $this->token());
        $alumnosRes = $this->api->get('/usuarios/mis-alumnos', $this->token());

        if (!$rutinaRes['ok']) {
            header('Location: /entrenador/rutinas');
            exit;
        }

        $this->twig->render('entrenador/rutina-dias.twig', [
            'rutina'  => $rutinaRes['data'],
            'alumnos' => $alumnosRes['ok'] ? $alumnosRes['data'] : [],
        ]);
    }

    // ── Asignar / desasignar rutina ───────────────────────────────────

    public function asignar(): void
    {
        $rutinaId = (int) ($_POST['rutina_id'] ?? 0);
        $alumnoId = (int) ($_POST['alumno_id'] ?? 0);

        $this->api->post("/rutinas/{$rutinaId}/asignar", [
            'alumno_id' => $alumnoId,
        ], $this->token());

        header("Location: /entrenador/rutinas/gestionar?id={$rutinaId}");
        exit;
    }

    public function desasignar(): void
    {
        $rutinaId = (int) ($_POST['rutina_id'] ?? 0);
        $alumnoId = (int) ($_POST['alumno_id'] ?? 0);

        $this->api->delete("/rutinas/{$rutinaId}/asignar/{$alumnoId}", $this->token());

        header("Location: /entrenador/rutinas/gestionar?id={$rutinaId}");
        exit;
    }

    // ── Búsqueda de ejercicios (proxy JSON) ───────────────────────────

    public function buscarEjercicios(): void
    {
        $q        = $_GET['q'] ?? '';
        $response = $this->api->get('/ejercicios?q=' . urlencode($q) . '&limit=10', $this->token());

        header('Content-Type: application/json');
        if ($response['ok']) {
            echo json_encode($response['data']['ejercicios'] ?? []);
        } else {
            echo json_encode([]);
        }
        exit;
    }

    // ── Días ──────────────────────────────────────────────────────────

    public function agregarDia(): void
    {
        $rutinaId = (int) ($_POST['rutina_id'] ?? 0);

        $this->api->post("/rutinas/{$rutinaId}/dias", [
            'nombre_dia' => $_POST['nombre_dia'] ?? '',
            'orden'      => (int) ($_POST['orden'] ?? 1),
        ], $this->token());

        header("Location: /entrenador/rutinas/gestionar?id={$rutinaId}");
        exit;
    }

    public function eliminarDia(): void
    {
        $rutinaId = (int) ($_POST['rutina_id'] ?? 0);
        $diaId    = (int) ($_POST['dia_id']    ?? 0);

        $this->api->delete("/rutinas/{$rutinaId}/dias/{$diaId}", $this->token());

        header("Location: /entrenador/rutinas/gestionar?id={$rutinaId}");
        exit;
    }

    // ── Ejercicios dentro de un día ───────────────────────────────────

    public function agregarEjercicio(): void
    {
        $rutinaId = (int) ($_POST['rutina_id']     ?? 0);
        $diaId    = (int) ($_POST['dia_id']         ?? 0);
        $series   = (int) ($_POST['series']         ?? 3);
        $reps     = (int) ($_POST['repeticiones']   ?? 10);

        $this->api->post("/rutinas/{$rutinaId}/dias/{$diaId}/ejercicios", [
            'api_ejercicio_id'  => (int) ($_POST['api_ejercicio_id'] ?? 0),
            'series_repeticiones' => "{$series}x{$reps}",
            'orden'             => (int) ($_POST['orden'] ?? 1),
        ], $this->token());

        header("Location: /entrenador/rutinas/gestionar?id={$rutinaId}");
        exit;
    }

    public function eliminarEjercicio(): void
    {
        $rutinaId = (int) ($_POST['rutina_id'] ?? 0);
        $diaId    = (int) ($_POST['dia_id']    ?? 0);
        $pivotId  = (int) ($_POST['pivot_id']  ?? 0);

        $this->api->delete("/rutinas/{$rutinaId}/dias/{$diaId}/ejercicios/{$pivotId}", $this->token());

        header("Location: /entrenador/rutinas/gestionar?id={$rutinaId}");
        exit;
    }
}
