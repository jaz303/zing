<?php
// Application routes
// $R is an instance of zing\routing\Router

$R->connect(':controller/:action/:id', array('action' => 'index', 'id' => null));

// Here's an example namespaced route for setting up an "admin" area
$R->with_options(array('namespace' => 'admin'), function($R) {
    $R->connect('admin/:controller/:action/:id', array('action' => 'index', 'id' => null));
});
?>