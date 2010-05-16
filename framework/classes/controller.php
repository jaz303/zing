<?php
namespace zing;

class DoublePerformException extends \Exception {}

/**
 * This is Zing!'s default controller implementation. There's no reason you
 * have to extend this, the only requirement placed on a controller is that
 * it implements an invoke(zing\http\Request $request, $action_name) method
 * that returns a zing\http\Response object or similar.
 */
class Controller
{
    /**
     * Array mapping view extensions to handlers.
     * Implementing handlers is easy, check out zing\view\View
     */
    public static $view_handlers = array(
        'php'       => 'zing\\view\\View'
    );
    
    public static $view_paths = array(
        ZING_VIEW_DIR
    );
    
    protected $request;
    protected $response;
    
    protected $controller_namespace;
    protected $controller_class;
    protected $controller_name;
    protected $action_name;
    
    //
    // Fields with double leading underscores will not be copied to the view
    // ($response isn't copied either, for that matter)
    
    protected $__performed;
    protected $__helpers;
    
    //
    //
    
    protected function has_performed() {
        return $this->__performed;
    }
    
    protected function set_performed($performed = true) {
        $this->__performed = $performed;
    }
    
    public function get_helpers() {
        return $this->__helpers;
    }
    
    protected function helper($helper_class) {
        $this->__helpers[] = $helper_class;
    }
    
    protected function clear_helpers() {
        $this->__helpers = array();
    }
    
    public function get_assigns() {
        $assigns = array();
        foreach ($this as $k => $v) {
            if ($k[0] != '_' || $k[1] != '_') {
                $assigns[$k] = $v;
            }
        }
        unset($assigns['response']);
        return $assigns;
    }
    
    /**
     * Set a new response object.
     *
     * The main reason to use this method is if you're sending a file - use a
     * zing\http\FileResponse instance to avoid loading the whole thing into
     * memory. In the future, this will take advantage of the server's support
     * for the X-Sendfile header, if any.
     *
     * @param $response new response object
     */
    protected function set_response($response) {
        $this->response = $response;
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
        
        $this->helper('\\zing\\helpers\\DebugHelper');
        $this->helper('\\zing\\helpers\\HTMLHelper');
        $this->helper('\\zing\\helpers\\FormHelper');
        
        $this->init();
        
    }
    
    /**
     * Override to perform custom initialisation.
     */
    protected function init() {}
    
    public function invoke(\zing\http\Request $request, $action) {
        
        $this->request              = $request;
        $this->response             = new \zing\http\Response;
        
        $class = get_class($this);
        if ($p = strrpos($class, '\\')) {
            $this->contoller_namespace  = substr($class, 0, $p);
            $this->controller_class     = substr($class, $p + 1);
        } else {
            $this->controller_namespace = '';
            $this->controller_class     = $class;
        }
        
        $this->controller_name      = strtolower(preg_replace('/([^^])([A-Z])/e', '$1_$2', $this->controller_class));
        $this->controller_name      = preg_replace('/_controller$/', '', $this->controller_name);
        $this->action_name          = $action;
        
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
            $this->render('view');
        }
        
        $this->after();
        
        return $this->response;
    
    }
    
    //
    // Rendering/redirecting
    
    protected function redirect($url) {
        
        if ($this->has_performed()) {
            throw new DoublePerformException;
        }
        
        $this->set_performed(true);
        $this->set_response(\zing\http\Response::redirect($url));
    
    }
    
    protected function render($what) {
        
        if ($this->has_performed()) {
            throw new DoublePerformException;
        }
        
        $this->set_performed(true);
        
        $this->before_render();
        $render_args = func_get_args();
        array_shift($render_args);
        call_user_func_array(array($this, "render_$what"), $render_args);
        $this->after_render();
    
    }
    
    protected function render_file($file, $options = array()) {
        $response = new \zing\http\FileResponse($file);
        foreach ($options as $k => $v) {
            $response->{"set_$k"}($v);
        }
        $this->set_response($response);
    }
    
    protected function render_html($html) {
        $this->response->set_content_type('text/html');
        $this->response->set_body($html);
    }
    
    protected function render_json($object) {
        if (is_object($object)) {
            if (method_exists($object, 'to_json')) {
                $json = $object->to_json();
            } elseif (method_exists($object, 'to_structure')) {
                $json = json_encode($object->to_structure());
            } else {
                $json = json_encode($object);
            }
        } else {
            $json = json_encode($object);
        }
        $this->response->set_content_type('application/json');
        $this->response->set_body($json);
    }
    
    protected function render_nothing() {
        $this->render_text('');
    }
    
    protected function render_text($text) {
        $this->response->set_content_type('text/plain');
        $this->response->set_body($text);
    }
    
    /**
     * Renders a view
     *
     * We now have a view name, e.g.
     * admin/stories/edit
     *
     * The algorithm to find the correct view is as follows:
     * 1. foreach registered view path $v
     *      append view name to $v
     *      glob for $v.*.*
     *      collect all candidate views
     * 2. extract distinct template types from candidate views
     * 3. run content negotiation algorithm on template types to find best fit
     * 4. find first candidate template with matching type
     * 5. derive view handler
     * 6. instantiate view handler
     * 7. render view
     *     
     * That's quite a lot of work to do.
     *
     * Possible optimisations:
     * 1. caching - keep a map of controller/view candidates
     *    (globbing is the main performance problem; would be enabled in production mode only)
     * 2. if there's only one registered template type and one handler, just assume default
     * 3. callback method can be used to selectively disable content negotiation
     */
    protected function render_view($view_name = null) {
        
        if ($view_name === null) {
            $view_name = $this->action_name;
        }
        
        $view_name = $this->controller_name . '/' . $view_name;
        
        if ($this->controller_namespace) {
            $view_name = str_replace('\\', '/', $this->controller_namespace) . '/' . $view_name;
        }
        
        $view_file = ZING_VIEW_DIR . '/' . $view_name . '.html.php';
        
        $view = new \zing\view\View;
        $view->set_controller($this);
        
        $this->response->set_body($view->render_file($view_file));
        
    }
    
    //
    // Rendering Support
    
    /**
     * Given an array of available template types, select the best match
     * for the client and return it.
     *
     * @todo implement content negotiation and provide hooks for extending
     *       to facilitate support for mobile devices etc.
     */
    protected function negotiate_template_type($candidates) {
        return $candidates[0];
    }
}
?>