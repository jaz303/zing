<?php
namespace zing\reporter;

class ConsoleReporter
{
    private static $colors = array(
        'red'       => "\033[31m",
        'green'     => "\033[32m",
        'blue'      => "\033[34m",
        'yellow'    => "\033[33m",
        'default'   => "\033[0m"
    );
    
    private $use_color  = true;
    private $indent     = 0;
    
    public function in() { $this->indent++; }
    public function out() { $this->indent--; }
    
    public function info($message, $new_line = true) { $this->write($message, $new_line); }
    public function success($message, $new_line = true) { $this->write($message, $new_line, 'green'); }
    public function warning($message, $new_line = true) { $this->write($message, $new_line, 'yellow'); }
    public function error($message, $new_line = true) { $this->write($message, $new_line, 'red'); }
    public function fatal($message, $new_line = true) { $this->write($message, $new_line, 'red'); }
    
    public function ok() { $this->write(' [OK]', true, 'green', 0); }
    public function fail() { $this->write(' [FAILED]', true, 'red', 0); }
    
    private function write($string, $new_line, $color = 'default', $indent = null) {
        if ($indent === null) $indent = $this->indent;
        foreach (explode("\n", trim($string, "\n")) as $line) {
            echo str_repeat('  ', $indent);
            if ($this->use_color) {
    	        echo self::$colors[$color] . $line . self::$colors['default'];
    	    } else {
    	        echo $line;
    	    }
    	    if ($new_line) echo "\n";
        }
	}
}
?>