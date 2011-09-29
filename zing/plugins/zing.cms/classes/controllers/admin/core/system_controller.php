<?php
namespace admin\core;

class SystemController extends \zing\cms\admin\BaseController
{
    public function section_path() { return 'core.system'; }
  
    public function _index() {
    }
    
    public function _database() {
        $this->usage = \GDB::instance()->get_usage();
    }
}
?>