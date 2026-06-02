<?php

namespace PAW\app\controllers;

use PAW\app\services\ApiClient;

class RutinasController
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
        if (empty($_SESSION['user'])) {
            header('Location: /inicio-sesion');
            exit;
        }

        $jwt = $_SESSION['jwt'];
        $user = $_SESSION['user'];

        // Obtener rutinas (el backend ya filtra por rol)
        $resRutinas = $this->api->get('/rutinas', $jwt);
        $rutinas = $resRutinas['ok'] ? $resRutinas['data'] : [];

        $alumnos = [];
        if (in_array($user['rol'], ['ENTRENADOR', 'ADMIN'])) {
            // Obtener todos los alumnos para el selector de asignación
            $resAlumnos = $this->api->get('/entrenadores/alumnos', $jwt);
            $alumnos = $resAlumnos['ok'] ? $resAlumnos['data'] : [];
        }

        require $this->viewsDir . 'rutinas.view.php';
    }

    public function asignar()
    {
        if (empty($_SESSION['user'])) {
            header('Location: /inicio-sesion');
            exit;
        }

        $rutinaId = $_POST['rutina_id'] ?? null;
        $alumnoId = $_POST['alumno_id'] ?? null;

        if (!$rutinaId || !$alumnoId) {
            header('Location: /rutinas?error=Datos incompletos');
            exit;
        }

        $response = $this->api->post("/rutinas/{$rutinaId}/asignar", [
            'alumno_id' => $alumnoId
        ], $_SESSION['jwt']);

        if ($response['ok']) {
            header('Location: /rutinas?mensaje=Rutina asignada correctamente');
        } else {
            $error = $response['data']['error'] ?? 'Error al asignar rutina';
            header("Location: /rutinas?error=" . urlencode($error));
        }
        exit;
    }
}
