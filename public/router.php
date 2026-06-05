<?php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Servir archivos estáticos (css, js, imágenes) directamente
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Todo lo demás va a index.php
require __DIR__ . '/index.php';
