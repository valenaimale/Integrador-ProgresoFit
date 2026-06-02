<?php

namespace PAW\app\controllers;

class EscanearQrController
{
    public string $viewsDir;

    public function __construct()
    {
        $this->viewsDir = __DIR__ . '/../views/';
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

        require $this->viewsDir . 'escanearQr.view.php';
    }
}
