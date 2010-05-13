<?php
namespace zing\plugin;

abstract class BlankPlugin implements Plugin
{
    public function title() {
        return $this->id();
    }
    
    public function attribution() {
        return array();
    }
    
    public function dependencies() {
        return array();
    }
    
    public function has($thing) {
        return method_exists($this, "get_exported_$thing");
    }
    
    public function post_install() {}
}
?>