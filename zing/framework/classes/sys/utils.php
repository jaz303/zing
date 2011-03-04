<?php
namespace zing\sys;

class Utils
{
    public static function invoke_task($task) {
        $cmd = "cd " . ZING_ROOT . " && ./script/phake $task";
        return shell_exec($cmd);
    }
    
    public static function regenerate_autoload_map() {
        return self::invoke_task('core:regenerate_autoload_map');
    }
}
?>