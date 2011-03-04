<?php
namespace zing\cms\admin;

use \zing\cms\helpers\admin\URLHelper;

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
    public $admin_user = false;
    
    //
    // Filters
    
    protected static $filters = array(
        'before' => array(
            'ensure_admin_logged_in' => true
        )
    );
    
    //
    // Initialisation
    
    protected function init() {
        
        \zing\view\Base::$view_paths[] = ZING_ROOT . '/vendor/plugins/zing.cms/app/views';

        \zing\view\Base::$stylesheet_collections['zing.cms.admin-core'] = array(
            'zing.cms/admin/reset.css',
            'zing.cms/admin/typography.css',
            'zing.cms/admin/flash.css',
            'zing.cms/admin/helpers.css',
            'zing.cms/admin/layout.css'
        );
        
        \zing\view\Base::$stylesheet_collections['zing.cms.admin-session'] = array(
            ':zing.cms.admin-core',
            'zing.cms/admin/session.css'
        );

        \zing\view\Base::$stylesheet_collections['zing.cms.admin'] = array(
            ':zing.cms.admin-core',
            'zing.cms/admin/widgets.css',
            'zing.cms/admin/main.css',
            'zing.cms/admin/asset-dialog.css',
            'zing.cms/jscalendar-1.0/calendar-win2k-1.css'
        );
        
      \zing\view\Base::$javascript_collections['zing.cms.admin'] = array(
            'zing.cms.config.admin.js',
            'zing.cms/jscalendar-1.0/calendar_stripped.js',
            'zing.cms/jscalendar-1.0/lang/calendar-en.js',
            'zing.cms/admin/jquery.min.js',
            'zing.cms/admin/zing.js',
            'zing.cms/admin/jquery.drag-queen.js',
            'zing.cms/tiny_mce/jquery.tinymce.js',
            'zing.cms/admin/jquery.rebind.js',
            'zing.cms/admin/admin.js'
        );
        
        $this->layout('admin/main');
        $this->helper('\zing\cms\helpers\admin\BaseHelper');
        $this->helper('\zing\cms\helpers\admin\URLHelper');
        
        \zing_load_config('zing.cms.admin');
        
        $this->admin_structure = \zing\cms\admin\Structure::instance();
        
    }
    
    //
    // Basic URLs
    
    protected function redirect_to_admin_login() {
        $this->redirect_to(URLHelper::admin_login_url());
    }
    
    protected function redirect_to_admin_dashboard() {
        $this->redirect_to(URLHelper::admin_dashboard_url());
    }
    
    //
    // Admin Login Stuff
    
    protected function is_admin_login_required() {
        return true;
    }
    
    protected function ensure_admin_logged_in() {
        if ($this->is_admin_login_required() && !$this->is_admin_logged_in()) {
            $this->flash('error', 'Please log in before continuing');
            $this->redirect_to_admin_login();
        }
    }
    
    public function log_in_admin($user) {
        $this->session['zing.cms.admin_id'] = $user->get_id();
        $this->admin_user = $user;
    }
    
    public function log_out_admin() {
        $this->admin_user = null;
        $this->session['zing.cms.admin_id'] = null;
    }
    
    public function logged_in_admin() {
        if ($this->admin_user === false) {
            $id = $this->logged_in_admin_id();
            if ($id) {
                $this->admin_user = \zing\cms\ZingUser::find($id);
            } else {
                $this->admin_user = null;
            }
        }
        return $this->admin_user;
    }
    
    public function logged_in_admin_id() {
        return isset($this->session['zing.cms.admin_id'])
                ? $this->session['zing.cms.admin_id']
                : null;
    }
    
    public function is_admin_logged_in() {
        return $this->logged_in_admin_id() !== null;
    }
}
?>