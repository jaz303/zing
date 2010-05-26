<?php
namespace zing\generator;

class GeneratorNotFoundException extends \Exception {}

class Manager
{
    public static function create_with_default_locator() {
        global $_ZING;
        $locator_class = $_ZING['zing.generator.locator'];
        return new self(new $locator_class);
    }
    
    private $generators = array();
    
    public function __construct($locator) {
        $this->generators = $locator->locate_generators();
    }
    
    public function directory() {
        $directory = array();
        foreach (array_keys($this->generators) as $generator_name) {
            $directory[$generator_name] = $this->create($generator_name)->description();
        }
        ksort($directory);
        return $directory;
    }
    
    public function create($name) {
        
        if (!isset($this->generators[$name])) {
            throw new GeneratorNotFoundException;
        }
        
        $generator_file     = $this->generators[$name]['file'];
        $generator_class    = $this->generators[$name]['class'];
        
        require_once $generator_file;
        
        return new $generator_class(dirname($generator_file));
    
    }
}
?>