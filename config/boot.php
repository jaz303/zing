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

// Config array.
$_ZING = array();

define('ZING_VERSION',      '0.0.1');
define('ZING_SIGNATURE',    'Zing! Framework v' . ZING_VERSION);

define('ZING_CONFIG_DIR',   dirname(__FILE__));
define('ZING_ENV_DIR',      ZING_CONFIG_DIR . '/environments');
define('ZING_ROOT',         dirname(ZING_CONFIG_DIR));
define('ZING_PUBLIC_DIR',   ZING_ROOT . '/public');
define('ZING_TMP_DIR',      ZING_ROOT . '/tmp');
define('ZING_APP_DIR',      ZING_ROOT . '/app');
define('ZING_VIEW_DIR',     ZING_APP_DIR . '/views');
define('ZING_CACHE_DIR',    ZING_TMP_DIR . '/cache');
define('ZING_COMPILED_DIR', ZING_TMP_DIR . '/compiled');
define('ZING_VENDOR_DIR',   ZING_ROOT . '/vendor');
define('ZING_PLUGIN_DIR',   ZING_VENDOR_DIR . '/plugins');

set_include_path('.:' . ZING_ROOT);

zing_lib('common');
zing_load_environment('main');

//
// Essentials!

function zing_lib($library) {
    require ZING_ROOT . "/framework/lib/{$library}.php";
}

function zing_environments() {
    return array_map('basename', glob(ZING_ENV_DIR . '/*', GLOB_ONLYDIR));
}

function zing_load_environment($name, $env = null) {
    $ENV = array();
    require ZING_ENV_DIR . '/' . ($env === null ? ZING_ENV : $env) . '/' . $name . '.php';
    global $_ZING;
    foreach ($ENV as $k => $v) $_ZING[$k] = $v;
}

function zing_export_environment($name, $env = null) {
    $ENV = array();
    require ZING_ENV_DIR . '/' . ($env === null ? ZING_ENV : $env) . '/' . $name . '.php';
    return $ENV;
}

// set_exception_handler("pym_exception_handler");
// function pym_exception_handler($e) {
//  if (DEBUG) {
//      display_template(":exception_handler", array('exception' => $e));
//  } else {
//      echo "Application Error";
//      log_exception($e);
//  }
// }

//
// Bail out if we're running from the console

if (php_sapi_name() == 'cli') {
    echo "Zing! Console initialised, environment: " . ZING_ENV . "\n";
    return;
}

//
// Everything under here is web-only...

// set_exception_handler('my_exception_handler');

//
// The autoloader is auto-generated by the 'core:regenerate_autoload_map' task
// (or you can use 'core:kick', which updates all cached stuff)

function __autoload($class) {

    // SUPERLOAD-BEGIN
	static $map = array (
	  'IllegalArgumentException' => 'vendor/base-php/inc/base.php',
	  'IllegalStateException' => 'vendor/base-php/inc/base.php',
	  'IOException' => 'vendor/base-php/inc/base.php',
	  'NotFoundException' => 'vendor/base-php/inc/base.php',
	  'SecurityException' => 'vendor/base-php/inc/base.php',
	  'SyntaxException' => 'vendor/base-php/inc/base.php',
	  'UnsupportedOperationException' => 'vendor/base-php/inc/base.php',
	  'Base' => 'vendor/base-php/inc/base.php',
	  'Callback' => 'vendor/base-php/inc/base.php',
	  'FunctionCallback' => 'vendor/base-php/inc/base.php',
	  'InstanceCallback' => 'vendor/base-php/inc/base.php',
	  'StaticCallback' => 'vendor/base-php/inc/base.php',
	  'Inflector' => 'vendor/base-php/inc/base.php',
	  'Date' => 'vendor/base-php/inc/date.php',
	  'Date_Time' => 'vendor/base-php/inc/date.php',
	  'File' => 'vendor/base-php/inc/file.php',
	  'UploadedFile' => 'vendor/base-php/inc/file.php',
	  'UploadedFileError' => 'vendor/base-php/inc/file.php',
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
	  'zing\\Controller' => 'framework/classes/controller.php',
	  'zing\\http\\Constants' => 'framework/classes/http.php',
	  'zing\\http\\Exception' => 'framework/classes/http.php',
	  'zing\\http\\Headers' => 'framework/classes/http.php',
	  'zing\\http\\Request' => 'framework/classes/http.php',
	  'zing\\http\\Response' => 'framework/classes/http.php',
	  'zing\\routing\\DuplicateRouteException' => 'framework/classes/routing.php',
	  'zing\\routing\\Router' => 'framework/classes/routing.php',
	  'zing\\view\\View' => 'framework/classes/view.php',
	  'zing\\plugin\\BlankPlugin' => 'framework/classes/plugin/blank_plugin.php',
	  'zing\\plugin\\Dependency' => 'framework/classes/plugin/dependency.php',
	  'zing\\plugin\\Initialiser' => 'framework/classes/plugin/initialiser.php',
	  'zing\\plugin\\Locator' => 'framework/classes/plugin/locator.php',
	  'zing\\plugin\\Plugin' => 'framework/classes/plugin/plugin.php',
	  'zing\\plugin\\PluginStub' => 'framework/classes/plugin/plugin_stub.php',
	  'zing\\plugin\\Utils' => 'framework/classes/plugin/utils.php',
	  'zing\\lang\\Reflection' => 'framework/classes/lang/reflection.php',
	  'zing\\helpers\\HTMLHelper' => 'framework/classes/helpers/common.php',
	  'zing\\helpers\\FormHelper' => 'framework/classes/helpers/common.php',
	  'zing\\helpers\\AssetHelper' => 'framework/classes/helpers/common.php',
	  'TestController' => 'app/TestController.php',
	);
	// SUPERLOAD-END
    
    if (isset($map[$class])) {
        require $map[$class];
    }
    
}
?>