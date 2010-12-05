<?php
class TestController extends ApplicationController
{
    protected static $filters = array(
        'before' => array(
            'before' => array('except' => 'foo')
        )
    );
    
    protected function before() {
        \zing_load_config('zing.cms.admin');
        $this->layout('admin/main');
        $this->helper('\\zing\\cms\\helpers\\AdminHelper');
        $this->title = "Test Title";
        $this->subtitle = "Test Subtitle";
        $this->admin_structure = \zing\cms\admin\Structure::instance();
    }
        
    public function _index() {
        $this->errors = new \Errors;
        $this->errors->add('foo', 'is not a bar');
        $this->errors->add_to_base('blah blah blah');
    }
}
?>
