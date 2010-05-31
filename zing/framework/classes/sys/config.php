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
        
        $contents .= \zing\FileUtils::relativize_path($class_path, ZING_ROOT);
        $contents .= "\n";
        
        file_put_contents($file, $contents);
    }
    
    public static function write_application_config_code($code, $config_file = 'main') {
        
        $path = ZING_CONFIG_DIR . '/app/' . $config_file . '.php';
        
        $source  = file_get_contents($path);
        $source  = trim(preg_replace('|\?\>\s*$|m', '', $source));
        $source .= "\n\n" . trim($code) . "\n?>";
        
        file_put_contents($path, $source);
        
    }
    
    public static function write_application_config_assignment($variable, $value, $config_file = 'main') {
        self::write_application_config_code("$variable = " . var_export($value, true) . ";\n", $config_file);
    }
    
    public static function add_stylesheet_to_collection($collection, $stylesheet) {
        self::write_application_config_assignment(
            '\\zing\\view\\Base::$stylesheet_collections[]',
            $stylesheet
        );
    }
}
?>