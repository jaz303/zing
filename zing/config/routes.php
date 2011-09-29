<?php
// Application routes
// $R is an instance of zing\routing\Router

$R->connect(':controller/:action/:id', array('action' => 'index', 'id' => null));

// begin: cms-routes
\ff\routing\Router::draw($R, function($R) {
    $R->defaults();
    $R->editable('articles', 'admin', 'Articles');
    $R->editable('commissioners', 'admin', 'Commissioners');
    $R->editable('artists', 'admin', 'Artists');
});
// end: cms-routes
?>