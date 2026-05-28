<?php

namespace PAW\app\controllers;

use PAW\app\services\ApiClient;

class CrearCuentaController
{
    public string $viewsDir;
    private ApiClient $api;

    public function __construct()
    {
        $this->viewsDir = __DIR__ . '/../views/';
        $this->api = new ApiClient();
    }

    public function crearCuenta()
    {
        require $this->viewsDir . 'crearCuenta.view.php';
    }

    public function cuentaCreada()
    {
        require $this->viewsDir . 'crearCuentaCreada.view.php';
    }

    public function crearCuentaProcess()
    {
        $nombre     = $_POST['nombre_apellido'] ?? '';
        $email      = $_POST['email'] ?? '';
        $contraseña = $_POST['contraseña'] ?? '';
        $ccontraseña = $_POST['ccontraseña'] ?? '';

        if ($contraseña !== $ccontraseña) {
            $error = 'Las contraseñas no coinciden';
            require $this->viewsDir . 'crearCuenta.view.php';
            exit;
        }

        $response = $this->api->post('/usuarios/alumno', [
            'nombre'   => $nombre,
            'email'    => $email,
            'password' => $contraseña,
        ]);

        if ($response['ok']) {
            header('Location: /crearCuentaCreada');
            exit;
        }

        $error = $response['data']['message'] ?? 'Error al crear la cuenta. El email puede ya estar registrado.';
        require $this->viewsDir . 'crearCuenta.view.php';
    }
}
