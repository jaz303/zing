<?php
/**
 * Database configuration
 */
$ENV['config.gdb'] = array(
    'driver'    => 'mysql',
    'host'      => '127.0.0.1',
    'username'  => 'root',
    'password'  => '',
    'database'  => 'zing_development'
);

/**
 * Recompilation strategy for routes
 *
 * true     - always
 * 'mtime'  - perform mtime check
 * false    - never
 */
$ENV['config.zing.routing.recompile'] = true;

/**
 * Recompilation strategy for PHP views
 *
 * true     - always
 * 'mtime'  - perform mtime check
 * false    - never
 */
$ENV['config.zing.view.recompile'] = true;

/**
 * Show error reports in the event of an exception propagating to the
 * top level?
 */
$ENV['config.zing.exception_reports'] = true;
?>