<?php
namespace zing\generator;

class ControllerGenerator extends \zing\generator\Generator
{
    public function description() {
        return "Create a new controller extending zing\\Controller";
    }
    
    protected function parse_args(array $args) {
        
        if (count($args) < 1) {
            throw new \InvalidArgumentException("Usage: script/generate controller controller_name [action_list]");
        }
        
        // foo\bar\baz
        $controller_path        = str_replace('.', '\\', array_shift($args));
        
        // array('foo', 'bar', 'baz')
        $controller_parts       = explode('\\', $controller_path);
        
        // baz
        $controller_name        = array_pop($controller_parts);
        
        // foo\bar
        $namespace              = implode('\\', $controller_parts);
        
        // foo/bar/baz
        $file_stem              = trim(str_replace('\\', '/', $controller_path), '/');
        
        // Baz
        $this->class_prefix     = \Inflector::camelize($controller_name);
        
        $this->controller_file  = 'app/controllers/' . $file_stem . '_controller.php';
        $this->helper_file      = 'app/helpers/' . $file_stem . '_helper.php';
        $this->view_dir         = 'app/views/' . $file_stem . '/';
        
        if ($namespace) {
            $this->namespace_declaration = "namespace $namespace;";
            $this->namespace_prefix = "$namespace\\";
        } else {
            $this->namespace_declaration = "";
            $this->namespace_prefix = "";
        }
        
        $this->actions = array();
        foreach ($args as $action) {
            if ($p = strpos($action, '.')) {
                $this->actions[] = array(
                    'name' => substr($action, 0, $p),
                    'extension' => substr($action, $p)
                );
            } else {
                $this->actions[] = array(
                    'name' => $action,
                    'extension' => '.html.php'
                );
            }
        }
    
    }
    
    protected function manifest() {
        $manifest = array(
            $this->controller_file  => $this->relative_path('/templates/controller_template.php'),
            $this->helper_file      => $this->relative_path('/templates/helper_template.php'),
            $this->view_dir         => true
        );
        foreach ($this->actions as $action) {
            $action_path = $this->view_dir . $action['name'] . $action['extension'];
            $manifest[$action_path] = true;
        }
        return $manifest;
    }
    
    protected function tasks() {
        return array('core:regenerate_autoload_map');
    }
}
?>