<?php
namespace zing\server\adapters;

class UnknownAdapterException extends \Exception {}

abstract class AbstractAdapter
{
    public static $REGISTRY = array(
        'lighttpd'          => 'zing\server\adapters\LighttpdAdapter'
    );
    
    public static function for_name($name) {
        if (isset(self::$REGISTRY[$name])) {
            return self::$REGISTRY[$name];
        } else {
            throw new UnknownAdapterException();
        }
    }
    
    private $port = 3000;
    
    public function get_port() { return $this->port; }
    public function set_port($p) { $this->port = (int) $p; }
    
    public abstract function get_server_string();
}
?>