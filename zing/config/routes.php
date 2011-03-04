<?php
// Application routes
// $R is an instance of zing\routing\Router

$R->connect(':controller/:action/:id', array('action' => 'index', 'id' => null));

// begin: cms-routes
\zing\cms\routing\Router::draw_routes($R, 'admin', 'assets');
// end: cms-routes
?>