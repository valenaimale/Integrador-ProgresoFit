<?php

namespace PAW\app\controllers;

use PAW\app\services\ApiClient;

class InicioSesionController
{
    public string $viewsDir;
    private ApiClient $api;

    public function __construct()
    {
        $this->viewsDir = __DIR__ . '/../views/';
        $this->api = new ApiClient();
    }

    public function index()
    {
        require $this->viewsDir . 'inicioSesion.view.php';
    }

    public function process()
    {
        $email    = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $response = $this->api->post('/auth/login', [
            'email'    => $email,
            'password' => $password,
        ]);

        if ($response['ok']) {
            $_SESSION['jwt']  = $response['data']['token'];
            $_SESSION['user'] = $response['data']['user'];
            header('Location: /');
            exit;
        }

        $error = $response['data']['message'] ?? 'Email o contraseña incorrectos';
        require $this->viewsDir . 'inicioSesion.view.php';
    }

    public function logout()
    {
        if (!empty($_SESSION['jwt'])) {
            $this->api->post('/auth/logout', [], $_SESSION['jwt']);
        }
        session_destroy();
        header('Location: /');
        exit;
    }
}
