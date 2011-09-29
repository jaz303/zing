<?php
/**
 * Database configuration
 */
$ENV['config.gdb'] = array(
    'driver'    => 'mysql',
    'host'      => '127.0.0.1',
    'username'  => 'root',
    'password'  => '',
    'database'  => 'random_acts'
);

/**
 * Recompilation strategy for routes
 *
 * true     - always
 * 'mtime'  - perform mtime check
 * false    - never
 */
$ENV['config.zing.routing.recompile'] = false;

/**
 * Recompilation strategy for PHP views
 *
 * true     - always
 * 'mtime'  - perform mtime check
 * false    - never
 */
$ENV['config.zing.view.recompile'] = false;

/**
 * Show error reports in the event of an exception propagating to the
 * top level?
 */
$ENV['config.zing.exception_reports'] = false;
?>