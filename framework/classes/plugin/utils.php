<?php
namespace zing\plugin;

class Utils
{
    public static function declared_plugin_classes() {
        return \zing\lang\Reflection::concrete_descendants_of('\zing\plugin\Plugin');
    }
    
    public static function is_plugin($plugin_path) {
        return self::initialiser_for_path($plugin_path) !== null;
    }
    
    public static function initialiser_for_path($plugin_path) {
        $candidate = $plugin_path . '/plugin.php';
        return is_file($candidate) ? $candidate : null;
    }
}
?>