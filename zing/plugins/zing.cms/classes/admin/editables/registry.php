<?php
namespace zing\cms\admin\editables;

class Registry implements \IteratorAggregate
{
    private static $instance = null;
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }
  
    private $editables;
    
    public function __construct() {
        $this->editables = &$GLOBALS['_ZING']['zing.cms.editables'];
    }
  
    public function editables() {
        return $this->editables;
    }
    
    public function is_valid_editable($table) {
        return isset($this->editables[$table]);
    }
    
    public function getIterator() {
        return new \ArrayIterator($this->editables);
    }
}
?>