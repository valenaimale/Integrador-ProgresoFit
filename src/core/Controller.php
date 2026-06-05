<?php

namespace PAW\Core;

use Twig\Environment;

abstract class Controller
{
    protected Environment $twig;

    public function __construct()
    {
        global $twig;
        $this->twig = $twig;
    }

    protected function render(string $template, array $data = []): void
    {
        $this->twig->addGlobal('session', $_SESSION ?? []);
        echo $this->twig->render($template, $data);
    }
}
