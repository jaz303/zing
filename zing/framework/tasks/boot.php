<?php
// This file boots the Zing! task system by finding:
// 1. framework tasks
// 2. application tasks
// 3. plugin tasks
//
// It's all a bit ad-hoc but we don't want require boot.php unnecessarily

$stack = array(
    dirname(__FILE__),
    dirname(__FILE__) . '/../../app/tasks'
);

$plugins_dir = dirname(__FILE__) . '/../../vendor/plugins';
foreach (glob($plugins_dir . '/*', GLOB_ONLYDIR) as $plugin) {
    if (is_dir($plugin . '/tasks')) {
        $stack[] = $plugin;
    }
}

// TODO: extract this recursive globbing crap
while (count($stack)) {
    $directory = array_pop($stack);
    $handle = opendir($directory);
    while (($file = readdir($handle)) !== false) {
        if ($file == '.' || $file == '..') continue;
        $abs_path = $directory . '/' . $file;
        if (is_file($abs_path) && preg_match('/\.phake$/', $abs_path)) {
            require $abs_path;
        } else if (is_dir($abs_path)) {
            $stack[] = $abs_path;
        }
    }
    closedir($handle);
}
?>