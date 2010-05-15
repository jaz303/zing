<?php
// Application routes
// $R is an instance of zing\routing\Router

$R->connect(':controller/:action/:id', array('action' => 'index', 'id' => null));
?>