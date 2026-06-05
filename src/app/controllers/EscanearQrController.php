<?php

namespace PAW\app\controllers;

use PAW\Core\Controller;

class EscanearQrController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function mostrar()
    {
        if (empty($_SESSION['user'])) {
            header('Location: /inicio-sesion');
            exit;
        }

        if ($_SESSION['user']['rol'] !== 'ADMIN') {
            header('Location: /');
            exit;
        }

        $jwt        = $_SESSION['jwt'] ?? '';
        $gimnasioId = 1;

        $this->render('escanearQr.html.twig', [
            'jwt'        => $jwt,
            'gimnasioId' => $gimnasioId,
        ]);
    }
}
