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
                $this->render('perfilGimnasioPropio.html.twig', ['userData' => $userData]);
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
                $this->render('perfilUserEdicion.html.twig', ['userData' => $userData]);
                break;
            case 'ENTRENADOR':
                $entResponse = $this->api->get("/entrenadores/{$userId}", $jwt);
                if ($entResponse['ok']) {
                    $userData = array_merge($userData, $entResponse['data']);
                }
                $this->render('perfilEntrenadorEdicion.html.twig', ['userData' => $userData]);
                break;
            case 'GIMNASIO':
                $gimResponse = $this->api->get("/gimnasios/me", $jwt);
                if ($gimResponse['ok']) {
                    $userData = array_merge($userData, $gimResponse['data']);
                }
                $this->render('perfilGimnasioEdicion.html.twig', ['userData' => $userData]);
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
                        $this->render('perfilUserEdicion.html.twig', ['userData' => $userData, 'error' => $error ?? null]);
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
                    $this->render('perfilUserEdicion.html.twig', ['userData' => $userData, 'error' => $error ?? null]);
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
                        $this->render('perfilEntrenadorEdicion.html.twig', ['userData' => $userData, 'error' => $error ?? null]);
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
                        $this->render('perfilEntrenadorEdicion.html.twig', ['userData' => $userData, 'error' => $error ?? null]);
                        return;
                    }
                } else {
                    $this->api->put("/usuarios/{$userId}", ['nombre' => $_POST['nombre'] ?? ''], $jwt);
                }
                $foto_url = null;
                if (isset($_FILES['fotodeperfil']) && $_FILES['fotodeperfil']['error'] === UPLOAD_ERR_OK) {
                    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
                    $mime    = mime_content_type($_FILES['fotodeperfil']['tmp_name']);
                    if (in_array($mime, $allowed)) {
                        $ext       = pathinfo($_FILES['fotodeperfil']['name'], PATHINFO_EXTENSION);
                        $filename  = uniqid('ent_', true) . '.' . strtolower($ext);
                        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';
                        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                        if (move_uploaded_file($_FILES['fotodeperfil']['tmp_name'], $uploadDir . $filename)) {
                            $foto_url = '/uploads/' . $filename;
                        }
                    }
                }
                if ($foto_url) {
                    $this->api->put("/usuarios/{$userId}", ['foto_url' => $foto_url], $jwt);
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
                $this->render('perfilEntrenadorEdicion.html.twig', ['userData' => $userData, 'error' => $error ?? null]);
                break;

            case 'GIMNASIO':
                if (!empty($_POST['contra_nueva'])) {
                    if ($_POST['contra_nueva'] !== $_POST['contra_nueva_repetida']) {
                        $error = 'Las contraseñas nuevas no coinciden';
                        $gimResponse = $this->api->get("/gimnasios/me", $jwt);
                        if ($gimResponse['ok']) {
                            $userData = array_merge($userData, $gimResponse['data']);
                        }
                        $this->render('perfilGimnasioEdicion.html.twig', ['userData' => $userData, 'error' => $error ?? null]);
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
                        $this->render('perfilGimnasioEdicion.html.twig', ['userData' => $userData, 'error' => $error ?? null]);
                        return;
                    }
                }
                $foto_url = null;
                if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
                    $mime    = mime_content_type($_FILES['logo']['tmp_name']);
                    if (in_array($mime, $allowed)) {
                        $ext       = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                        $filename  = uniqid('gym_', true) . '.' . strtolower($ext);
                        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';
                        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                        if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $filename)) {
                            $foto_url = '/uploads/' . $filename;
                        }
                    }
                }
                $gimResponse = $this->api->put("/gimnasios/me", array_filter([
                    'nombre'      => $_POST['nombre']      ?? '',
                    'direccion'   => $_POST['direccion']   ?? '',
                    'horarios'    => $_POST['horarios']    ?? '',
                    'telefono'    => $_POST['telefono']    ?? '',
                    'descripcion' => $_POST['descripcion'] ?? '',
                    'servicios'   => $_POST['servicios']   ?? '',
                    'foto_url'    => $foto_url,
                ], fn($v) => $v !== null), $jwt);
                if ($gimResponse['ok']) {
                    header('Location: /perfil');
                    exit;
                }
                $error = $gimResponse['data']['error'] ?? 'Error al actualizar el perfil';
                $gimResponse = $this->api->get("/gimnasios/me", $jwt);
                if ($gimResponse['ok']) {
                    $userData = array_merge($userData, $gimResponse['data']);
                }
                $this->render('perfilGimnasioEdicion.html.twig', ['userData' => $userData, 'error' => $error ?? null]);
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
