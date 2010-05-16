<?php
namespace zing\view;

class View
{
    private $helpers            = array();
    private $assigns            = array();
    
    public function set_controller($controller) {
        
        foreach ($controller->get_helpers() as $h) {
            $this->add_helper($h);
        }
        
        foreach ($controller->get_assigns() as $k => $v) {
            $this->set($k, $v);
        }
    
    }
    
    public function add_helper($class) {
        array_unshift($this->helpers, $class);
    }
    
    public function clear_helpers() {
        $this->helpers = array();
    }
    
    public function set($k, $v) {
        $this->assigns[$k] = $v;
    }
    
    public function merge(array $assigns) {
        $this->assigns = array_merge($this->assigns, $assigns);
    }
    
    /**
     * Render a template and return the output.
     *
     * It's important that the template path is absolute to avoid confusing the
     * template compiler, which bases the cached file name on the template's
     * path.
     *
     * @param $__template__ absolute path of template to render
     * @return rendered template
     */
    public function render_file($__template__) {
        $__compiled_template__ = $this->compile($__template__);
        extract($this->assigns);
        ob_start();
        require $__compiled_template__;
        return ob_get_clean();
    }
    
    private function compile($source_file) {
        
        $file_token = $source_file;
        foreach ($this->helpers as $helper_class) {
            $file_token .= ":$helper_class";
        }
        $file_token = sha1($file_token);
        
        $target_file = ZING_COMPILED_DIR . '/zing.view/' . $file_token . '.php';
        
        if ($this->is_compilation_required($source_file, $target_file)) {
            $this->perform_compile($source_file, $target_file);
        }
        
        return $target_file;
        
    }
    
    private function is_compilation_required($source_file, $target_file) {
        
        if (!file_exists($target_file)) {
            return true;
        }
        
        global $_ZING;
        
        if (!$_ZING['config.zing.view.recompile']) {
            return false;
        } elseif ($_ZING['config.zing.view.recompile'] === 'mtime') {
            return filemtime($source_file) > filemtime($target_file);
        } else {
            return true;
        }
    
    }
    
    private function perform_compile($source_file, $target_file) {
        $target_dir = dirname($target_file);
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        file_put_contents($target_file, $this->rewrite_source(file_get_contents($source_file)));
    }
    
    //
    // Compilation gubbins
    
    private function rewrite_source($source) {
        $this->reset($source);
        $skip = false;
        while (($tok = $this->next()) !== null) {
            if ($skip) {
                $out .= $this->token_string($tok);
                if (!$this->is_space($tok)) $skip = false;
            } elseif (is_string($tok)) {
                if ($this->is_wipeout($tok)) $skip = true;
                $out .= $tok;
            } elseif ($tok[0] == T_STRING) {
                $func = $tok[1];
                $buff = '';
                while (($tok = $this->next()) && $this->is_space($tok)) {
                    $buff .= $tok[1];
                }
                if ($tok == '(') {
                    $out .= $this->rewrite_function_call($func) . $buff . '(';
                } else {
                    if ($this->is_wipeout($tok)) $skip = true;
                    $out .= $func . $buff . $this->token_string($tok);
                }
            } else {
                if ($this->is_wipeout($tok)) $skip = true;
                $out .= $tok[1];
            }
        }
        return $out;
    }
    
    private function reset($source) {
        $this->tokens   = token_get_all($source);
        $this->index    = 0;
    }
    
    private function next() {
        if ($this->index < count($this->tokens)) {
            return $this->tokens[$this->index++];
        } else {
            return null;
        }
    }
    
    private function is_wipeout($tok) {
        return is_array($tok) && $tok[0] == T_PAAMAYIM_NEKUDOTAYIM;
    }
    
    private function is_space($tok) {
        return is_array($tok) && $tok[0] == T_WHITESPACE;
    }
    
    private function token_string($t) {
        return is_array($t) ? $t[1] : $t;
    }
    
    private function rewrite_function_call($function) {
        foreach ($this->helpers as $helper_class) {
            if ($helper_class[0] == '\\') {
                $helper_class = substr($helper_class, 1);
            }
            if (class_exists($helper_class, true)) {
                $class = new \ReflectionClass($helper_class);
                if ($class->hasMethod($function) &&
                    $class->getMethod($function)->isStatic() &&
                    $class->getMethod($function)->isPublic()) {
                    return "{$helper_class}::{$function}";
                }
            }
        }
        return $function;
    }
}
?>