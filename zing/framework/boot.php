<?php
//
// Config array.

$GLOBALS['_ZING'] = array();

define('ZING_VERSION',          '0.0.1');
define('ZING_SIGNATURE',        'Zing! Framework v' . ZING_VERSION);

define('ZING_FRAMEWORK_DIR',    __DIR__);
define('ZING_ROOT',             dirname(ZING_FRAMEWORK_DIR));
define('ZING_CONFIG_DIR',       ZING_ROOT . '/config');
define('ZING_PUBLIC_DIR',       ZING_ROOT . '/public');
define('ZING_TMP_DIR',          ZING_ROOT . '/tmp');
define('ZING_APP_DIR',          ZING_ROOT . '/app');
define('ZING_DATA_DIR',         ZING_ROOT . '/data');
define('ZING_VIEW_DIR',         ZING_APP_DIR . '/views');
define('ZING_CACHE_DIR',        ZING_TMP_DIR . '/cache');
define('ZING_COMPILED_DIR',     ZING_TMP_DIR . '/compiled');
define('ZING_VENDOR_DIR',       ZING_ROOT . '/vendor');
define('ZING_PLUGIN_DIR',       ZING_ROOT . '/plugins');
define('ZING_CONSOLE',          php_sapi_name() == 'cli');

zing_core_lib('common');
zing_load_config('main');
zing_load_environment('main');

//
// Export GDB config if present

if (isset($GLOBALS['_ZING']['config.gdb'])) {
    $GLOBALS['_GDB'] = array('default' => $GLOBALS['_ZING']['config.gdb']);
}

//
// Essentials!

function zing_lib($library) {
    require ZING_APP_DIR . "/lib/$library.php";
}

function zing_core_lib($library) {
    require ZING_FRAMEWORK_DIR . "/lib/$library.php";
}

function zing_plugin_lib($plugin, $library) {
    require ZING_PLUGIN_DIR . "/$plugin/lib/$library.php";
}

function zing_environments() {
    return array_map('basename', glob(ZING_CONFIG_DIR . '/environments/*', GLOB_ONLYDIR));
}

function zing_load_environment($name, $env = null) {
    $ENV = array();
    require ZING_CONFIG_DIR . '/environments/' . ($env === null ? ZING_ENV : $env) . '/' . $name . '.php';
    global $_ZING;
    foreach ($ENV as $k => $v) $_ZING[$k] = $v;
}

function zing_export_environment($name, $env = null) {
    $ENV = array();
    require ZING_CONFIG_DIR . '/environments/' . ($env === null ? ZING_ENV : $env) . '/' . $name . '.php';
    return $ENV;
}

/**
 * Load a named configuration file from the config/app directory.
 *
 * @param $name name of config file to load, excluding .php extension
 * @param $extracts optional array of name/value pairs to make available to the
 *        config file via extract(). Useful for passing builder objects.
 */
function zing_load_config($name, $extracts = null) {
    global $_ZING;
    if ($extracts) extract($extracts);
    require ZING_CONFIG_DIR . '/app/' . $name . '.php';
}

function zing_config($name, $default = null) {
    return isset($GLOBALS['_ZING'][$name]) ? $GLOBALS['_ZING'][$name] : $default;
}

/**
 * This is a bit of an oddball and nowhere seemed like the best place to put it.
 * It turns a qualified class name into a path and is primarily used by the default
 * view layer.
 *
 * One assumption - namespaces never contain uppercase
 *
 * Foo => foo
 * FooBar => foo_bar
 * namespace\Foo => namespace/foo
 * ns1\ns2\FooBar => ns1/ns2/foo_bar
 */
function zing_class_path($class) {
    if (is_object($class)) $class = get_class($class);
    return strtolower(preg_replace('|([^^/])([A-Z])|', '$1_$2', str_replace('\\', '/', $class)));
}

/**
 * Foo => foo
 * FooBar => foo_bar
 * namespace\Foo => foo
 * ns1\ns2\FooBar => foo_bar
 */
function zing_class_name($class) {
    if (is_object($class)) $class = get_class($class);
    if (($p = strrpos($class, '\\')) !== false) {
        $class = substr($class, $p + 1);
    }
    return strtolower(preg_replace('|([^^])([A-Z])|', '$1_$2', $class));
}

//
// Bail out if we're running from the console
// Everything hereafter is web-only...

if (ZING_CONSOLE) {
    zing_load_config('system');
    echo "Zing! Console initialised, environment: " . ZING_ENV . "\n";
    return;
}

//
// Input transformation

//
// Compatibility

if (strpos($_SERVER['SERVER_SOFTWARE'], 'lighttpd') !== false) {
    if (($p = strpos($_SERVER['REQUEST_URI'], '?')) !== false) {
        $_SERVER['QUERY_STRING'] = substr($_SERVER['REQUEST_URI'], $p + 1);
        parse_str($_SERVER['QUERY_STRING'], $_GET);
    }
}

//
// Rejig $_FILES layout to be sane and create objects in $_POST for each
// uploaded file. This implementation works with deeply nested files, e.g.:
// <input type='file' name='files[a][b][c][]' />

foreach ($_FILES as $k => $f) {
    if (is_string($f['name'])) {
        if ($f['error'] == UPLOAD_ERR_OK) {
            $_POST[$k] = new UploadedFile($f);
        } else {
            $_POST[$k] = new UploadedFileError($f['error']);
        }
    } elseif (is_array($f['name'])) {
        if (!is_array($_POST[$k])) {
            $_POST[$k] = array();
        }
        zing_fix_file_uploads_recurse($f['name'], $f['type'], $f['tmp_name'], $f['error'], $f['size'], $_POST[$k]);
    }
}

function zing_fix_file_uploads_recurse($n, $ty, $tm, $e, $s, &$target) {
    foreach ($n as $k => $v) {
        if (is_string($v)) {
            if ($e[$k] == UPLOAD_ERR_OK) {
                $target[$k] = new UploadedFile(array('name'      => $v,
                                                     'type'      => $ty[$k],
                                                     'tmp_name'  => $tm[$k],
                                                     'size'      => $s[$k]));
            } else {
                $target[$k] = new UploadedFileError($e[$k]);
            }
        } else {
            if (!is_array($target[$k])) {
                $target[$k] = array();                
            }
            zing_fix_file_uploads_recurse($n[$k], $ty[$k], $tm[$k], $e[$k], $s[$k], $target[$k]);
        }
    }
}

//
// The autoloader is auto-generated by the 'core:regenerate_autoload_map' task
// (or you can use 'core:kick', which updates all cached stuff)

function __autoload($class) {
    global $ZING_AUTOLOAD_MAP;
    if (isset($ZING_AUTOLOAD_MAP[$class])) {
        require ZING_ROOT . '/' . $ZING_AUTOLOAD_MAP[$class];
    }
}
?>