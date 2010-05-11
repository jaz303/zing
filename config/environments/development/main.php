<?php
/**
 * Database configuration
 */
$ENV['config.gdb'] = array(
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'username'  => 'root',
    'password'  => '',
    'database'  => 'zing_app_development'
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
$ENV['config.zing.view.recompile'] = 'mtime';
?>