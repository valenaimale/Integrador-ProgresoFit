<?php

namespace PAW\app\controllers;

use PAW\app\services\ApiClient;

class PerfilUserController
{
    public string $viewsDir;
    private ApiClient $api;

    public function __construct()
    {
        $this->viewsDir = __DIR__ . '/../views/';
        $this->api = new ApiClient();
    }

    /*public function mostrar()
    {
        
        if (empty($_SESSION['user'])) {
            header('Location: /inicio-sesion');
            exit;
        }
        $userId   = $_SESSION['user']['id'];
        $response = $this->api->get("/usuarios/{$userId}", $_SESSION['jwt']);

        $userData = $response['ok'] ? $response['data'] : $_SESSION['user'];

        require $this->viewsDir . 'perfilUser.view.php';
    }*/
    //funcion mostrar generica de valen (se borra tanto controlador de perfil de gimnasio como controlador de perfil de gimnasio como perfio de entrenador)    
    public function mostrarPerfil(){
        if(empty($_SESSION['user'])){
            header('Location: /inicio-sesion');
            exit;
        }
        $userId   = $_SESSION['user']['id'];
        $response = $this->api->get("/usuarios/{$userId}", $_SESSION['jwt']);
        $userData = $response['ok'] ? $response['data'] : $_SESSION['user'];
        $rol      = $_SESSION['user']['rol'];

        switch ($rol) {
            case 'ALUMNO':
                require $this->viewsDir . 'perfilUser.view.php';
                break;
            case 'ENTRENADOR':
                // enriquecer $userData con especialidad, descripcion, horario del perfil de entrenador
                $entResponse = $this->api->get("/entrenadores/{$userId}", $_SESSION['jwt']);
                if ($entResponse['ok']) {
                    $userData = array_merge($userData, $entResponse['data']);
                }
                require $this->viewsDir . 'perfilEntrenador.view.php';
                break;
            case 'GIMNASIO':
                $gimResponse = $this->api->get("/gimnasios/me", $_SESSION['jwt']);
                if ($gimResponse['ok']) {
                    $userData = array_merge($userData, $gimResponse['data']);
                }
                require $this->viewsDir . 'perfilGimnasio.view.php';
                break;
            default:
                header('Location: /');
                exit;
        }
    }
    public function mostrarEditar(){
        if(empty($_SESSION['user'])){
            header('Location: /inicio-sesion');
            exit;
        }
        $userId   = $_SESSION['user']['id'];
        $response = $this->api->get("/usuarios/{$userId}", $_SESSION['jwt']);
        $userData = $response['ok'] ? $response['data'] : $_SESSION['user'];
        $rol      = $_SESSION['user']['rol'];
        switch ($rol) {
            case 'ALUMNO':
                require $this->viewsDir . 'perfilUserEdicion.view.php';
                break;
            case 'ENTRENADOR':
                // enriquecer $userData con especialidad, descripcion, horario del perfil de entrenador
                $entResponse = $this->api->get("/entrenadores/{$userId}", $_SESSION['jwt']);
                if ($entResponse['ok']) {
                    $userData = array_merge($userData, $entResponse['data']);
                }
                require $this->viewsDir . 'perfilEntrenadorEdicion.view.php';
                break;
            case 'GIMNASIO':
                $gimResponse = $this->api->get("/gimnasios/me", $_SESSION['jwt']);
                if ($gimResponse['ok']) {
                    $userData = array_merge($userData, $gimResponse['data']);
                }
                require $this->viewsDir . 'perfilGimnasioEdicion.view.php';
                break;
            default:
                header('Location: /');
                exit;
        }
    }
    public function perfilEditado(){
        if(empty($_SESSION['user'])){
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
                        require $this->viewsDir . 'perfilUserEdicion.view.php';
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
                    require $this->viewsDir . 'perfilUserEdicion.view.php';
                    return;
                }

                // si no hay contraseña nueva, redirigir sin hacer nada
                header('Location: /perfil');
                exit;
            case 'ENTRENADOR':
            // validar que las contraseñas coincidan antes de hacer cualquier llamada
            if (!empty($_POST['contra_nueva'])) {
                if ($_POST['contra_nueva'] !== $_POST['contra_nueva_repetida']) {
                    $error = 'Las contraseñas nuevas no coinciden';
                    $entResponse = $this->api->get("/entrenadores/{$userId}", $_SESSION['jwt']);
                    if ($entResponse['ok']) {
                        $userData = array_merge($userData, $entResponse['data']);
                    }
                    require $this->viewsDir . 'perfilEntrenadorEdicion.view.php';
                    return;
                }
                // si coinciden, actualizar contraseña y nombre en /usuarios
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
                    require $this->viewsDir . 'perfilEntrenadorEdicion.view.php';
                    return;
                }
            } else {
                // si no hay contraseña nueva, solo actualizar nombre
                $this->api->put("/usuarios/{$userId}", [
                    'nombre' => $_POST['nombre'] ?? '',
                ], $_SESSION['jwt']);
            }

            // actualizar datos específicos del entrenador
            $entResponse = $this->api->put("/entrenadores/{$userId}", [
                'especialidad' => $_POST['especialidad'] ?? '',
                'descripcion'  => $_POST['descripcion']  ?? '',
                'horario'      => $_POST['horario']       ?? '',
            ], $_SESSION['jwt']);

            if ($entResponse['ok']) {
                header('Location: /perfil');
                exit;//detiene toda la ejecucion de PHP
            }

            // si el put falló, volver al formulario con el error
            $error = $entResponse['data']['error'] ?? 'Error al actualizar el perfil';
            $entResponse = $this->api->get("/entrenadores/{$userId}", $_SESSION['jwt']);
            if ($entResponse['ok']) {
                $userData = array_merge($userData, $entResponse['data']);
            }
            require $this->viewsDir . 'perfilEntrenadorEdicion.view.php';
            break;

            case 'GIMNASIO':
                // validar que las contraseñas coincidan antes de hacer cualquier llamada
                if (!empty($_POST['contra_nueva'])) {
                    if ($_POST['contra_nueva'] !== $_POST['contra_nueva_repetida']) {
                        $error = 'Las contraseñas nuevas no coinciden';
                        $gimResponse = $this->api->get("/gimnasios/me", $_SESSION['jwt']);
                        if ($gimResponse['ok']) {
                            $userData = array_merge($userData, $gimResponse['data']);
                        }
                        require $this->viewsDir . 'perfilGimnasioEdicion.view.php';
                        return;
                    }
                    // si coinciden, actualizar contraseña en /usuarios
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
                        require $this->viewsDir . 'perfilGimnasioEdicion.view.php';
                        return;
                    }
                }

                // actualizar datos del gimnasio
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

                // si el put falló, volver al formulario con el error
                $error = $gimResponse['data']['error'] ?? 'Error al actualizar el perfil';
                $gimResponse = $this->api->get("/gimnasios/me", $_SESSION['jwt']);
                if ($gimResponse['ok']) {
                    $userData = array_merge($userData, $gimResponse['data']);
                }
                require $this->viewsDir . 'perfilGimnasioEdicion.view.php';
                break;
            default:
                header('Location: /');
                exit;
        }
    }
}
