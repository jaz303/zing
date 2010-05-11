<?php
namespace zing\plugin;

class Dependency
{
    /**
     *
     *
     * @param $plugins array of Plugin instances for which to resolve load order
     * @return array of Plugin instances, in an order suitable for loading
     * @throws UnsatisfiedDependencyException if a dependency is not present
     * @throws CyclicDependencyException if two plugins report that they must be
     *         loaded before each other
     */
    public static function resolve_load_order(array $plugins) {
        
    }
    
    public function __construct($id, $version_operator = null, $version = null) {
        
    }
    
    public function get_name() {
        
    }
    
    public function get_version_operator() {
        
    }
    
    public function get_version() {
        
    }
    
    public function should_load_before() {
        
    }
}
?>