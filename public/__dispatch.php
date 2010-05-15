<?php
// Zing! Framework
//
// This is the standard entry-point for all web requests.
// You can also bypass the router and just require boot.php directly from your
// own scripts.

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
    
    $request    = zing\http\Request::build_request_from_input();
    $route      = zing\routing\Recognizer::recognize($request->path(), $request->method());
    
    if ($route === null) {
        zing\http\Exception::not_found();
    }
    
    if (!isset($route['controller']) || !isset($route['action'])) {
        die('invalid route - ' . var_dump($route, true));
    }
    
    $controller_class = preg_replace('/(^|_)([a-z])/e', 'strtoupper(\'$2\')', $route['controller']) . 'Controller';
    
    if (isset($route['namespace'])) {
        $controller_class = $route['namespace'] . '\\' . $controller_class;
    }
    
    if (!class_exists($controller_class, true)) {
        zing\http\Exception::not_found();
    }
    
    $controller = new $controller_class;
    $response   = $controller->invoke($request, $route['action']);
    
    // Controller can elect not to return a response - in this case the controller
    // should have sent any response itself.
    if ($response) {
        $response->set_header('X-Powered-By', ZING_SIGNATURE);
        $response->send();
    }
    
} catch (zing\http\Exception $http_exception) {
    
    $response = new zing\http\Response;
    $response->set_status($http_exception->get_status());
    $response->send();
    
    // TODO: check for error template & display
    
} catch (\Exception $exception) {
    
    $response = new zing\http\Response;
    $response->set_status(500);
    $response->send();
    
    // TODO: check for internal error and display
    
}
?>