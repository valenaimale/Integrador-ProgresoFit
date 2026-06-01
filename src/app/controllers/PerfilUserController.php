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
    public function mostrar(){
        if(empty($_SESSION['user'])){
            header('Location: /inicio-sesion');
            exit;
        }
        $rol = getRol();

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
    public function editar(){
        if(empty($_SESSION['user'])){
            header('Location: /inicio-sesion');
            exit;
        }
        $rol = getRol();
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
    private function getRol(){
        $userId   = $_SESSION['user']['id'];
        $response = $this->api->get("/usuarios/{$userId}", $_SESSION['jwt']);
        $userData = $response['ok'] ? $response['data'] : $_SESSION['user'];
        $rol      = $_SESSION['user']['rol'];
        return $rol;
    }
}
