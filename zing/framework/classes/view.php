<?php
namespace zing\view;

class MissingViewException extends \Exception {}

abstract class Base
{
    protected static $active = array();
    protected static $templates = array();
    
    /**
     * Returns the current active view handler
     */
    public static function active() { return self::$active[0]; }
    
    /**
     * Returns the currently rendering template path
     */
    public static function template() { return self::$templates[0]; }
    
    //
    // Some configuration
    
    /**
     * View paths to search
     * Earlier entries in this array take precedence
     * No trailing slashes, please
     */
    public static $view_paths = array(
        ZING_VIEW_DIR
    );

    public static $stylesheet_collections = array(
        'defaults'          => array('main.css', ':jquery-plugins'),
        'jquery-plugins'    => array()
    );
    
    public static $javascript_collections = array(
        'defaults'          => array(':jquery', ':jquery-plugins', 'application.js'),
        'jquery'            => array('jquery.min.js'),
        'jquery-plugins'    => array()
    );
    
    /**
     * Returns array of absolute filenames for candidate views matching the given
     * view name, optional template type and optional handler extension.
     *
     * Returned filenames are always absolute.
     * Basename component will always be of the format view_name.template_type.handler_extension
     *
     */
    public static function candidate_views_for($view, $template_type = null, $handler_ext = null) {
        // TODO: this is an optimisation point
        // In production mode, memo the candidate views for any given input
        if ($template_type && $handler_ext) {
            $view_file = "$view.$template_type.$handler_ext";
            foreach (self::$view_paths as $vp) {
                if (file_exists("$vp/$view_file")) {
                    return array(array($vp, $view_file));
                }
            }
            return array();
        } else {
            $view_glob = $view .
                            '.' . ($template_type ? $template_type : '*') .
                            '.' . ($handler_ext ? $handler_ext : '*');
            $candidates = array();
            foreach (self::$view_paths as $vp) {
                $len = strlen($vp);
                foreach (glob("$vp/$view_glob") as $c) {
                    $candidates[] = array($vp, substr($c, $len + 1));
                }
            }
            return $candidates;
        }
    }
    
    protected static function find_first_view_for($view, $template_type = null, $handler_ext = null) {
        $candidates = self::candidate_views_for($view, $template_type, $handler_ext);
        if (count($candidates)) {
            return $candidates[0][0] . '/' . $candidates[0][1];
        } else {
            throw new MissingViewException("no suitable view found for view '$view' [template_type=$template_type,handler_ext=$handler_ext]");
        }
    }
    
    /**
     * $view_path           $relative_to            $result
     * -------------------------------------------------------------------------
     * index                admin/users             admin/users/index
     * :index               admin/users             global/index
     * index/repeater       admin/users             admin/users/index/repeater
     * /products/list       admin/users             products/list
     * ../products/list     admin/users             admin/users/../products/list
     */
    public static function resolve_relative_view_path($view_path, $relative_to) {
        if ($view_path[0] == ':') {
            return "global/" . substr($view_path, 1);
        } elseif ($view_path[0] == '/') {
            return substr($view_path, 1);
        } else {
            return $relative_to . '/' . $view_path;
        }
    }
    
    //
    //
    
    protected $template_type = null;
    
    public function set_template_type($tt) {
        $this->template_type = $tt;
    }
    
    //
    //
    
    // public abstract function render_view();
    // public abstract function render_file();
    
}

/**
 * To be compatible with PHPHandler, a controller must implement the following
 * methods:
 *
 * 1. get_assigns()     - return assoc. array of names => values for assignment to template
 * 2. get_helpers()     - return array of fully-qualified helper classnames
 * 3. get_layout()      - return name of layout to wrap rendered views
 *
 */
class PHPHandler extends Base
{
    public static $default_helpers = array(
        '\\zing\\helpers\\DebugHelper',
        '\\zing\\helpers\\HTMLHelper',
        '\\zing\\helpers\\FormHelper',
        '\\zing\\helpers\\AssetHelper'
    );
    
    private $helpers    = array();
    private $assigns    = array();
    private $layout     = null;
    private $view       = null;
    
    public function add_helper($class) { array_unshift($this->helpers, $class); }
    
    public function set($k, $v) { $this->assigns[$k] = $v; }
    public function merge(array $assigns) { $this->assigns = array_merge($this->assigns, $assigns); }
    
    public function set_layout($layout) { $this->layout = $layout; }
    
    public function __construct() {
        foreach (self::$default_helpers as $h) {
            $this->add_helper($h);
        }
    }
    
    public function import_from_controller($controller) {
        $this->helpers  = array_merge($this->helpers, $controller->get_helpers());
        $this->assigns  = array_merge($this->assigns, $controller->get_assigns());
        $this->layout   = $controller->get_layout();
    }
    
    public function render_view($view, $view_root = null) {
        
        if ($view_root === null) {
            $paths = self::candidate_views_for($view, $this->template_type, 'php');
            if (count($paths)) {
                $view = $paths[0][0];
                $view_root = $paths[0][1];
            } else {
                // TODO: error
            }
        }
        
        $file = $view_root . '/' . $view . '.' . $this->template_type . '.php';
        
        $this->view = $view;
        
        array_unshift(self::$active, $this);
        
        try {
            if ($this->layout) {
                $this->capture('layout', $this->render_file($file));
                $layout = self::find_first_view_for("layouts/{$this->layout}", $this->template_type, 'php');
                return $this->render_file($layout);
            } else {
                return $this->render_file($file);
            }
            array_shift(self::$active);
        } catch (\Exception $e) {
            array_shift(self::$active);
            throw $e;
        }
    
    }
    
    public function render_partial($partial) {
        $partial = preg_replace('/(^|\/|:)(\w+)$/', '$1_$2', $partial);
        $partial = self::resolve_relative_view_path($partial, dirname($this->view));
        $file    = self::find_first_view_for($partial, $this->template_type, 'php');
        return $this->render_file($file);
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
    public function render_file($__template__, $locals = array()) {
        array_unshift(self::$templates, $__template__);
        try {
            $__compiled_template__ = $this->compile($__template__);
            extract($this->assigns);
            ob_start();
            require $__compiled_template__;
            return ob_get_clean();
            array_unshift(self::$templates);
        } catch (\Exception $e) {
            array_unshift(self::$templates);
            throw $e;
        }
    }
    
    //
    // Captures
    
    private $captures           = array();
    private $active_captures    = array();
    
    public function content_for($block_name) {
        return isset($this->captures[$block_name]) ? $this->captures[$block_name] : '';
    }
    
    public function capture($block_name, $content) {
        if (is_callable($content)) {
            $this->start_capture($block_name);
            $content();
            $this->end_capture();
        } else {
            $this->captures[$block_name] = $content;
        }
    }
    
    public function start_capture($block_name) {
        ob_start();
        array_unshift($this->active_captures, $block_name);
    }
    
    public function end_capture() {
        $this->capture(array_shift($this->active_captures), ob_get_clean());
    }
    
    //
    // Helpers
    // Although it's possible to call helpers directly from templates, without qualification,
    // sometimes you may wish to invoke a helper dynamically from elsewhere (e.g. another
    // helper), where there has been no opportunity to pre-compile the code and resolve
    // helper references.
    // To do this: zing\view\Base::$active->my_helper()
    
    private $dynamic_helpers = null;
    
    private function index_dynamic_helpers() {
        $this->dynamic_helpers = array();
        foreach ($this->helpers as $h) {
            $r = new \ReflectionClass($h);
            foreach ($r->getMethods(\ReflectionMethod::IS_STATIC | \ReflectionMethod::IS_PUBLIC) as $m) {
                $this->dynamic_helpers[$m->getName()] = $h;
            }
        }
    }
    
    public function __call($method, $args) {
        if ($this->dynamic_helpers === null) {
            $this->index_dynamic_helpers();
        }
        if (isset($this->dynamic_helpers[$method])) {
            return call_user_func_array(array($this->dynamic_helpers[$method], $method), $args);
        } else {
            throw new NoSuchMethodException("helper not found: $method");
        }
    }
    
    //
    // Compilation
    
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
                // ad-hoc syntax extension;
                // ^{ is accepted as a shorthand for function() {
                // this form exposes a single (optional) argument named $_
                // if you need more args, this syntax is also valid:
                // ^($foo, $bar, $baz) { ... }
                // no spaces are permitted between ^ and { or (
                if ($tok == '^') {
                    $tok = $this->next();
                    if ($tok == '(') {
                        $out .= "function(";
                    } elseif ($tok == '{') {
                        $out .= 'function($_ = null) {';
                    } else {
                        $out .= "^" . (is_array($tok) ? $tok[1] : $tok);
                    }
                } else{
                    $out .= $tok;
                }
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