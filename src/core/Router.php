<?php

namespace PAW\Core;


use PAW\Core\Exceptions\RouteNotFoundException;
use PAW\Core\Traits\Loggable; //incluyo loggable para 
//usar el trait

class Router {
    use Loggable;
    private $routes = [];
    public string $notFound = 'not_found';
    public string $internalError = 'internal_error';
    
    public function __construct(){
        $this->register($this->notFound,  'ErrorController@notFound');
        $this->register($this->internalError,  'ErrorController@internalError');
    }

    public function call($controller, $action){
        $controller_name = "PAW\\app\\controllers\\{$controller}";
        $objController = new $controller_name;
        $objController->$action();
    }
    public function getController($clavemethodmaspath){
        if(!array_key_exists($clavemethodmaspath, $this->routes)){
            throw new RouteNotFoundException("No existe ruta para este path");
        }
        return $valorControllerYAction = explode("@", $this->routes[$clavemethodmaspath]);
    }    
//codigo de valen
    public function direct(Request $request){
        $clavemethodmaspath = $request->route();//obtengo de la request el method_http + la url solicitada por el usuario
        $clave_path_method=explode("@", $clavemethodmaspath);//separo method http de path solo para logger
      
        try{
            $valorcontrolleraction = $this->getController($clavemethodmaspath);//intento obtener el controlador + la accion a realizar por el mismo
            $this->call($valorcontrolleraction[0], $valorcontrolleraction[1]);
            $this->logger->info(
                "Status Code:200", 
                [
                    "Path"=>$clave_path_method[1],
                    "Method"=>$clave_path_method[0],
                ]
            );
        }    
        catch (RouteNotFoundException $e){
            $valorcontrolleraction = $this->getController($this->notFound);//si salta la exception de que no se encontro la ruta, intenta obtener
            //controlador + accion a realizar para el caso de una ruta no encontrada
            $this->call($valorcontrolleraction[0], $valorcontrolleraction[1]);
            $this->logger->debug('Status Code:404 - Route Not Found', ["ERROR"=>$e]);
        }

        catch (Exception $e){
            $valorcontrolleraction = $this->getController($this->internalError);//si salta la exception, intenta obtener
            //controlador + accion a realizar para el caso de exception
            $this->call($valorcontrolleraction[0], $valorcontrolleraction[1]);
            $this->logger->error('Status Code:500 - Internal Server Error', ["ERROR"=>$e]);
        }
    }

    //cambiarlas
    //cargo las rutas (siempre van a estar hardcodeadas)
    public function cargar_rutas(){
        $this->register('GET@/', 'IndexController@index');
        $this->register('GET@/home-sin-login', 'HomesinloggedController@index');

        // Catálogo y Rutinas
        $this->register('GET@/catalogo', 'CatalogoController@listar');
        $this->register('GET@/rutinas', 'RutinasController@listar');
        $this->register('GET@/rutina1', 'RutinaIndController@mostrar');
        $this->register('GET@/rutina2', 'RutinaIndController@mostrar');
        $this->register('GET@/rutina3', 'RutinaIndController@mostrar');
        $this->register('GET@/rutina4', 'RutinaIndController@mostrar');

        // Ejercicio
        $this->register('GET@/ejercicio', 'EjercicioController@mostrar');


        // Crear cuenta
        $this->register('GET@/crearCuenta', 'CrearCuentaController@crearCuenta');
        $this->register('POST@/crearCuenta', 'CrearCuentaController@crearCuentaProcess');
        $this->register('GET@/crearCuentaCreada', 'CrearCuentaController@cuentaCreada');
        $this->register('GET@/crearcuenta-gym', 'CrearcuentaGymController@crear');

        // Inicio de sesión
        $this->register('GET@/inicio-sesion', 'InicioSesionController@index');
        $this->register('POST@/inicio-sesion', 'InicioSesionController@process');
        $this->register('GET@/cerrar-sesion', 'InicioSesionController@logout');

        // Perfiles
        $this->register('GET@/perfil-gimnasio', 'PerfilGimnasioController@mostrar');
        $this->register('GET@/perfil-user', 'PerfilUserController@mostrar');
        $this->register('GET@/perfil-entrenador', 'PerfilEntrenadorController@mostrar');

        // Formulario de compra e Historial
        $this->register('GET@/formulario', 'FormularioController@index');
        $this->register('POST@/formulario', 'FormularioController@process');
        $this->register('GET@/mis-compras', 'FormularioController@historial');

        // Suscripción
        $this->register('GET@/pagos-suscripcion', 'PagosuscripcionController@mostrar');


        // Gimnasios
        $this->register('GET@/gimnasios', 'GymController@listar');

        // Nosotros
        $this->register('GET@/nosotros', 'NosotrosController@mostrar_nosotros');

    }
        
    //Registra las rutas en el ROUTER
    public function register($method_http_y_path, $controller_y_action){
        $this->routes[$method_http_y_path] = $controller_y_action;
    }
}
