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
        $this->render('preCrearCuenta.html.twig');
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
        $this->render('crearCuentaEntrenador.html.twig');
    }

    public function crearCuentaProcessEntrenador()
    {
        $nombre      = $_POST['nombre'] ?? '';
        $email       = $_POST['email'] ?? '';
        $contraseña  = $_POST['contra_nueva'] ?? '';
        $ccontraseña = $_POST['contra_nueva_repetida'] ?? '';

        if (!$nombre || !$email || !$ccontraseña || !$contraseña) {
            $this->render('crearCuentaEntrenador.html.twig', ['error' => 'Hay campos obligatorios sin completar']);
            exit;
        }
        if ($contraseña !== $ccontraseña) {
            $this->render('crearCuentaEntrenador.html.twig', ['error' => 'Las contraseñas no coinciden']);
            exit;
        }

        $especialidad = $_POST['especialidad'] ?? '';
        $horario      = $_POST['horario']      ?? '';
        $descripcion  = $_POST['descripcion']  ?? '';

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

        $response = $this->api->post('/entrenadores/registrar', [
            'nombre'       => $nombre,
            'email'        => $email,
            'password'     => $contraseña,
            'especialidad' => $especialidad,
            'horario'      => $horario,
            'descripcion'  => $descripcion,
            'foto_url'     => $foto_url,
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
        $this->render('crearCuentaEntrenador.html.twig', ['error' => $error]);
    }

    public function mostrarCrearCuentaGym()
    {
        $this->render('crearCuentaGym.html.twig');
    }

    public function crearCuentaProcessGym()
    {
        $nombre      = $_POST['nombre'] ?? '';
        $email       = $_POST['email'] ?? '';
        $direccion   = $_POST['direccion'] ?? '';
        $contraseña  = $_POST['contra_nueva'] ?? '';
        $ccontraseña = $_POST['contra_nueva_repetida'] ?? '';

        if (!$nombre || !$email || !$direccion || !$ccontraseña || !$contraseña) {
            $this->render('crearCuentaGym.html.twig', ['error' => 'Hay campos obligatorios sin completar']);
            exit;
        }
        if ($contraseña !== $ccontraseña) {
            $this->render('crearCuentaGym.html.twig', ['error' => 'Las contraseñas no coinciden']);
            exit;
        }

        $horarios    = $_POST['horarios']    ?? '';
        $telefono    = $_POST['telefono']    ?? '';
        $servicios   = $_POST['servicios']   ?? '';
        $descripcion = $_POST['descripcion'] ?? '';

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

        $response = $this->api->post('/gimnasios/registrar', [
            'nombre'      => $nombre,
            'email'       => $email,
            'password'    => $contraseña,
            'direccion'   => $direccion,
            'horarios'    => $horarios,
            'telefono'    => $telefono,
            'servicios'   => $servicios,
            'descripcion' => $descripcion,
            'foto_url'    => $foto_url,
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
        $this->render('crearCuentaGym.html.twig', ['error' => $error]);
    }
}
