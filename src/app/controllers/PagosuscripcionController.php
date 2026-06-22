<?php

namespace PAW\app\controllers;

use PAW\Core\Controller;
use PAW\app\services\ApiClient;

class PagosuscripcionController extends Controller
{
    private ApiClient $api;

    public function __construct()
    {
        parent::__construct();
        $this->api = new ApiClient();
    }

    public function mostrar()
    {
        if (empty($_SESSION['user'])) {
            header('Location: /inicio-sesion');
            exit;
        }

        $resPlanes = $this->api->get('/suscripciones/planes', $_SESSION['jwt']);
        $planes = $resPlanes['ok'] ? $resPlanes['data'] : [];

        $this->render('pagosuscripcion.html.twig', [
            'planes' => $planes,
            'mensaje' => $_GET['mensaje'] ?? null,
            'error'   => $_GET['error'] ?? null
        ]);
    }

    public function suscribir()
    {
        if (empty($_SESSION['user'])) {
            header('Location: /inicio-sesion');
            exit;
        }

        $plan = $_POST['plan'] ?? null;
        if (!$plan) {
            header('Location: /pago-suscripcion?error=Debe seleccionar un plan');
            exit;
        }

        // 1. Crear suscripción pendiente
        $susc = $this->api->post('/suscripciones', ['plan' => $plan], $_SESSION['jwt']);
        if (!$susc['ok']) {
            $error = $susc['data']['error'] ?? 'Error al crear la suscripción';
            header("Location: /pago-suscripcion?error=" . urlencode($error));
            exit;
        }

        $suscripcionId = $susc['data']['id'];
        $monto = $susc['data']['precio'];
        $nombrePlan = $susc['data']['plan'];
        $user = $_SESSION['user'];

        // 2. Crear preferencia de pago en MP
        $pref = $this->api->post('/mercadopago/preference', [
            'suscripcion_id' => $suscripcionId,
            'monto'          => (float)$monto,
            'titular'        => $user['nombre'] ?? 'Alumno',
            'email'          => $user['email'],
            'descripcion'    => "ProgresoFit - {$nombrePlan} 30d"
        ], $_SESSION['jwt']);

        if (!$pref['ok']) {
            $msg = $pref['data']['message'] ?? $pref['data']['error'] ?? 'Error al conectar con Mercado Pago';
            header("Location: /pago-suscripcion?error=" . urlencode($msg));
            exit;
        }

        // 3. Redirigir a MP
        header("Location: " . $pref['data']['init_point']);
        exit;
    }

    public function pagoExitoso()
    {
        $this->render('pago-exitoso.html.twig', [
            'mensaje' => $_GET['mensaje'] ?? null,
        ]);
    }

    public function pagoFallido()
    {
        $this->render('pago-fallido.html.twig', [
            'error' => $_GET['error'] ?? 'El pago no pudo procesarse',
        ]);
    }
}
