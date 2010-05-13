<?php
namespace zing\sys;

class Config
{
    public static function add_class_path($class_path) {
        $file = ZING_CONFIG_DIR . '/CLASS_PATHS';
        $contents = file_get_contents($file);
        
        if (substr($contents, -1) != "\n") {
            $contents .= "\n";
        }
        
        $contents .= $class_path;
        $contents .= "\n";
        
        file_put_contents($file, $contents);
    }
}
?>