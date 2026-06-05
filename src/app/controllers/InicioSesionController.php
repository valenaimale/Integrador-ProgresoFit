<?php

namespace PAW\app\controllers;

use PAW\Core\Controller;
use PAW\app\services\ApiClient;

class InicioSesionController extends Controller
{
    private ApiClient $api;

    public function __construct()
    {
        parent::__construct();
        $this->api = new ApiClient();
    }

    public function index()
    {
        $this->render('inicioSesion.html.twig');
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
        $this->render('inicioSesion.html.twig', ['error' => $error]);
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
