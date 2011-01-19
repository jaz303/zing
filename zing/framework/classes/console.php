<?php
namespace zing;

class Console
{
    public static function write($line) { self::out($line . "\n"); }
    public static function error($line) { self::err($line . "\n"); }
    
    public static function out($line) { fwrite(STDOUT, $line); }
    public static function err($line) { fwrite(STDERR, $line); }
}
?>