<?php
namespace zing\router;

class Router
{
    public function map($name, $path, $options = array()) {
        
        $path = explode('/', $path);
        
        
    }
    
    public function compile() {
        
    }
}

class Node
{
    private $children = array();
    
    public function add_child() {
        
    }
}

class StaticNode extends Node
{
    private $segment;
    
    public function __construct($segment) {
        $this->segment = $segment;
    }
}

class DynamicNode extends Node
{

?>