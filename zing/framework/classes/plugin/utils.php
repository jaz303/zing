<?php
namespace zing\plugin;

class Utils
{
    public static function is_plugin($dir) {
        return file_exists($dir . '/zing/plugin.php') && file_exists($dir . '/zing/plugin.json');
    }
    
    public static function is_valid_plugin_id($id) {
        return (bool) preg_match('/^[a-z][a-z0-9_-]*(\.[a-z][a-z0-9_-]*)*$/', $id);
    }
}
?>