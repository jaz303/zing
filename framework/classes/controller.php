<?php
namespace zing;

class DoublePerformException extends \Exception {}
class NoPerformException extends \Exception {}

/**
 * This is Zing!'s default controller implementation. There's no reason you
 * have to extend this, the only requirement placed on a controller is that
 * it implements an invoke(zing\http\Request $request, $action_name) method
 * that returns a zing\http\Response object or similar.
 */
class Controller
{
    protected $request;
    protected $action_name;
    
    //
    // Fields with double leading underscores will not be copied to the view
    
    protected $__performed;
    protected $__helpers;
    protected $__response;

    protected function has_performed() {
        return $this->__performed;
    }
    
    protected function set_performed($performed = true) {
        $this->__performed = $performed;
    }
    
    protected function helper($helper_class) {
        $this->__helpers[] = $helper_class;
    }
    
    protected function clear_helpers() {
        $this->__helpers = array();
    }
    
    /**
     * Set a new response object.
     *
     * The main reason to use this method is if you're sending a file - use a
     * zing\http\FileResponse instance to avoid loading the whole thing into
     * memory. In the future, this will take advantage of the server's support
     * for the X-Sendfile header, if any.
     *
     * Calling this method will mark the controller as having performed.
     *
     * @param $response new response object
     */
    protected function set_response($response) {
        $this->__response = $response;
        $this->__performed = true;
    }
    
    //
    // Callbacks
    
    /**
     * Called before controller action is invoked
     * If you render or redirect in this method, the main action will not be
     * invoked.
     */
    protected function before() { }
    
    /**
     * Called after controller action has been invoked.
     * This is the last chance to render/redirect if it hasn't happened yet.
     */
    protected function after() { }
    
    /**
     * Called before rendering occurs
     */
    protected function before_render() { }
    
    /**
     * Called after rendering is completed
     */
    protected function after_render() { }
    
    //
    //
    
    public function __construct() {
        
        $this->__performed  = false;
        $this->__helpers    = array();
        $this->__response   = new \zing\http\Response;
        
        $this->helper('\\zing\\helpers\\HTMLHelper');
        $this->helper('\\zing\\helpers\\FormHelper');
        
        $this->init();
        
    }
    
    /**
     * Override to perform custom initialisation.
     */
    protected function init() {}
    
    public function invoke(\zing\http\Request $request, $action) {
        
        $this->request      = $request;
        $this->action_name  = $action;
        
        $this->before();
        
        if (!$this->has_performed()) {
            $action_method = "_$action";
            if (method_exists($this, $action_method)) {
                $this->$action_method();
            } else {
                die('foo');
            }
        }
        
        // do some mumbo-jumbo
        
        if (!$this->has_performed()) {
            // perform default
        }
        
        $this->after();
        
        if (!$this->has_performed()) {
            throw new NoPerformException;
        }
        
        return $this->__response;
    
    }
    
    protected function render($file) {
        
        if ($this->has_performed()) {
            throw new DoublePerformException;
        }
        
        $this->before_render();
        
        $view = new \zing\view\View;
        foreach ($this->__helpers as $helper_class) {
            $view->add_helper($helper_class);
        }
        
        foreach ($this as $k => $v) {
            if ($k[0] != '_' || $k[1] != '_') {
                $view->set($k, $v);
            }
        }

        $this->__response->set_body($view->render_file(ZING_VIEW_DIR . '/' . $file . '.php'));
        $this->set_performed(true);
        
        $this->after_render();
    
    }
}
?>