<?php
//
// Main application configuration file
//
// This file *must* set ZING_ENV to the correct environment, as well as the
// default timezone.

// Customise this code block to automatically detect the operating environment.
// I usually use a $_SERVER key (set via Apache's SetEnv) or the server port
// (e.g. anything other than port 80 is development).
if (isset($_SERVER['ZING_ENV'])) {
    define('ZING_ENV', $_SERVER['ZING_ENV']);
} else {
    define('ZING_ENV', 'development');
}

date_default_timezone_set('Europe/London');
?>