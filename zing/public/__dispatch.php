<?php
// Zing! Framework
//
// This is the standard entry-point for all web requests.
// You can also bypass the router and just require boot.php directly from your
// own scripts.

require __DIR__ . '/../framework/boot.php';

$dispatcher = new \zing\Dispatcher;
$dispatcher->dispatch(zing\http\Request::build_request_from_input());
?>