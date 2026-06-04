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

    public function mostrarPreCrearCuenta(){
        require $this->viewsDir . 'preCrearCuenta.view.php';
    }
    
    public function cuentaCreada()
    {
        require $this->viewsDir . 'crearCuentaCreada.view.php';
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------
//  CREAR CUENTA ALUMNO
    public function mostrarCrearCuentaAlumno(){
        require $this->viewsDir . 'crearCuenta.view.php';
    }

    public function crearCuentaProcessAlumno(){
        $contraseña = $_POST['contraseña'];
        $ccontraseña = $_POST['ccontraseña'];
        $nombre     = $_POST['nombre_apellido'];
        $email      = $_POST['email'];

        if(!$nombre || !$email ||!$ccontraseña || !$contraseña){
            $error = 'Hay campos obligatorios sin completar';
            require $this->viewsDir . 'crearCuenta.view.php';
            exit;
        }
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
            require $this->viewsDir . 'crearCuentaCreada.view.php';
            exit;
        }

        $error = $response['data']['message'] ?? 'Error al crear la cuenta. El email puede ya estar registrado.';
        require $this->viewsDir . 'crearCuenta.view.php';
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------
//  CREAR CUENTA ENTRENADOR
    public function mostrarCrearCuentaEntrenador(){
        require $this->viewsDir . 'crearCuentaEntrenador.view.php';//falta crear la vista
    }

    public function crearCuentaProcessEntrenador(){
        $nombre     = $_POST['nombre_apellido'];
        $email      = $_POST['email'];
        $contraseña = $_POST['contraseña'];
        $ccontraseña = $_POST['ccontraseña'];
        
        if(!$nombre || !$email ||!$ccontraseña || !$contraseña){
            $error = 'Hay campos obligatorios sin completar';
            require $this->viewsDir . 'crearCuentaEntrenador.view.php';
            exit;
        }
        if ($contraseña !== $ccontraseña) {
            $error = 'Las contraseñas no coinciden';
            require $this->viewsDir . 'crearCuentaEntrenador.view.php';
            exit;
        }
        $especialidad = $_POST['especialidad'] ?? '';
        $horario = $_POST['horario'] ?? '';
        $response = $this->api->post('/usuarios/alumno', [
            'nombre'   => $nombre,
            'email'    => $email,
            'password' => $contraseña,
            'especialidad' => $especialidad,
            'horario' => $horario
        ]);

        if ($response['ok']) {
            require $this->viewsDir . 'crearCuentaCreada.view.php';
            exit;
        }

        $error = $response['data']['message'] ?? 'Error al crear la cuenta. El email puede ya estar registrado.';
        require $this->viewsDir . 'crearCuentaEntrenador.view.php';
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------
//  CREAR CUENTA GIMNASIO
    public function mostrarCrearCuentaGym(){
        require $this->viewsDir . 'crearCuentaGym.view.php';
    }
    public function crearCuentaProcessGym(){
        $nombre     = $_POST['nombre'];
        $email      = $_POST['email'];
        $direccion = $_POST['direccion'];
        $contraseña = $_POST['contra_nueva'];
        $ccontraseña = $_POST['contra_nueva_repetida'];

        if(!$nombre || !$email || !$direccion || !$ccontraseña || !$contraseña){
            $error = 'Hay campos obligatorios sin completar';
            require $this->viewsDir . 'crearCuentaGym.view.php';
            exit;
        }
        if ($contraseña !== $ccontraseña) {
            $error = 'Las contraseñas no coinciden';
            require $this->viewsDir . 'crearCuentaGym.view.php';
            exit;
        }
        
        $horarios=$_POST['horarios'] ?? '';
        $telefono=$_POST['telefono'] ?? '';
        $servicios=$_POST['servicios'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';

        $response = $this->api->post('/gimnasios/registrar', [
            'nombre'   => $nombre,
            'email'    => $email,
            'password' => $contraseña,
            'direccion'=> $direccion,
            'horarios'=> $horarios,
            'telefono'=> $telefono,
            'servicios'=> $servicios
        ]);

        if ($response['ok']) {
            require $this->viewsDir . 'crearCuentaCreada.view.php';
            exit;
        }

        $error = $response['data']['message'] ?? 'Error al crear la cuenta. El email puede ya estar registrado.';
        require $this->viewsDir . 'crearCuentaGym.view.php';

    }


    
    /*$this->register('GET@/crearCuenta', 'CrearCuentaController@mostrarCrearCuentaAlumno');
    $this->register('POST@/crearCuenta', 'CrearCuentaController@crearCuentaProcessAlumno');
    $this->register('GET@/crearCuenta-entrenador', 'CrearCuentaController@mostrarCrearCuentaEntrenador');
    $this->register('POST@/crearCuenta-entrenador', 'CrearCuentaController@crearCuentaProcessEntrenador');
    $this->register('GET@/crearCuenta-gym', 'CrearCuentaController@mostrarCrearCuentaGym');
    $this->register('POST@/crearCuenta-gym', 'CrearCuentaController@crearCuentaProcessGym');*/

}
