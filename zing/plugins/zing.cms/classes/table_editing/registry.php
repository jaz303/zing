<?php
namespace zing\cms\table_editing;

class Registry
{
    private $editable_tables;
    
    public function __construct() {
        $this->editable_tables = &$GLOBALS['_ZING']['zing.cms.tables'];
    }
  
    public function editable_tables() {
        return $this->editable_tables;
    }
    
    public function is_valid_table($table) {
        return isset($this->editable_tables[$table]);
    }
}
?>