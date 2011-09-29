<?php
namespace ff\admin\structure;

use \zing\cms\admin\helpers\URLHelper as URLHelper;

class Section
{
    public $parent;
    public $id;
    public $name;
    public $url;
    public $icon;
    public $children;
    
    private $path = null;
    
    public function __construct($parent, $id, $name, $url, $icon = null) {
        $this->parent   = $parent;
        $this->id       = $id;
        $this->name     = $name;
        $this->url      = URLHelper::admin_url($url);
        $this->icon     = $icon;
        $this->children = array();
    }
    
    public function add_child($id, $name, $url, $icon = null) {
        $child = new Section($this, $id, $name, $url, $icon);
        $this->children[$child->id] = $child;
        return $child;
    }
    
    public function path() {
        if ($this->path === null) {
            if ($this->parent === null) {
                $this->path = $this->id;
            } else {
                $this->path = $this->parent->path() . '.' . $this->id;
            }
        }
        return $this->path;
    }
    
    public function contains($path) {
        return strpos($path, $this->path()) === 0;
    }
}

/**
 * Structure is used to define the navigation structure of the admin system
 */
class Structure
{
    private static $instance = null;
  
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }
    
    private $modules = array();
    
    public function __construct() {
        $this->setup_defaults();
    }
    
    protected function setup_defaults() {
        
        $core = $this->add_module('core', 'Core', ':core/dashboard');
        $core->add_child('home', 'Home', ':core/dashboard', 'home');
        $core->add_child('security', 'Security', ':core/users', 'lock');
        
        $system = $core->add_child('system', 'System', ':core/system', 'application_monitor');
        $system->add_child('overview', 'Overview', ':core/system', 'application_monitor');
        $system->add_child('database', 'Database', ':core/system/database', 'database');
        
        $cms = $this->add_module('cms', 'CMS', ':cms/site_tree');
        $cms->add_child('site_tree', 'Site Tree', ':cms/site_tree', 'node_select');
        $cms->add_child('content', 'Content', ':cms/content', 'blog');
    
    }
    
    public function add_module($id, $name, $url) {
        $module = new Section(null, $id, $name, $url);
        $this->modules[$module->id] = $module;
        return $module;
    }
    
    public function available_modules() {
        return $this->modules;
    }
    
    public function module_for($section_path) {
        if (is_string($section_path)) $section_path = explode('.', $section_path);
        if (count($section_path) < 1) {
            return null;
        } else {
            return $this->modules[$section_path[0]];
        }
    }
    
    public function section_for($section_path) {
        if (is_string($section_path)) $section_path = explode('.', $section_path);
        if (count($section_path) < 2) {
            return null;
        } else {
            return $this->modules[$section_path[0]]->children[$section_path[1]];
        }
    }
}
?>