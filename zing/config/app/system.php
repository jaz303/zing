<?php
/**
 * Classes used to locate plugins
 */
$_ZING['zing.plugin.locators'][] = 'zing\plugin\DefaultLocator';

/**
 * Classes used to locate generators
 */
$_ZING['zing.generator.locators'][] = 'zing\generator\DefaultLocator';

/**
 * Development server
 *
 * Today, only a single adapter is supported: lighttpd
 *
 * Of course, you can still run under Apache by setting up a vhost but script/server
 * is a lot more fluid.
 */
$_ZING['zing.server'] = array(
    'adapter'       => 'lighttpd',
    'path'          => null,        // path to server binary, will be guessed if null
    'php-fcgi'      => null,        // path to PHP FCGI binary, will be guessed if null
    'port'          => 3000,        // server port, will use adapter default if null
);
?>