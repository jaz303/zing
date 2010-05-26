<?php
<%= $controller_namespace_declaration ? ($controller_namespace_declaration . "\n\n") : "" %>
class <%= $controller_class_prefix %>Controller extends \zing\Controller
{
    public function _index() {
        $this->render('html', '<h1>Hello World!</h1>');
    }
}
?>