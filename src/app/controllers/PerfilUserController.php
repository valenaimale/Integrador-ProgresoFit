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
        $response = $this->api->get("/usuarios/{$userId}", $_SESSION['jwt']);
        $userData = $response['ok'] ? $response['data'] : $_SESSION['user'];
        $this->render('perfilUser.html.twig', ['userData' => $userData]);
    }

    public function mostrarPerfil()
    {
        if (empty($_SESSION['user'])) {
            header('Location: /inicio-sesion');
            exit;
        }
        $userId   = $_SESSION['user']['id'];
        $response = $this->api->get("/usuarios/{$userId}", $_SESSION['jwt']);
        $userData = $response['ok'] ? $response['data'] : $_SESSION['user'];
        $rol      = $_SESSION['user']['rol'];

        switch ($rol) {
            case 'ALUMNO':
                $this->render('perfilUser.html.twig', ['userData' => $userData]);
                break;
            case 'ENTRENADOR':
                $entResponse = $this->api->get("/entrenadores/{$userId}", $_SESSION['jwt']);
                if ($entResponse['ok']) {
                    $userData = array_merge($userData, $entResponse['data']);
                }
                $this->render('perfilEntrenador.html.twig', ['userData' => $userData]);
                break;
            case 'GIMNASIO':
                $gimResponse = $this->api->get("/gimnasios/me", $_SESSION['jwt']);
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
        $response = $this->api->get("/usuarios/{$userId}", $_SESSION['jwt']);
        $userData = $response['ok'] ? $response['data'] : $_SESSION['user'];
        $rol      = $_SESSION['user']['rol'];

        switch ($rol) {
            case 'ALUMNO':
                require __DIR__ . '/../views/perfilUserEdicion.view.php';
                break;
            case 'ENTRENADOR':
                $entResponse = $this->api->get("/entrenadores/{$userId}", $_SESSION['jwt']);
                if ($entResponse['ok']) {
                    $userData = array_merge($userData, $entResponse['data']);
                }
                require __DIR__ . '/../views/perfilEntrenadorEdicion.view.php';
                break;
            case 'GIMNASIO':
                $gimResponse = $this->api->get("/gimnasios/me", $_SESSION['jwt']);
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
        $response = $this->api->get("/usuarios/{$userId}", $_SESSION['jwt']);
        $userData = $response['ok'] ? $response['data'] : $_SESSION['user'];
        $rol      = $_SESSION['user']['rol'];

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
                    ], $_SESSION['jwt']);
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
                        $entResponse = $this->api->get("/entrenadores/{$userId}", $_SESSION['jwt']);
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
                    ], $_SESSION['jwt']);
                    if (!$passResponse['ok']) {
                        $error = $passResponse['data']['error'] ?? 'La contraseña actual es incorrecta';
                        $entResponse = $this->api->get("/entrenadores/{$userId}", $_SESSION['jwt']);
                        if ($entResponse['ok']) {
                            $userData = array_merge($userData, $entResponse['data']);
                        }
                        require __DIR__ . '/../views/perfilEntrenadorEdicion.view.php';
                        return;
                    }
                } else {
                    $this->api->put("/usuarios/{$userId}", ['nombre' => $_POST['nombre'] ?? ''], $_SESSION['jwt']);
                }
                $entResponse = $this->api->put("/entrenadores/{$userId}", [
                    'especialidad' => $_POST['especialidad'] ?? '',
                    'descripcion'  => $_POST['descripcion']  ?? '',
                    'horario'      => $_POST['horario']       ?? '',
                ], $_SESSION['jwt']);
                if ($entResponse['ok']) {
                    header('Location: /perfil');
                    exit;
                }
                $error = $entResponse['data']['error'] ?? 'Error al actualizar el perfil';
                $entResponse = $this->api->get("/entrenadores/{$userId}", $_SESSION['jwt']);
                if ($entResponse['ok']) {
                    $userData = array_merge($userData, $entResponse['data']);
                }
                require __DIR__ . '/../views/perfilEntrenadorEdicion.view.php';
                break;

            case 'GIMNASIO':
                if (!empty($_POST['contra_nueva'])) {
                    if ($_POST['contra_nueva'] !== $_POST['contra_nueva_repetida']) {
                        $error = 'Las contraseñas nuevas no coinciden';
                        $gimResponse = $this->api->get("/gimnasios/me", $_SESSION['jwt']);
                        if ($gimResponse['ok']) {
                            $userData = array_merge($userData, $gimResponse['data']);
                        }
                        require __DIR__ . '/../views/perfilGimnasioEdicion.view.php';
                        return;
                    }
                    $passResponse = $this->api->put("/usuarios/{$userId}", [
                        'password'      => $_POST['contra_nueva'],
                        'contra_actual' => $_POST['contra_actual'] ?? '',
                    ], $_SESSION['jwt']);
                    if (!$passResponse['ok']) {
                        $error = $passResponse['data']['error'] ?? 'La contraseña actual es incorrecta';
                        $gimResponse = $this->api->get("/gimnasios/me", $_SESSION['jwt']);
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
                ], $_SESSION['jwt']);
                if ($gimResponse['ok']) {
                    header('Location: /perfil');
                    exit;
                }
                $error = $gimResponse['data']['error'] ?? 'Error al actualizar el perfil';
                $gimResponse = $this->api->get("/gimnasios/me", $_SESSION['jwt']);
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
        $data   = array_filter([
            'nombre'   => $_POST['nombre']   ?? '',
            'email'    => $_POST['email']    ?? '',
            'password' => $_POST['password'] ?? '',
        ]);
        $response = $this->api->put("/usuarios/{$userId}", $data, $_SESSION['jwt']);
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
