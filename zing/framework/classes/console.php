<?php
namespace zing;

class Console
{
    public static function write($line) { self::out($line . "\n"); }
    public static function error($line) { self::err($line . "\n"); }
    
    public static function exception(\Exception $e) {
        self::error("Error: {$e->getMessage()} (" . get_class($e) . ")");
        self::error($e->getTraceAsString());
    }
    
    public static function out($line) {
        if (is_object($line)) $line = $line->__toString();
        fwrite(STDOUT, $line);
    }
    
    public static function err($line) {
        if (is_object($line)) $line = $line->__toString();
        fwrite(STDERR, $line);
    }
}
?>