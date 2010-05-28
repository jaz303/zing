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
        
        $file_stem = trim(str_replace('\\', '/', $controller_path), '/');
        
        $this->controller_file  = 'app/controllers/' . $file_stem . '_controller.php';
        $this->helper_file      = 'app/helpers/' . $file_stem . '_helper.php';
        $this->view_dir         = 'app/views/' . $file_stem . '/';
        
        $this->class_prefix = \Inflector::camelize($controller_name);
        
        if ($namespace) {
            $this->namespace_declaration = "namespace $namespace;";
        } else {
            $this->namespace_declaration = "";
        }
    }
    
    protected function manifest() {
        return array(
            $this->controller_file  => $this->__directory . '/templates/controller_template.php',
            $this->helper_file      => $this->__directory . '/templates/helper_template.php',
            $this->view_dir         => true
        );
    }
    
    protected function tasks() {
        return array('core:regenerate_autoload_map');
    }
}
?>