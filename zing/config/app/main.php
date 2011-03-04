<?php
//
// Main application configuration file
//
// This file *must* set ZING_ENV to the correct environment, as well as the
// default timezone.
//
// This file is loaded from inside a function so you must assign globals via
// the $GLOBALS array.

// Customise this code block to automatically detect the operating environment.
// I usually use a $_SERVER key (set via Apache's SetEnv) or the server port
// (e.g. anything other than port 80 is development).
if (isset($_SERVER['ZING_ENV'])) {
    define('ZING_ENV', $_SERVER['ZING_ENV']);
} else {
    define('ZING_ENV', 'development');
}

date_default_timezone_set('Europe/London');


// {begin:zing.cms.asset-path}
$GLOBALS['_ZING']['zing.cms.asset_path'] = ZING_DATA_DIR . '/cms/assets';
// {end:zing.cms.asset-path}



// {begin:zing.autoload-map}
$GLOBALS["ZING_AUTOLOAD_MAP"] = array (
  'IllegalStateException' => 'vendor/base-php/inc/base.php',
  'UnsupportedOperationException' => 'vendor/base-php/inc/base.php',
  'IOException' => 'vendor/base-php/inc/base.php',
  'NotFoundException' => 'vendor/base-php/inc/base.php',
  'NoSuchMethodException' => 'vendor/base-php/inc/base.php',
  'SecurityException' => 'vendor/base-php/inc/base.php',
  'SyntaxException' => 'vendor/base-php/inc/base.php',
  'Persistable' => 'vendor/base-php/inc/base.php',
  'Callback' => 'vendor/base-php/inc/base.php',
  'Inflector' => 'vendor/base-php/inc/base.php',
  'Blob' => 'vendor/base-php/inc/blob.php',
  'MemoryBlob' => 'vendor/base-php/inc/blob.php',
  'FileBlob' => 'vendor/base-php/inc/blob.php',
  'Date' => 'vendor/base-php/inc/date.php',
  'Date_Time' => 'vendor/base-php/inc/date.php',
  'Time' => 'vendor/base-php/inc/date.php',
  'Errors' => 'vendor/base-php/inc/errors.php',
  'AbstractFile' => 'vendor/base-php/inc/file.php',
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
  'GDBQuery' => 'vendor/base-php/inc/gdb/gdb.php',
  'GDBQueryJoin' => 'vendor/base-php/inc/gdb/gdb.php',
  'gdb\\Migration' => 'vendor/base-php/inc/gdb/migration.php',
  'gdb\\SchemaBuilder' => 'vendor/base-php/inc/gdb/schema_builder.php',
  'gdb\\TableDefinition' => 'vendor/base-php/inc/gdb/table_definition.php',
  'zing\\Console' => 'framework/classes/console.php',
  'zing\\DoublePerformException' => 'framework/classes/controller.php',
  'zing\\Controller' => 'framework/classes/controller.php',
  'zing\\Dispatcher' => 'framework/classes/dispatcher.php',
  'zing\\FileUtils' => 'framework/classes/fs.php',
  'zing\\http\\Constants' => 'framework/classes/http.php',
  'zing\\http\\Exception' => 'framework/classes/http.php',
  'zing\\http\\Headers' => 'framework/classes/http.php',
  'zing\\http\\Request' => 'framework/classes/http.php',
  'zing\\http\\Query' => 'framework/classes/http.php',
  'zing\\http\\AbstractResponse' => 'framework/classes/http.php',
  'zing\\http\\Response' => 'framework/classes/http.php',
  'zing\\http\\FileResponse' => 'framework/classes/http.php',
  'zing\\http\\Session' => 'framework/classes/http.php',
  'zing\\http\\LazySession' => 'framework/classes/http.php',
  'zing\\routing\\RoutingException' => 'framework/classes/routing.php',
  'zing\\routing\\DuplicateRouteException' => 'framework/classes/routing.php',
  'zing\\routing\\Router' => 'framework/classes/routing.php',
  'zing\\view\\MissingViewException' => 'framework/classes/view.php',
  'zing\\view\\Base' => 'framework/classes/view.php',
  'zing\\view\\PHPHandler' => 'framework/classes/view.php',
  'zing\\sys\\AutoloadMapper' => 'framework/classes/sys/autoload_mapper.php',
  'zing\\sys\\Config' => 'framework/classes/sys/config.php',
  'zing\\sys\\CSS' => 'framework/classes/sys/css.php',
  'zing\\sys\\JS' => 'framework/classes/sys/js.php',
  'zing\\sys\\OS' => 'framework/classes/sys/os.php',
  'zing\\sys\\SourceBlockWriter' => 'framework/classes/sys/source_block_writer.php',
  'zing\\sys\\Utils' => 'framework/classes/sys/utils.php',
  'zing\\server\\adapters\\UnknownAdapterException' => 'framework/classes/server/adapters/abstract.php',
  'zing\\server\\adapters\\AbstractAdapter' => 'framework/classes/server/adapters/abstract.php',
  'zing\\server\\adapters\\LighttpdAdapter' => 'framework/classes/server/adapters/lighttpd.php',
  'zing\\reporter\\ConsoleReporter' => 'framework/classes/reporter/console_reporter.php',
  'zing\\plugin\\DefaultLocator' => 'framework/classes/plugin/default_locator.php',
  'zing\\plugin\\Dependency' => 'framework/classes/plugin/dependency.php',
  'zing\\plugin\\Installer' => 'framework/classes/plugin/installer.php',
  'zing\\plugin\\PluginNotFoundException' => 'framework/classes/plugin/manager.php',
  'zing\\plugin\\DuplicatePluginException' => 'framework/classes/plugin/manager.php',
  'zing\\plugin\\Manager' => 'framework/classes/plugin/manager.php',
  'zing\\plugin\\Plugin' => 'framework/classes/plugin/plugin.php',
  'zing\\plugin\\GitSource' => 'framework/classes/plugin/sources.php',
  'zing\\plugin\\GithubSource' => 'framework/classes/plugin/sources.php',
  'zing\\plugin\\InvalidPluginException' => 'framework/classes/plugin/stub.php',
  'zing\\plugin\\Stub' => 'framework/classes/plugin/stub.php',
  'zing\\plugin\\Utils' => 'framework/classes/plugin/utils.php',
  'zing\\mail\\MailSendException' => 'framework/classes/mail/mail.php',
  'zing\\mail\\Message' => 'framework/classes/mail/mail.php',
  'zing\\mail\\Transport' => 'framework/classes/mail/mail.php',
  'zing\\mail\\MailTransport' => 'framework/classes/mail/mail.php',
  'zing\\lang\\Introspector' => 'framework/classes/lang/introspector.php',
  'zing\\lang\\OptionParser' => 'framework/classes/lang/options.php',
  'zing\\lang\\Reflection' => 'framework/classes/lang/reflection.php',
  'zing\\helpers\\DebugHelper' => 'framework/classes/helpers/common.php',
  'zing\\helpers\\HTMLHelper' => 'framework/classes/helpers/common.php',
  'zing\\helpers\\FormHelper' => 'framework/classes/helpers/common.php',
  'zing\\helpers\\AssetHelper' => 'framework/classes/helpers/common.php',
  'zing\\generator\\DefaultLocator' => 'framework/classes/generator/default_locator.php',
  'zing\\generator\\Generator' => 'framework/classes/generator/generator.php',
  'zing\\generator\\GeneratorNotFoundException' => 'framework/classes/generator/manager.php',
  'zing\\generator\\Manager' => 'framework/classes/generator/manager.php',
  'zing\\dependency\\Dependency' => 'framework/classes/dependency/dependency.php',
  'zing\\dependency\\Atom' => 'framework/classes/dependency/dependency.php',
  'zing\\dependency\\Version' => 'framework/classes/dependency/version.php',
  'zing\\db\\Migrator' => 'framework/classes/db/migration.php',
  'zing\\db\\MigrationLocator' => 'framework/classes/db/migration.php',
  'zing\\db\\Migration' => 'framework/classes/db/migration.php',
  'zing\\archive\\UnsupportedAlgorithmException' => 'framework/classes/archive/archive.php',
  'zing\\archive\\OperationFailedException' => 'framework/classes/archive/archive.php',
  'zing\\archive\\Support' => 'framework/classes/archive/archive.php',
  'zing\\archive\\ZipNative' => 'framework/classes/archive/archive.php',
  'zing\\archive\\ZipCliNix' => 'framework/classes/archive/archive.php',
  'admin\\cms\\table_editing\\ProductController' => 'app/lib/product_controller.php',
  'U' => 'app/helpers/url.php',
  'ApplicationController' => 'app/controllers/application_controller.php',
  'TestController' => 'app/controllers/test_controller.php',
  'Product' => 'app/admin_models/product.php',
  'lfo\\RecordNotFoundException' => 'plugins/jaz303.little-fat-objects/classes/lfo.php',
  'lfo\\UnknownSerializationFormat' => 'plugins/jaz303.little-fat-objects/classes/lfo.php',
  'lfo\\UnknownIndexException' => 'plugins/jaz303.little-fat-objects/classes/lfo.php',
  'lfo\\QueryFailedException' => 'plugins/jaz303.little-fat-objects/classes/lfo.php',
  'lfo\\RollbackException' => 'plugins/jaz303.little-fat-objects/classes/lfo.php',
  'lfo\\Gateway' => 'plugins/jaz303.little-fat-objects/classes/lfo.php',
  'lfo\\Query' => 'plugins/jaz303.little-fat-objects/classes/lfo.php',
  'lfo\\Result' => 'plugins/jaz303.little-fat-objects/classes/lfo.php',
  'lfo\\PHPSerializer' => 'plugins/jaz303.little-fat-objects/classes/lfo.php',
  'lfo\\Object' => 'plugins/jaz303.little-fat-objects/classes/lfo_object.php',
  'lfo\\ArrayObject' => 'plugins/jaz303.little-fat-objects/classes/lfo_object.php',
  'lfo\\OpenArrayObject' => 'plugins/jaz303.little-fat-objects/classes/lfo_object.php',
);

// {end:zing.autoload-map}
?>