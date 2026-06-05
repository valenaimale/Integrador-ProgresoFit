<?php

namespace PAW\app\controllers;

use PAW\Core\Controller;
use PAW\app\services\ApiClient;

class CrearcuentaGymController extends Controller
{
    private ApiClient $api;

    public function __construct()
    {
        parent::__construct();
        $this->api = new ApiClient();
    }

    public function crear(): void
    {
        $this->render('crearcuenta-gym.html.twig');
    }

    public function crearGym(): void
    {
        $email      = $_POST['email']      ?? '';
        $contraseña = $_POST['contraseña'] ?? '';
        $cconf      = $_POST['ccontraseña'] ?? '';

        if ($contraseña !== $cconf) {
            $this->render('crearcuenta-gym.html.twig', [
                'error' => 'Las contraseñas no coinciden',
                'form'  => $_POST,
            ]);
            return;
        }

        $response = $this->api->post('/auth/register', [
            'email'       => $email,
            'password'    => $contraseña,
            'rol'         => 'GIMNASIO',
            'nombre'      => $_POST['nombre']      ?? '',
            'direccion'   => $_POST['direccion']   ?? '',
            'telefono'    => $_POST['telefono']    ?? '',
            'descripcion' => $_POST['descripcion'] ?? '',
            'horarios'    => $_POST['horarios']    ?? '',
            'servicios'   => $_POST['servicios']   ?? '',
        ]);

        if ($response['ok']) {
            header('Location: /inicio-sesion');
            exit;
        }

        $this->render('crearcuenta-gym.html.twig', [
            'error' => $response['data']['error'] ?? 'Error al crear la cuenta. El email puede estar registrado.',
            'form'  => $_POST,
        ]);
    }
}
