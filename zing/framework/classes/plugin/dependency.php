<?php
namespace zing\plugin;

class Dependency
{
    private $string;
    private $plugin_id;
    private $dependency;
    
    /**
     * @param $string e.g. "zing.cms >=1.0.0 <1.1.0"
     */
    public function __construct($string) {
        $this->string = trim($string);
        $chunks = preg_split('/\s+/', $this->string);
        $this->plugin_id = array_shift($chunks);
        if (!Utils::is_valid_plugin_id($this->plugin_id)) {
            throw new \InvalidArgumentException("{$this->plugin_id} is not a valid plugin ID");
        }
        
        $this->dependency = new \zing\dependency\Dependency(implode(' ', $chunks));
    }
    
    public function toString() {
        return $this->plugin_id . ' ' . $this->dependency->toString();
    }
}
?>