<?php

namespace PAW\app\controllers;

use PAW\app\services\ApiClient;
use PAW\app\services\TwigRenderer;

class CrearcuentaGymController
{
    private ApiClient $api;
    private TwigRenderer $twig;

    public function __construct()
    {
        $this->api  = new ApiClient();
        $this->twig = new TwigRenderer();
    }

    public function crear(): void
    {
        $this->twig->render('crearcuenta-gym.twig');
    }

    public function crearGym(): void
    {
        $email      = $_POST['email']      ?? '';
        $contraseña = $_POST['contraseña'] ?? '';
        $cconf      = $_POST['ccontraseña'] ?? '';

        if ($contraseña !== $cconf) {
            $this->twig->render('crearcuenta-gym.twig', [
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

        $this->twig->render('crearcuenta-gym.twig', [
            'error' => $response['data']['error'] ?? 'Error al crear la cuenta. El email puede estar registrado.',
            'form'  => $_POST,
        ]);
    }
}
