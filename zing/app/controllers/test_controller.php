<?php
class TestController extends zing\Controller
{
    protected function before() {
        $this->layout('application');
    }
        
    public function _index() {}
}
?>