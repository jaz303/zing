<?php
namespace zing;

class DoublePerformException extends \Exception {}

/**
 * This is Zing!'s default controller implementation. There's no reason you
 * have to extend this, the only requirement placed on a controller is that
 * it implements an invoke(zing\http\Request $request, $action_name) method
 * that returns a zing\http\Response object or similar (there are a few
 * extra required methods if you wish to integrate with the standard view
 * classes)
 */
class Controller
{
    /**
     * Array mapping view extensions to handlers.
     * Implementing handlers is easy, check out zing\view\View
     */
    public static $view_handlers = array(
        'php'       => 'zing\\view\\PHPHandler'
    );
    
    protected $request;
    protected $response;
    
    protected $controller_path;
    protected $controller_name;
    protected $action_name;
    
    //
    // Fields with double leading underscores will not be copied to the view
    // ($response isn't copied either, for that matter)
    
    protected $__performed              = false;
    protected $__layout                 = false;
    protected $__helpers                = array();
    
    //
    //
    
    protected function has_performed() {
        return $this->__performed;
    }
    
    protected function set_performed($performed = true) {
        $this->__performed = $performed;
    }
    
    //
    //
    
    public function get_layout() {
        return $this->__layout;
    }
    
    protected function layout($layout_name) {
        $this->__layout = $layout_name;
    }
    
    //
    //
    
    public function get_helpers() {
        return $this->__helpers;
    }
    
    protected function helper($helper_class) {
        $this->__helpers[] = $helper_class;
    }
    
    //
    //
    
    public function get_assigns() {
        $assigns = array();
        foreach ($this as $k => $v) {
            if ($k[0] != '_' || $k[1] != '_') {
                $assigns[$k] = $v;
            }
        }
        unset($assigns['response']);
        $assigns['controller'] = $this;
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
    
    /**
     * This really just exists so subclasses can call parent::__construct()
     */
    public function __construct() {}
    
    public function invoke(\zing\http\Request $request, $action) {
        
        $this->request              = $request;
        $this->response             = new \zing\http\Response;
        
        $this->controller_path      = preg_replace('/_controller$/', '', \zing_class_path($this));
        $this->controller_name      = basename($this->controller_path);
        $this->action_name          = $action;
        
        $this->before();
        
        if (!$this->has_performed()) $this->perform_invoke();
        if (!$this->has_performed()) $this->render('view');
        
        $this->after();
        
        return $this->response;
    
    }
    
    /**
     * Override this in subclasses if you wish to keep the rest of the controller
     * infrastructure but change the default action -> _method mapping
     */
    protected function perform_invoke() {
        $action_method = "_{$this->action_name}";
        if (method_exists($this, $action_method)) {
            $this->$action_method();
        } else {
            throw new NotFoundException("No such action - {$this->action_name}");
        }
    }
    
    //
    // Rendering/redirecting
    
    protected function redirect_to($url) {
        
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
        
        $view_name = $this->controller_path . '/' . $view_name;
        
        $view_paths = \zing\view\Base::candidate_views_for($view_name);
        
        $template_types = array();
        foreach ($view_paths as $vp) {
            $base = basename($vp[1]);
            $p1 = strpos($base, '.');
            $p2 = strrpos($base, '.');
            $tt = substr($base, $p1 + 1, $p2 - $p1 - 1);
            if (!in_array($tt, $template_types)) {
                $template_types[] = $tt;
            }
        }
        
        $template_type = $this->negotiate_template_type($template_types);
        
        foreach ($view_paths as $view_path) {
            if (strpos($view_path[1], ".$template_type.") !== false) {
                break;
            }
        }
        
        $handler_ext = substr($view_path[1], strrpos($view_path[1], '.') + 1);
        
        if (!isset(self::$view_handlers[$handler_ext])) {
            throw new \Exception("no registered view handler for .$handler_ext");
        }
        
        $handler_class = self::$view_handlers[$handler_ext];
        
        $handler = new $handler_class;
        $handler->set_template_type($template_type);
        $handler->import_from_controller($this);
        
        $this->response->set_body($handler->render_view($view_name, $view_path[0]));
        
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