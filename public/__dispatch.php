<?php
require dirname(__FILE__) . '/../config/boot.php';

try {
    
    $route_definitions  = ZING_CONFIG_DIR . '/routes.php';
    $compiled_routes    = ZING_COMPILED_DIR . '/zing.routing/recognizer.php';
    
    if (!file_exists($compiled_routes)
        || ($_ZING['config.zing.routing.recompile']
            && ($_ZING['config.zing.routing.recompile'] === true || filemtime($route_definitions) > filemtime($compiled_routes)))) {
        zing\routing\Router::compile($route_definitions, $compiled_routes);
    }
    
    require $compiled_routes;
    
    $request    = new zing\http\Request;
    $route      = zing\routing\Recognizer::recognize($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
    
    if ($route === null) {
        zing\http\Exception::not_found();
    }
    
    $controller_class   = $route['controller'] . 'Controller';
    $controller         = new $controller_class;
    
    $response = $controller->invoke($request, $route['action']);
    
    // Controller can elect not to return a response - in this case the controller
    // should have sent any response itself.
    if ($response) {
        $response->set_header('X-Powered-By', ZING_SIGNATURE);
        $response->send();
    }
    
} catch (zing\http\Exception $http_exception) {
    
    // TODO: check for error template and display

} catch (\Exception $exception) {
    
    // TODO: check for internal error and display
    
}
?>