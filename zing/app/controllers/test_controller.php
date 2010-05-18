<?php
class TestController extends zing\Controller
{
    protected static $filters = array(
        'before' => array(
            'before' => array('except' => 'foo')
        )
    );
    
    protected function before() {
        $this->layout('application');
    }
        
    public function _index() {}
}
?>