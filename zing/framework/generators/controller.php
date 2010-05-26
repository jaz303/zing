<?php
namespace zing\generator;

class ControllerGenerator extends \zing\generator\Generator
{
    public function description() {
        return "create a new controller extending zing\\Controller";
    }
    
    protected function parse_args(array $args) {
        if (count($args) != 1) {
            throw new \InvalidArgumentException("Usage: script/generate controller controller_name");
        }
        
        $controller_path    = str_replace('.', '\\', $args[0]);
        $controller_parts   = explode('\\', $controller_path);
        $controller_name    = array_pop($controller_parts);
        $namespace          = implode('\\', $controller_parts);
        
        $this->file = 'app/controllers/' . trim(str_replace('\\', '/', $controller_path), '/') . '_controller.php';
        $this->controller_class_prefix = \Inflector::camelize($controller_name);
        
        if ($namespace) {
            $this->controller_namespace_declaration = "namespace $namespace;";
        } else {
            $this->controller_namespace_declaration = "";
        }
    }
    
    protected function manifest() {
        return array(
            $this->file => $this->__directory . '/templates/controller_template.php'
        );
    }
    
    protected function tasks() {
        return array('core:regenerate_autoload_map');
    }
}
?>