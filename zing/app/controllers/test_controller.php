<?php
class TestController extends ApplicationController
{
    protected static $filters = array(
        'before' => array(
            'before' => array('except' => 'foo')
        )
    );
    
    protected function before() {
        $this->layout('application');
    }
        
    public function _index() {
        $this->errors = new \Errors;
        $this->errors->add('foo', 'is not a bar');
        $this->errors->add_to_base('blah blah blah');
    }
}
?>
