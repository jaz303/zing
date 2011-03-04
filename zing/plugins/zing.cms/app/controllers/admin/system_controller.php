<?php
namespace admin;

class SystemController extends \zing\cms\admin\BaseController
{
    public function _index() {
    }
    
    public function _database() {
        $this->usage = \GDB::instance()->get_usage();
    }
}
?>