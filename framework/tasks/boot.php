<?php
$stack = array(
    dirname(__FILE__),
    dirname(__FILE__) . '/../../tasks'
);

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