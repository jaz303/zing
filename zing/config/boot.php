<?php
//
// Customisable bits

/*
 * Customise this code block to automatically detect the operating environment.
 * I usually use a $_SERVER key (set via Apache's SetEnv) or the server port
 * (e.g. anything other than port 80 is development).
 */
if (isset($_SERVER['ZING_ENV'])) {
    define('ZING_ENV', $_SERVER['ZING_ENV']);
} else {
    define('ZING_ENV', 'development');
}

date_default_timezone_set('Europe/London');

//
// End customisable bits

//
// Config array.

$GLOBALS['_ZING'] = array();

define('ZING_VERSION',          '0.0.1');
define('ZING_SIGNATURE',        'Zing! Framework v' . ZING_VERSION);

define('ZING_CONFIG_DIR',       dirname(__FILE__));
define('ZING_ROOT',             dirname(ZING_CONFIG_DIR));
define('ZING_PUBLIC_DIR',       ZING_ROOT . '/public');
define('ZING_TMP_DIR',          ZING_ROOT . '/tmp');
define('ZING_APP_DIR',          ZING_ROOT . '/app');
define('ZING_VIEW_DIR',         ZING_APP_DIR . '/views');
define('ZING_CACHE_DIR',        ZING_TMP_DIR . '/cache');
define('ZING_COMPILED_DIR',     ZING_TMP_DIR . '/compiled');
define('ZING_VENDOR_DIR',       ZING_ROOT . '/vendor');
define('ZING_PLUGIN_DIR',       ZING_VENDOR_DIR . '/plugins');
define('ZING_CONSOLE',          php_sapi_name() == 'cli');

zing_lib('common');
zing_load_environment('main');
zing_load_configuration('main');

//
// Export GDB config if present

if (isset($GLOBALS['_ZING']['config.gdb'])) {
    $GLOBALS['_GDB'] = array('default' => $GLOBALS['_ZING']['config.gdb']);
}

//
// Essentials!

function zing_lib($library) {
    require ZING_ROOT . "/framework/lib/{$library}.php";
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

function zing_load_configuration($name) {
    global $_ZING;
    require ZING_CONFIG_DIR . '/app/' . $name . '.php';
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

//
// Bail out if we're running from the console
// Everything hereafter is web-only...

if (ZING_CONSOLE) {
    echo "Zing! Console initialised, environment: " . ZING_ENV . "\n";
    return;
}

//
// Input transformation

//
// Request keys beginning with @ and $ and converted to date and money values

zing_rewire($_POST);
zing_rewire($_GET);
zing_rewire($_REQUEST);

//
// Rejig $_FILES layout to be sane and create objects in $_POST for each
// uploaded file. This implementation works with deeply nested files, e.g.:
// <input type='file' name='files[a][b][c][]' />

zing_fix_file_uploads();

function zing_rewire(&$array) {
    foreach (array_keys($array) as $k) {
        if ($k[0] == '@') {
            $array[substr($k, 1)] = Date::from_request($array[$k]);
            unset($array[$k]);
        } elseif ($k[0] == '$') {
            $array[substr($k, 1)] = Money::from_request($array[$k]);
            unset($array[$k]);
        } elseif (is_array($array[$k])) {
            zing_rewire($array[$k]);
        }
    }
}

function zing_fix_file_uploads() {
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

    // SUPERLOAD-BEGIN
	static $map = array (
	  'IllegalArgumentException' => 'vendor/base-php/inc/base.php',
	  'IllegalStateException' => 'vendor/base-php/inc/base.php',
	  'UnsupportedOperationException' => 'vendor/base-php/inc/base.php',
	  'IOException' => 'vendor/base-php/inc/base.php',
	  'NotFoundException' => 'vendor/base-php/inc/base.php',
	  'NoSuchMethodException' => 'vendor/base-php/inc/base.php',
	  'SecurityException' => 'vendor/base-php/inc/base.php',
	  'SyntaxException' => 'vendor/base-php/inc/base.php',
	  'Base' => 'vendor/base-php/inc/base.php',
	  'Callback' => 'vendor/base-php/inc/base.php',
	  'FunctionCallback' => 'vendor/base-php/inc/base.php',
	  'InstanceCallback' => 'vendor/base-php/inc/base.php',
	  'StaticCallback' => 'vendor/base-php/inc/base.php',
	  'Inflector' => 'vendor/base-php/inc/base.php',
	  'Date' => 'vendor/base-php/inc/date.php',
	  'Date_Time' => 'vendor/base-php/inc/date.php',
	  'Errors' => 'vendor/base-php/inc/errors.php',
	  'File' => 'vendor/base-php/inc/file.php',
	  'UploadedFile' => 'vendor/base-php/inc/file.php',
	  'UploadedFileError' => 'vendor/base-php/inc/file.php',
	  'UnsupportedImageTypeException' => 'vendor/base-php/inc/image.php',
	  'Image' => 'vendor/base-php/inc/image.php',
	  'MIME' => 'vendor/base-php/inc/mime.php',
	  'MoneyConversionException' => 'vendor/base-php/inc/money.php',
	  'Money' => 'vendor/base-php/inc/money.php',
	  'MoneyBank' => 'vendor/base-php/inc/money.php',
	  'ISO_Country' => 'vendor/base-php/inc/iso/country.php',
	  'ISO_Language' => 'vendor/base-php/inc/iso/language.php',
	  'GDBException' => 'vendor/base-php/inc/gdb/gdb.php',
	  'GDBQueryException' => 'vendor/base-php/inc/gdb/gdb.php',
	  'GDBIntegrityConstraintViolation' => 'vendor/base-php/inc/gdb/gdb.php',
	  'GDBForeignKeyViolation' => 'vendor/base-php/inc/gdb/gdb.php',
	  'GDBUniqueViolation' => 'vendor/base-php/inc/gdb/gdb.php',
	  'GDBCheckViolation' => 'vendor/base-php/inc/gdb/gdb.php',
	  'GDB' => 'vendor/base-php/inc/gdb/gdb.php',
	  'GDBMySQL' => 'vendor/base-php/inc/gdb/gdb.php',
	  'GDBResult' => 'vendor/base-php/inc/gdb/gdb.php',
	  'GDBResultMySQL' => 'vendor/base-php/inc/gdb/gdb.php',
	  'gdb\\Migration' => 'vendor/base-php/inc/gdb/migration.php',
	  'gdb\\SchemaBuilder' => 'vendor/base-php/inc/gdb/schema_builder.php',
	  'gdb\\TableDefinition' => 'vendor/base-php/inc/gdb/table_definition.php',
	  'zing\\DoublePerformException' => 'framework/classes/controller.php',
	  'zing\\Controller' => 'framework/classes/controller.php',
	  'zing\\http\\Constants' => 'framework/classes/http.php',
	  'zing\\http\\Exception' => 'framework/classes/http.php',
	  'zing\\http\\Headers' => 'framework/classes/http.php',
	  'zing\\http\\Request' => 'framework/classes/http.php',
	  'zing\\http\\AbstractResponse' => 'framework/classes/http.php',
	  'zing\\http\\Response' => 'framework/classes/http.php',
	  'zing\\http\\FileResponse' => 'framework/classes/http.php',
	  'zing\\routing\\RoutingException' => 'framework/classes/routing.php',
	  'zing\\routing\\DuplicateRouteException' => 'framework/classes/routing.php',
	  'zing\\routing\\Router' => 'framework/classes/routing.php',
	  'zing\\view\\MissingViewException' => 'framework/classes/view.php',
	  'zing\\view\\Base' => 'framework/classes/view.php',
	  'zing\\view\\PHPHandler' => 'framework/classes/view.php',
	  'zing\\sys\\Config' => 'framework/classes/sys/config.php',
	  'zing\\sys\\JS' => 'framework/classes/sys/js.php',
	  'zing\\plugin\\Dependency' => 'framework/classes/plugin/dependency.php',
	  'zing\\plugin\\Locator' => 'framework/classes/plugin/locator.php',
	  'zing\\plugin\\Manager' => 'framework/classes/plugin/manager.php',
	  'zing\\plugin\\PluginException' => 'framework/classes/plugin/plugin.php',
	  'zing\\plugin\\Plugin' => 'framework/classes/plugin/plugin.php',
	  'zing\\plugin\\PluginStub' => 'framework/classes/plugin/plugin_stub.php',
	  'gdb\\lang\\OptionParser' => 'framework/classes/lang/options.php',
	  'zing\\lang\\Reflection' => 'framework/classes/lang/reflection.php',
	  'zing\\helpers\\DebugHelper' => 'framework/classes/helpers/common.php',
	  'zing\\helpers\\HTMLHelper' => 'framework/classes/helpers/common.php',
	  'zing\\helpers\\FormHelper' => 'framework/classes/helpers/common.php',
	  'zing\\helpers\\AssetHelper' => 'framework/classes/helpers/common.php',
	  'zing\\db\\Migrator' => 'framework/classes/db/migration.php',
	  'zing\\db\\MigrationLocator' => 'framework/classes/db/migration.php',
	  'zing\\db\\Migration' => 'framework/classes/db/migration.php',
	  'TestController' => 'app/controllers/test_controller.php',
	);
	// SUPERLOAD-END
    
    if (isset($map[$class])) {
        require ZING_ROOT . '/' . $map[$class];
    }
    
}
?>