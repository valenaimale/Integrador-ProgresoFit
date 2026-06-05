<?php

namespace PAW\app\controllers;

use PAW\Core\Controller;
use PAW\app\services\ApiClient;

class CrearCuentaController extends Controller
{
    private ApiClient $api;

    public function __construct()
    {
        parent::__construct();
        $this->api = new ApiClient();
    }

    public function crearCuenta()
    {
        $this->render('crearCuenta.html.twig');
    }

    public function cuentaCreada()
    {
        $this->render('crearCuentaCreada.html.twig');
    }

    public function crearCuentaProcess()
    {
        $nombre      = $_POST['nombre_apellido'] ?? '';
        $email       = $_POST['email'] ?? '';
        $contraseña  = $_POST['contraseña'] ?? '';
        $ccontraseña = $_POST['ccontraseña'] ?? '';

        if (!$nombre || !$email || !$ccontraseña || !$contraseña) {
            $this->render('crearCuenta.html.twig', ['error' => 'Hay campos obligatorios sin completar']);
            exit;
        }
        if ($contraseña !== $ccontraseña) {
            $this->render('crearCuenta.html.twig', ['error' => 'Las contraseñas no coinciden']);
            exit;
        }

        $response = $this->api->post('/usuarios/alumno', [
            'nombre'   => $nombre,
            'email'    => $email,
            'password' => $contraseña,
        ]);

        if ($response['ok']) {
            $login = $this->api->post('/auth/login', [
                'email'    => $email,
                'password' => $contraseña,
            ]);
            if ($login['ok']) {
                $_SESSION['jwt']  = $login['data']['token'];
                $_SESSION['user'] = $login['data']['user'];
            }
            $this->render('crearCuentaCreada.html.twig');
            exit;
        }

        $error = $response['data']['message'] ?? 'Error al crear la cuenta. El email puede ya estar registrado.';
        $this->render('crearCuenta.html.twig', ['error' => $error]);
    }

    public function mostrarPreCrearCuenta()
    {
        $this->render('crearCuenta.html.twig');
    }

    public function mostrarCrearCuentaAlumno()
    {
        $this->render('crearCuenta.html.twig');
    }

    public function crearCuentaProcessAlumno()
    {
        $contraseña  = $_POST['contraseña'] ?? '';
        $ccontraseña = $_POST['ccontraseña'] ?? '';
        $nombre      = $_POST['nombre_apellido'] ?? '';
        $email       = $_POST['email'] ?? '';

        if (!$nombre || !$email || !$ccontraseña || !$contraseña) {
            $this->render('crearCuenta.html.twig', ['error' => 'Hay campos obligatorios sin completar']);
            exit;
        }
        if ($contraseña !== $ccontraseña) {
            $this->render('crearCuenta.html.twig', ['error' => 'Las contraseñas no coinciden']);
            exit;
        }

        $response = $this->api->post('/usuarios/alumno', [
            'nombre'   => $nombre,
            'email'    => $email,
            'password' => $contraseña,
        ]);

        if ($response['ok']) {
            $login = $this->api->post('/auth/login', [
                'email'    => $email,
                'password' => $contraseña,
            ]);
            if ($login['ok']) {
                $_SESSION['jwt']  = $login['data']['token'];
                $_SESSION['user'] = $login['data']['user'];
            }
            $this->render('crearCuentaCreada.html.twig');
            exit;
        }

        $error = $response['data']['message'] ?? 'Error al crear la cuenta. El email puede ya estar registrado.';
        $this->render('crearCuenta.html.twig', ['error' => $error]);
    }

    public function mostrarCrearCuentaEntrenador()
    {
        $this->render('crearCuenta.html.twig');
    }

    public function crearCuentaProcessEntrenador()
    {
        $nombre      = $_POST['nombre'] ?? '';
        $email       = $_POST['email'] ?? '';
        $contraseña  = $_POST['contra_nueva'] ?? '';
        $ccontraseña = $_POST['contra_nueva_repetida'] ?? '';

        if (!$nombre || !$email || !$ccontraseña || !$contraseña) {
            $this->render('crearCuenta.html.twig', ['error' => 'Hay campos obligatorios sin completar']);
            exit;
        }
        if ($contraseña !== $ccontraseña) {
            $this->render('crearCuenta.html.twig', ['error' => 'Las contraseñas no coinciden']);
            exit;
        }

        $especialidad = $_POST['especialidad'] ?? '';
        $horario      = $_POST['horario'] ?? '';

        $response = $this->api->post('/entrenadores/registrar', [
            'nombre'       => $nombre,
            'email'        => $email,
            'password'     => $contraseña,
            'especialidad' => $especialidad,
            'horario'      => $horario,
        ]);

        if ($response['ok']) {
            $login = $this->api->post('/auth/login', [
                'email'    => $email,
                'password' => $contraseña,
            ]);
            if ($login['ok']) {
                $_SESSION['jwt']  = $login['data']['token'];
                $_SESSION['user'] = $login['data']['user'];
            }
            $this->render('crearCuentaCreada.html.twig');
            exit;
        }

        $error = $response['data']['message'] ?? 'Error al crear la cuenta. El email puede ya estar registrado.';
        $this->render('crearCuenta.html.twig', ['error' => $error]);
    }

    public function mostrarCrearCuentaGym()
    {
        $this->render('crearcuenta-gym.html.twig');
    }

    public function crearCuentaProcessGym()
    {
        $nombre      = $_POST['nombre'] ?? '';
        $email       = $_POST['email'] ?? '';
        $direccion   = $_POST['direccion'] ?? '';
        $contraseña  = $_POST['contra_nueva'] ?? '';
        $ccontraseña = $_POST['contra_nueva_repetida'] ?? '';

        if (!$nombre || !$email || !$direccion || !$ccontraseña || !$contraseña) {
            $this->render('crearcuenta-gym.html.twig', ['error' => 'Hay campos obligatorios sin completar']);
            exit;
        }
        if ($contraseña !== $ccontraseña) {
            $this->render('crearcuenta-gym.html.twig', ['error' => 'Las contraseñas no coinciden']);
            exit;
        }

        $horarios    = $_POST['horarios']    ?? '';
        $telefono    = $_POST['telefono']    ?? '';
        $servicios   = $_POST['servicios']   ?? '';
        $descripcion = $_POST['descripcion'] ?? '';

        $response = $this->api->post('/gimnasios/registrar', [
            'nombre'      => $nombre,
            'email'       => $email,
            'password'    => $contraseña,
            'direccion'   => $direccion,
            'horarios'    => $horarios,
            'telefono'    => $telefono,
            'servicios'   => $servicios,
            'descripcion' => $descripcion,
        ]);

        if ($response['ok']) {
            $login = $this->api->post('/auth/login', [
                'email'    => $email,
                'password' => $contraseña,
            ]);
            if ($login['ok']) {
                $_SESSION['jwt']  = $login['data']['token'];
                $_SESSION['user'] = $login['data']['user'];
            }
            $this->render('crearCuentaCreada.html.twig');
            exit;
        }

        $error = $response['data']['message'] ?? 'Error al crear la cuenta. El email puede ya estar registrado.';
        $this->render('crearcuenta-gym.html.twig', ['error' => $error]);
    }
}
