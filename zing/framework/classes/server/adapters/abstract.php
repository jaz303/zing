<?php
namespace zing\server\adapters;

abstract class AbstractAdapter
{
    private $port = 3000;
    
    public function get_port() { return $this->port; }
    public function set_port($p) { $this->port = (int) $p; }
    
    public abstract function get_server_string();
}
?>