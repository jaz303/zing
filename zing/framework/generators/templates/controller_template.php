<?php
<%= $namespace_declaration ? ($namespace_declaration . "\n\n") : "" %>
class <%= $class_prefix %>Controller extends \zing\Controller
{
    protected static $filters = array(
    );
    
    protected function init() {
        parent::init();
        $this->helper('<%= $class_prefix %>Helper');
    }
    
    public function _index() {
        $this->render('html', '<h1>Hello World!</h1>');
    }
}
?>