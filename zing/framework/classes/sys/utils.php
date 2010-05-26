<?php
namespace zing\sys;

class Utils
{
    public static function invoke_task($task) {
        $cmd = "./script/phake $task";
        return shell_exec($cmd);
    }
}
?>