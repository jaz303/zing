<?php
namespace zing\generator;

class GeneratorNotFoundException extends \Exception {}

class Manager
{
    //
    // Singleton
    
    private static $instance = null;
    
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }
    
    //
    //
    
    private $generators = null;
    
    public function __construct() {
        $this->locate_generators();
    }
    
    public function get_generator($name) {
        if (!isset($this->generators[$name])) {
            throw new GeneratorNotFoundException;
        } else {
            return $this->generators[$name];
        }
    }
    
    public function generators() {
        return $this->generators;
    }

    private function locate_generators() {
        if ($this->generators === null) {
            $this->generators = array();
            foreach ($GLOBALS['_ZING']['zing.generator.locators'] as $locator_class) {
                $locator = new $locator_class;
                foreach ($locator->locate_generators() as $generator) {
                    require_once $generator['file'];
                    $generator_class = $generator['class'];
                    $instance = new $generator_class;
                    $instance->set_directory(dirname($generator['file']));
                    $instance->set_name($generator['name']);
                    $this->generators[$instance->name()] = $instance;
                }
            }
        }
    }
}
?>