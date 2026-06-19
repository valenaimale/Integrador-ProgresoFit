<?php

namespace PAW\app\controllers;

use PAW\Core\Controller;
use PAW\app\services\ApiClient;

class PerfilUserController extends Controller
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
        $userId   = $_SESSION['user']['id'];
        $jwt      = $_SESSION['jwt'];

        // Obtener datos básicos del perfil
        $response = $this->api->get("/usuarios/{$userId}", $jwt);
        $userData = $response['ok'] ? $response['data'] : $_SESSION['user'];

        $suscripcionActiva = null;
        $planes = [];

        if ($userData['rol'] === 'ALUMNO') {
            // Obtener suscripción activa
            $resSub = $this->api->get('/suscripciones/mi-suscripcion', $jwt);
            if ($resSub['ok']) {
                $suscripcionActiva = $resSub['data'];
            }

            // Obtener planes disponibles
            $resPlanes = $this->api->get('/suscripciones/planes', $jwt);
            if ($resPlanes['ok']) {
                $planes = $resPlanes['data'];
            }
        }

        $this->render('perfilUser.html.twig', [
            'userData' => $userData,
            'suscripcionActiva' => $suscripcionActiva,
            'planes' => $planes
        ]);
    }

    public function mostrarPerfil()
    {
        if (empty($_SESSION['user'])) {
            header('Location: /inicio-sesion');
            exit;
        }
        $userId   = $_SESSION['user']['id'];
        $jwt      = $_SESSION['jwt'];
        $response = $this->api->get("/usuarios/{$userId}", $jwt);
        $userData = $response['ok'] ? $response['data'] : $_SESSION['user'];
        $rol      = $userData['rol'] ?? $_SESSION['user']['rol'];

        switch ($rol) {
            case 'ALUMNO':
                $suscripcionActiva = null;
                $planes = [];
                // Tu lógica de suscripciones:
                $resSub = $this->api->get('/suscripciones/mi-suscripcion', $jwt);
                if ($resSub['ok']) {
                    $suscripcionActiva = $resSub['data'];
                }
                $resPlanes = $this->api->get('/suscripciones/planes', $jwt);
                if ($resPlanes['ok']) {
                    $planes = $resPlanes['data'];
                }
                $this->render('perfilUser.html.twig', [
                    'userData' => $userData,
                    'suscripcionActiva' => $suscripcionActiva,
                    'planes' => $planes
                ]);
                break;
            case 'ENTRENADOR':
                $entResponse = $this->api->get("/entrenadores/{$userId}", $jwt);
                if ($entResponse['ok']) {
                    $userData = array_merge($userData, $entResponse['data']);
                }
                $this->render('perfilEntrenador.html.twig', ['userData' => $userData]);
                break;
            case 'GIMNASIO':
                $gimResponse = $this->api->get("/gimnasios/me", $jwt);
                if ($gimResponse['ok']) {
                    $userData = array_merge($userData, $gimResponse['data']);
                }
                $this->render('perfilGimnasio.html.twig', ['userData' => $userData]);
                break;
            default:
                header('Location: /');
                exit;
        }
    }

    public function mostrarEditar()
    {
        if (empty($_SESSION['user'])) {
            header('Location: /inicio-sesion');
            exit;
        }
        $userId   = $_SESSION['user']['id'];
        $jwt      = $_SESSION['jwt'];
        $response = $this->api->get("/usuarios/{$userId}", $jwt);
        $userData = $response['ok'] ? $response['data'] : $_SESSION['user'];
        $rol      = $userData['rol'] ?? $_SESSION['user']['rol'];

        switch ($rol) {
            case 'ALUMNO':
                // Nota: Ellos siguen usando require para edición, podrías migrarlo luego
                require __DIR__ . '/../views/perfilUserEdicion.view.php';
                break;
            case 'ENTRENADOR':
                $entResponse = $this->api->get("/entrenadores/{$userId}", $jwt);
                if ($entResponse['ok']) {
                    $userData = array_merge($userData, $entResponse['data']);
                }
                require __DIR__ . '/../views/perfilEntrenadorEdicion.view.php';
                break;
            case 'GIMNASIO':
                $gimResponse = $this->api->get("/gimnasios/me", $jwt);
                if ($gimResponse['ok']) {
                    $userData = array_merge($userData, $gimResponse['data']);
                }
                require __DIR__ . '/../views/perfilGimnasioEdicion.view.php';
                break;
            default:
                header('Location: /');
                exit;
        }
    }

    public function perfilEditado()
    {
        if (empty($_SESSION['user'])) {
            header('Location: /inicio-sesion');
            exit;
        }
        $userId   = $_SESSION['user']['id'];
        $jwt      = $_SESSION['jwt'];
        $response = $this->api->get("/usuarios/{$userId}", $jwt);
        $userData = $response['ok'] ? $response['data'] : $_SESSION['user'];
        $rol      = $userData['rol'] ?? $_SESSION['user']['rol'];

        switch ($rol) {
            case 'ALUMNO':
                if (!empty($_POST['contra_nueva'])) {
                    if ($_POST['contra_nueva'] !== $_POST['contra_nueva_repetida']) {
                        $error = 'Las contraseñas nuevas no coinciden';
                        require __DIR__ . '/../views/perfilUserEdicion.view.php';
                        return;
                    }
                    $almResponse = $this->api->put("/usuarios/{$userId}", [
                        'password'      => $_POST['contra_nueva'],
                        'contra_actual' => $_POST['contra_actual'] ?? '',
                    ], $jwt);
                    if ($almResponse['ok']) {
                        header('Location: /perfil');
                        exit;
                    }
                    $error = $almResponse['data']['error'] ?? 'Error al actualizar el perfil';
                    require __DIR__ . '/../views/perfilUserEdicion.view.php';
                    return;
                }
                header('Location: /perfil');
                exit;

            case 'ENTRENADOR':
                if (!empty($_POST['contra_nueva'])) {
                    if ($_POST['contra_nueva'] !== $_POST['contra_nueva_repetida']) {
                        $error = 'Las contraseñas nuevas no coinciden';
                        $entResponse = $this->api->get("/entrenadores/{$userId}", $jwt);
                        if ($entResponse['ok']) {
                            $userData = array_merge($userData, $entResponse['data']);
                        }
                        require __DIR__ . '/../views/perfilEntrenadorEdicion.view.php';
                        return;
                    }
                    $passResponse = $this->api->put("/usuarios/{$userId}", [
                        'nombre'        => $_POST['nombre']       ?? '',
                        'password'      => $_POST['contra_nueva'],
                        'contra_actual' => $_POST['contra_actual'] ?? '',
                    ], $jwt);
                    if (!$passResponse['ok']) {
                        $error = $passResponse['data']['error'] ?? 'La contraseña actual es incorrecta';
                        $entResponse = $this->api->get("/entrenadores/{$userId}", $jwt);
                        if ($entResponse['ok']) {
                            $userData = array_merge($userData, $entResponse['data']);
                        }
                        require __DIR__ . '/../views/perfilEntrenadorEdicion.view.php';
                        return;
                    }
                } else {
                    $this->api->put("/usuarios/{$userId}", ['nombre' => $_POST['nombre'] ?? ''], $jwt);
                }
                $entResponse = $this->api->put("/entrenadores/{$userId}", [
                    'especialidad' => $_POST['especialidad'] ?? '',
                    'descripcion'  => $_POST['descripcion']  ?? '',
                    'horario'      => $_POST['horario']       ?? '',
                ], $jwt);
                if ($entResponse['ok']) {
                    header('Location: /perfil');
                    exit;
                }
                $error = $entResponse['data']['error'] ?? 'Error al actualizar el perfil';
                $entResponse = $this->api->get("/entrenadores/{$userId}", $jwt);
                if ($entResponse['ok']) {
                    $userData = array_merge($userData, $entResponse['data']);
                }
                require __DIR__ . '/../views/perfilEntrenadorEdicion.view.php';
                break;

            case 'GIMNASIO':
                if (!empty($_POST['contra_nueva'])) {
                    if ($_POST['contra_nueva'] !== $_POST['contra_nueva_repetida']) {
                        $error = 'Las contraseñas nuevas no coinciden';
                        $gimResponse = $this->api->get("/gimnasios/me", $jwt);
                        if ($gimResponse['ok']) {
                            $userData = array_merge($userData, $gimResponse['data']);
                        }
                        require __DIR__ . '/../views/perfilGimnasioEdicion.view.php';
                        return;
                    }
                    $passResponse = $this->api->put("/usuarios/{$userId}", [
                        'password'      => $_POST['contra_nueva'],
                        'contra_actual' => $_POST['contra_actual'] ?? '',
                    ], $jwt);
                    if (!$passResponse['ok']) {
                        $error = $passResponse['data']['error'] ?? 'La contraseña actual es incorrecta';
                        $gimResponse = $this->api->get("/gimnasios/me", $jwt);
                        if ($gimResponse['ok']) {
                            $userData = array_merge($userData, $gimResponse['data']);
                        }
                        require __DIR__ . '/../views/perfilGimnasioEdicion.view.php';
                        return;
                    }
                }
                $gimResponse = $this->api->put("/gimnasios/me", [
                    'nombre'      => $_POST['nombre']      ?? '',
                    'direccion'   => $_POST['direccion']   ?? '',
                    'horarios'    => $_POST['horarios']    ?? '',
                    'telefono'    => $_POST['telefono']    ?? '',
                    'descripcion' => $_POST['descripcion'] ?? '',
                    'servicios'   => $_POST['servicios']   ?? '',
                ], $jwt);
                if ($gimResponse['ok']) {
                    header('Location: /perfil');
                    exit;
                }
                $error = $gimResponse['data']['error'] ?? 'Error al actualizar el perfil';
                $gimResponse = $this->api->get("/gimnasios/me", $jwt);
                if ($gimResponse['ok']) {
                    $userData = array_merge($userData, $gimResponse['data']);
                }
                require __DIR__ . '/../views/perfilGimnasioEdicion.view.php';
                break;

            default:
                header('Location: /');
                exit;
        }
    }

    public function actualizar()
    {
        if (empty($_SESSION['user'])) {
            header('Location: /inicio-sesion');
            exit;
        }
        $userId = $_SESSION['user']['id'];
        $jwt    = $_SESSION['jwt'];
        $data   = array_filter([
            'nombre'   => $_POST['nombre']   ?? '',
            'email'    => $_POST['email']    ?? '',
            'password' => $_POST['password'] ?? '',
        ]);
        $response = $this->api->put("/usuarios/{$userId}", $data, $jwt);
        if ($response['ok']) {
            $_SESSION['user'] = array_merge($_SESSION['user'], $data);
            header('Location: /perfil-user');
            exit;
        }
        $error    = $response['data']['error'] ?? 'Error al actualizar el perfil';
        $userData = $_SESSION['user'];
        $this->render('perfilUser.html.twig', ['userData' => $userData, 'error' => $error]);
    }
}
