<?php

namespace PAW\app\services;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigRenderer
{
    private Environment $twig;

    public function __construct()
    {
        $loader = new FilesystemLoader(__DIR__ . '/../views/twig');
        $this->twig = new Environment($loader, [
            'cache' => false,
        ]);

        $this->twig->addGlobal('session', $_SESSION ?? []);
    }

    public function render(string $template, array $data = []): void
    {
        echo $this->twig->render($template, $data);
    }
}
