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

require dirname(__FILE__) . '/autoload.php';

// {begin:zing.cms.asset-path}
$GLOBALS['_ZING']['zing.cms.asset_path'] = ZING_DATA_DIR . '/cms/assets';
// {end:zing.cms.asset-path}

class RandomActs
{
    public static $ARTICLE_TYPES = array(
        1 => 'Article Type 1',
        2 => 'Article Type 2',
        3 => 'Article Type 3'
    );
}
?>