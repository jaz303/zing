<?php
namespace zing;

class Dispatcher
{
    public function dispatch($request) {
        
        $this->load_routes();
        
        try {
            
            $route = \zing\routing\Recognizer::recognize($request->path(), $request->method());
            if ($route === null) {
                throw new \NotFoundException("no matching route found for '{$request->request_uri()}'");
            }
            
            $request->merge_params($route);

            if (!isset($route['controller']) || !isset($route['action'])) {
                throw new \Exception("invalid route - missing controller or action");
            }

            $controller_class = preg_replace('/(^|_)([a-z])/e',
                                             'strtoupper(\'$2\')',
                                             $route['controller']) . 'Controller';

            if (isset($route['namespace'])) {
                $controller_class = $route['namespace'] . '\\' . $controller_class;
            }

            if (!class_exists($controller_class, true)) {
                throw new \zing\http\Exception(\zing\http\Constants::NOT_FOUND,
                                              "no such controller - '$controller_class'");
            }

            $controller = new $controller_class;
            $this->maybe_send($controller->invoke($request, $route['action']));
            
        } catch (\Exception $exception) {
            if ($GLOBALS['_ZING']['config.zing.exception_reports']) {
                header("Content-Type: text/html");
                require ZING_ROOT . '/framework/templates/exception_report.php';
            } else {
                $this->maybe_send($GLOBALS['_ZING']['zing.exception_handler']($request, $exception));
            }
        }
        
    }
    
    private function load_routes() {
        
        $route_definitions  = ZING_CONFIG_DIR . '/routes.php';
        $compiled_routes    = ZING_COMPILED_DIR . '/zing.routing/recognizer.php';

        if (!file_exists($compiled_routes)
            || ($GLOBALS['_ZING']['config.zing.routing.recompile']
                && ($GLOBALS['_ZING']['config.zing.routing.recompile'] === true || filemtime($route_definitions) > filemtime($compiled_routes)))) {
            \zing\routing\Router::compile($route_definitions, $compiled_routes);
        }

        require $compiled_routes;
        
    }
    
    private function maybe_send($response) {
        // A controller can elect not to return a response - in this case the controller
        // must send any response itself.
        if ($response) {
            $response->set_header('X-Powered-By', ZING_SIGNATURE);
            $response->send();
        }
    }
}
?>