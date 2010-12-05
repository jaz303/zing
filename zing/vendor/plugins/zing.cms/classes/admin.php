<?php
namespace zing\cms\admin;

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
        $this->add_module('core', 'Core', ':core', array(
            'home' => array('url' => '#', 'icon' => 'home', 'title' => 'Home', 'children' => array(
                'index' => array('title' => 'Dashboard', 'url' => '#', 'icon' => 'dashboard'),
                'common_tasks' => array('title' => 'Common Tasks', 'url' => '#', 'icon' => 'gear')
            )),
            'stats' => array('url' => '#', 'icon' => 'chart', 'title' => 'Stats'),
            'assets' => array('url' => '#', 'icon' => 'picture', 'title' => 'Assets'),
            'security' => array('url' => '#', 'icon' => 'lock', 'title' => 'Security'),
            'system' => array('url' => '#', 'icon' => 'application_monitor', 'title' => 'System')
        ));
        
        $this->add_module('cms', 'CMS', ':cms', array(
            'content' => array('icon' => 'node_select', 'title' => 'Content')
        ));
    }
    
    public function add_module($id, $name, $url, $children = array()) {
        $this->modules[$id] = array('id'        => $id,
                                    'name'      => $name,
                                    'url'       => $url,
                                    'children'  => $children);
    }
    
    public function available_modules() {
        return $this->modules;
    }
    
    public function available_sections($section_path) {
        if (is_string($section_path)) $section_path = explode('.', $section_path);
        if (empty($section_path)) {
            return array();
        } else {
            return $this->modules[$section_path[0]]['children'];
        }
    }
    
    public function section_for($section_path) {
        if (is_string($section_path)) $section_path = explode('.', $section_path);
        $mod = $this->modules[$section_path[0]];
        return $mod['children'][$section_path[1]];
    }
}

class BaseController extends \zing\Controller
{
    protected function init() {
        \zing_load_config('zing.cms.admin');
        $this->layout('admin/main');
        $this->helper('\\zing\\cms\\helpers\\AdminHelper');
        $this->admin_structure = \zing\cms\admin\Structure::instance();
    }
}
?>