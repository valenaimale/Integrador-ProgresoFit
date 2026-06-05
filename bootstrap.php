<?php

// ============================================
// BOOTSTRAP - Configuración inicial de la app
// ============================================

// 1. Cargar el autoload de Composer
require __DIR__ . '/vendor/autoload.php';

// 2. Cargar configuración (SMTP, constantes)
require __DIR__ . '/src/config.php';

// 3. Iniciar sesiones
session_start();
header('Content-Type: text/html; charset=utf-8');

// 4. Configurar manejo de errores (Whoops)
try {
    $whoops = new \Whoops\Run;
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
    $whoops->register();
} catch (Exception $e) {
    // Si Whoops no está instalado, continuamos sin él
}
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$log = new Logger('libreria-PAW');
$log->pushHandler(new StreamHandler(__DIR__ . '/logs/lib.log', Logger::DEBUG));
//seteo el handler al logger con nivel de logger a DEBUG
//de esa manera puedo utilizar todos los tipos de logs
// 5. Inicializar Twig
$twigLoader = new FilesystemLoader(__DIR__ . '/src/app/views/');
$twig = new Environment($twigLoader, [
    'cache'       => false,
    'auto_escape' => 'html',
    'debug'       => true,
]);
$twig->addGlobal('session', $_SESSION ?? []);

// 6. Importar el Router
use PAW\Core\Router;

// 7. Crear instancia del Router
$router = new Router();
$router->setLogger($log);
use PAW\Core\Request;
$request = new Request();//creo el objeto request
$request->setLogger($log);
$router->cargar_rutas();//cargo las rutas de la aplicacion hardcodeadas
$router->direct($request);//le paso la request al router, que la va a rutear a donde corresponda


