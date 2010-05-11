<?php
namespace zing\routing;

class DuplicateRouteException extends \Exception {}

class Router
{
    public static function compile($__source__, $__target__) {
        
        $R = new self;
        require $__source__;
        
        $compilation = $R->perform_compile();
        
        $fp = fopen(__FILE__, 'r');
        fseek($fp, __COMPILER_HALT_OFFSET__);
        
        $compiled_source = '';
        $in = false;
        
        while (!feof($fp)) {
            $line = fgets($fp, 8192);
            if (preg_match('|// START-ROUTES|', $line)) {
                $compiled_source .= $compilation->export();
                $in = true;
            } elseif (preg_match('|// END-ROUTES|', $line)) {
                $in = false;
            } elseif (!$in) {
                $compiled_source .= $line;
            }
        }
        
        fclose($fp);
        
        mkdir_p(dirname($__target__));
        file_put_contents($__target__, ltrim($compiled_source));
        
    }
    
    private $default_defaults = array(
        'action'    => 'index'
    );
    
    private $default_requirements = array(
        'action'    => '/^\w+$/',
        'id'        => '/^\d+/', // $ deliberately omitted
    );
    
    private $root;
    
    public function __construct() {
        $this->root = new StaticNode('');
    }
    
    public function connect($path, $options = array()) {
        
        $path = trim($path, '/');
        $node = $this->root;
        
        if (isset($options['method'])) {
            $method = array_map('strtolower', (array) $options['method']);
            unset($options['method']);
        } else {
            $method = null;
        }
        
        if (isset($options['requirements'])) {
            $requirements = $options['requirements'];
            unset($options['requirements']);
        } else {
            $requirements = array();
        }
        
        $defaults = $options;
        
        if ($path == '') {
            $node->set_method($method);
            $node->set_endpoint($defaults);
        } else {
            foreach (explode('/', $path) as $route_chunk) {
                if (preg_match('/^:(\w+)$/', $route_chunk, $matches)) {
                    
                    $capture = $matches[1];
                    
                    $requirement = null;
                    if (array_key_exists($capture, $requirements)) {
                        $requirement = $requirements[$capture];
                    } elseif (isset($this->default_requirements[$capture])) {
                        $requirement = $this->default_requirements[$capture];
                    }
                    
                    $default = null;
                    if (array_key_exists($capture, $defaults)) {
                        $default = $defaults[$capture];
                        unset($defaults[$capture]);
                    } elseif (isset($this->default_defaults[$capture])) {
                        $default = $this->default_defaults[$capture];
                    }
                    
                    $node = $node->add_child(new DynamicNode($capture, $requirement, $default));
                    
                } else {
                    $node = $node->add_child(new StaticNode($route_chunk));
                }
            }
            
            $node->set_method($method);
            $node->set_endpoint($defaults);
        }
    }
    
    protected function perform_compile() {
        $compilation = new Compilation;
        $this->root->finalize();
        $this->root->compile($compilation);
        return $compilation;
    }
}

// superload-ignore
abstract class Node
{
    protected $parent   = null;
    protected $children = array();
    protected $method   = null;
    protected $endpoint = null;
    
    public function set_parent(Node $parent) {
        $this->parent = $parent;
    }
    
    public function add_child(Node $new_child) {
        foreach ($this->children as $child) {
            if ($new_child->equals($child)) {
                return $child;
            }
        }
        $new_child->set_parent($this);
        $this->children[] = $new_child;
        return $new_child;
    }
    
    public function get_static_segment() {
        return null;
    }
    
    public function is_terminal() {
        return $this->endpoint !== null;
    }
    
    public function set_method($method) {
        $this->method = $method;
    }
    
    public function set_endpoint($options) {
        if ($this->endpoint === null) {
            $this->endpoint = $options;
        } else {
            throw new DuplicateRouteException;
        }
    }
    
    public function finalize() {
        // if we're not already a terminal node we could become one
        // if there's a path to a terminal upon which all dynamic
        // nodes have default values.
        if (!$this->is_terminal()) {
            foreach ($this->children as $child) {
                if ($this->endpoint = $child->find_default_dynamic_path(array())) {
                    break;
                }
            }
        }
        
        foreach ($this->children as $child) {
            $child->finalize();
        }
    }
    
    public abstract function is_static();
    public abstract function compile(Compilation $c);
    
    protected abstract function find_default_dynamic_path(array $options);
}

// superload-ignore
class StaticNode extends Node
{
    private $segment;
    
    public function __construct($segment) {
        $this->segment = $segment;
    }
    
    public function equals(Node $node) {
        return ($node instanceof StaticNode)
                && ($node->segment == $this->segment);
    }
    
    public function static_path() {
        if ($this->parent) {
            return $this->parent->static_path() . $this->segment . '/'; 
        } else {
            return $this->segment . '/';
        }
    }
    
    public function is_static() {
        return $this->parent ? $this->parent->is_static() : true;
    }
    
    public function compile(Compilation $c) {
        if ($this->is_terminal() && $this->is_static()) {
            $c->add_static_route($this->static_path(), $this->method, $this->endpoint);
        }
        if ($this->parent) {
            $c->push_static_segment($this->segment, $this->method, $this->endpoint);
        }
        foreach ($this->children as $child) {
            $child->compile($c, $state);
        }
        if ($this->parent) {
            $c->pop();
        }
    }
    
    protected function find_default_dynamic_path(array $options) {
        return null;
    }
}

// superload-ignore
class DynamicNode extends Node
{
    private $capture;
    private $requirement;
    private $default;
    
    public function __construct($capture, $requirement, $default) {
        $this->capture = $capture;
        $this->requirement = $requirement;
        $this->default = $default;
    }
    
    public function equals(Node $node) {
        return ($node instanceof DynamicNode)
                && $node->capture == $this->capture
                && $node->requirement == $this->requirement
                && $node->default == $this->default;
    }
    
    public function is_static() {
        return false;
    }
    
    public function compile(Compilation $c) {
        $c->push_dynamic_segment($this->capture, $this->match, $this->method, $this->endpoint);
        foreach ($this->children as $child) {
            $child->compile($c, $state);
        }
        $c->pop();
    }
    
    protected function find_default_dynamic_path(array $options) {
        if ($this->default === null) return null;
        $options[$this->capture] = $this->default;
        if ($this->is_terminal()) {
            return array_merge($options, $this->endpoint);
        } else {
            foreach ($this->children as $child) {
                if ($path = $child->find_default_dynamic_path($options)) {
                    return $path;
                }
            }
        }
        return null;
    }
}

// superload-ignore
class Compilation
{
    private $static_routes      = array();
    private $dynamic_routes     = array();
    private $dynamic_stack      = array();
    
    public function __construct() {
        $this->dynamic_stack[] = &$this->dynamic_routes;
    }
    
    public function add_static_route($path, $method, $endpoint) {
        $rule = array('endpoint' => $endpoint);
        if (!empty($method)) {
            $rule['method'] = (is_array($method) && count($method) == 1) ? $method[0] : $method;
        }
        $this->static_routes[trim($path, '/')] = $rule;
    }
    
    public function push_static_segment($segment, $method, $endpoint) {
        $rule = $this->build_rule($method, $endpoint);
        $this->dynamic_stack[count($this->dynamic_stack) - 1]['static'][$segment] = &$rule;
        $this->dynamic_stack[] = &$rule['children'];
    }
    
    public function push_dynamic_segment($capture, $match, $method, $endpoint) {
        $rule = $this->build_rule($method, $endpoint);
        $rule['capture'] = $capture;
        if ($match) $rule['match'] = $match;
        $this->dynamic_stack[count($this->dynamic_stack) - 1]['dynamic'][] = &$rule;
        $this->dynamic_stack[] = &$rule['children'];
    }
    
    private function build_rule($method, $endpoint) {
        $rule = array('children' => array());
        if (!empty($method)) {
            $rule['method'] = (is_array($method) && count($method) == 1) ? $method[0] : $method;
        }
        if (is_array($endpoint)) {
            $rule['endpoint'] = $endpoint;
        }
        return $rule;
    }
    
    public function pop() {
        array_pop($this->dynamic_stack);
    }
    
    public function export() {
        return 'private static $STATIC_ROUTES = ' . var_export($this->static_routes, true) . ";\n\n" .
                'private static $DYNAMIC_ROUTES = ' . var_export($this->dynamic_routes, true). ";\n\n";
    }
}

//
// Kill the compiler - the following code is the skeleton recognizer that we're
// gonna compile.

__halt_compiler();
<?php
namespace zing\routing;

class Recognizer
{
    // The compiler replaces these vars with the real routes
    // They're public as a debugging aid only.
    
    // START-ROUTES
    public static $STATIC_ROUTES = array(
        '' => array(
            'endpoint' => array('controller' => 'Welcome', 'action' => 'index'),
            'method' => array('post', 'get')
        )
    );
    
    public static $DYNAMIC_ROUTES = array(
        'static' => array(
            'auctions' => array(
                'endpoint' => array('controller' => 'Auctions', 'action' => 'index'),
                'method' => 'get',
                'children' => array(
                    'dynamic' => array(
                        array(
                            'capture' => 'id',
                            'match' => '/^\d+$/',
                            'endpoint' => array('controller' => 'Auctions', 'action' => 'show'),
                            'method' => 'get'
                        )
                    )
                )
            )
        )
    );
    // END-ROUTES
    
    private static $method;
    private static $chunks;
    private static $end_index;
    
    public static function recognize($path, $method) {
        
        $path = trim($path, '/');
        $method = strtolower($method);
        
        if (isset(self::$STATIC_ROUTES[$path])) {
            $sr = self::$STATIC_ROUTES[$path];
            if (!isset($sr['method']) || in_array($method, (array) $sr['method'])) {
                return $sr['endpoint'];
            }
        }
        
        // HACK
        if ($path == '') {
            return null;
        }
        
        self::$method = $method;
        self::$chunks = explode('/', $path);
        self::$end_index = count(self::$chunks) - 1;
        
        return self::recursive_recognize(0, self::$DYNAMIC_ROUTES, array());
        
    }
    
    private static function recursive_recognize($index, &$step, array $params) {
        
        $chunk = self::$chunks[$index];
        
        if (isset($step['static'][$chunk])) {
            $rule = $step['static'][$chunk];
            if ($index == self::$end_index) {
                if (isset($rule['endpoint'])) {
                    if (!isset($rule['method']) || in_array(self::$method, $rule['method'])) {
                        return array_merge($params, $rule['endpoint']);
                    }
                }
            } elseif ($match = self::recursive_recognize($index + 1, $rule['children'], $params)) {
                return $match;
            }
        }
        
        if (isset($step['dynamic'])) {
            foreach ($step['dynamic'] as $rule) {
                if (!isset($rule['match']) || preg_match($rule['match'], $chunk)) {
                    $params[$rule['capture']] = $chunk;
                    if ($index == self::$end_index) {
                        if (isset($rule['endpoint'])) {
                            if (!isset($rule['method']) || in_array(self::$method, $rule['method'])) {
                                return array_merge($params, $rule['endpoint']);
                            }
                        }
                    } elseif ($match = self::recursive_recognize($index + 1, $rule['children'], $params)) {
                        return $match;
                    }
                }
            }
        }
        
        return null;
        
    }
}
?>