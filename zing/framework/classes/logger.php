<?php
namespace zing;

class Logger
{
    const DEBUG     = 1;
    const INFO      = 2;
    const MESSAGE   = 3;
    const NOTICE    = 4;
    const WARNING   = 5;
    const ERROR     = 6;
    const FATAL     = 7;
    
    private static $instance = null;
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private $file;
    private $fd;
    private $threshold;
    
    public function __construct($file_or_resource = "php://stderr", $threshold = self::DEBUG) {
        if (is_resource($file_or_resource)) {
            $this->file = null;
            $this->td   = $file_or_resource;
        } else {
            $this->file = $file_or_resource;
            $this->fd   = fopen($file_or_resource, "w");
        }
    }
    
    public function log($prefix, $thing) {
        $out = date('Y-m-d\TH:i:s') . ' [' . $prefix . '] ';
        if ($thing instanceof \Exception) {
            $msg  = "Exception caught: " . $thing->getMessage() . ' (' . get_class($thing) . ")\n";
            $msg .= "{$thing->getFile()}:{$thing->getLine()}\n";
            $msg .= $thing->getTraceAsString();
        } else {
            $msg = $thing;
        }
        
        $out .=  str_replace("\n", "\n> ", rtrim($msg)) . "\n";
        fwrite($this->fd, $out);
    }
    
    public function debug($msg, $arg1 = null) {
        if ($this->threshold < self::DEBUG) {
            if ($arg1 !== null) $msg = call_user_func_array('sprintf', func_get_args());
            $this->log("DEBUG", $msg);
        }
    }
    
    public function info($msg, $arg1 = null) {
        if ($this->threshold < self::INFO) {
            if ($arg1 !== null) $msg = call_user_func_array('sprintf', func_get_args());
            $this->log("INFO", $msg);
        }
    }
    
    public function message($msg, $arg1 = null) {
        if ($this->threshold < self::MESSAGE) {
            if ($arg1 !== null) $msg = call_user_func_array('sprintf', func_get_args());
            $this->log("MSG", $msg);
        }
    }
    
    public function notice($msg, $arg1 = null) {
        if ($this->threshold < self::NOTICE) {
            if ($arg1 !== null) $msg = call_user_func_array('sprintf', func_get_args());
            $this->log("NOTICE", $msg);
        }
    }
    
    public function warning($msg, $arg1 = null) {
        if ($this->threshold < self::WARNING) {
            if ($arg1 !== null) $msg = call_user_func_array('sprintf', func_get_args());
            $this->log("WARNING", $msg);
        }
    }
    
    public function error($msg, $arg1 = null) {
        if ($this->threshold < self::ERROR) {
            if ($arg1 !== null) $msg = call_user_func_array('sprintf', func_get_args());
            $this->log("ERROR", $msg);
        }
    }
    
    public function fatal($msg, $arg1 = null) {
        if ($this->threshold < self::FATAL) {
            if ($arg1 !== null) $msg = call_user_func_array('sprintf', func_get_args());
            $this->log("FATAL", $msg);
        }
    }
}
?>