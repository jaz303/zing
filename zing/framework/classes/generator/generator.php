<?php
namespace zing\generator;

abstract class Generator
{
    protected $directory;
    
    /**
     * @param $directory root directory for this generator
     */
    public function __construct($directory) {
        $this->directory = $directory;
    }
    
    public function generate(array $args) {
        
    }
    
    public function description() {
        return "(untitled generator)";
    }
}
?>