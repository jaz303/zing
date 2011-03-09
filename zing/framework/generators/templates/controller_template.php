<?php
<%= $namespace_declaration ? ($namespace_declaration . "\n\n") : "" %>
class <%= $class_prefix %>Controller extends \zing\Controller
{
    protected static $filters = array(
    );
    
    protected function init() {
        parent::init();
        $this->helper('<%= $namespace_prefix %><%= $class_prefix %>Helper');
    }
<% foreach ($this->actions as $action) { %>
    
    public function _<%= $action['name'] %>() {
        // implementation of '<%= $action['name'] %>' action goes here
    }
<% } %>
}
?>