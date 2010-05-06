<?php
require 'lib/router.php';

$router = new zing\router\Router;


$router->map('home', '');
$router->map('auctions', 'auctions/:id/:action');


?>