<?php
require dirname(__FILE__) . '/../config/boot.php';

require CACHE_DIR . '/compiled/routes.php';

$route = __zing_route($request);

if (is_array($route)) {
    if (isset($route['controller_class'])) {
        $controller = new $route['controller_class'];
        $controller->__perform_action($route['controller_class'], $route['action']);
    } else {
        die();
    }
} else {
    if (!is_numeric($route)) $route = 404;
    $error_template = PUBLIC_DIR . '/' . $route . '.php';
    if (file_exists($error_template)) {
        require $error_template;
    } else {
        // generic error page
    }
}
?>