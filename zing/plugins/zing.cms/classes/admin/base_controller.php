<?php
namespace zing\cms\admin;

use \zing\cms\admin\helpers\URLHelper as URLHelper;

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
        
        \zing\view\Base::$view_paths[] = ZING_ROOT . '/plugins/zing.cms/app/views';

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
        $this->helper('\zing\cms\admin\helpers\BaseHelper');
        $this->helper('\zing\cms\admin\helpers\URLHelper');
        
        \zing_load_config('zing.cms.admin');
        
        $this->admin_structure = \ff\admin\structure\Structure::instance();
        
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
    // Section path
    
    /**
     * Returns a string denoting the section path for this admin controller.
     *
     * Example section path: "cms.content"
     *
     * A controller's section path determines where it exists within the admin system's
     * logical hierarchy, and is used to select navigation options for display. In
     * the future it will also be the basis for access-control.
     *
     * !REFACTOR this should be an annotation at the class level, so we can index them
     * ahead of time. Of course, this presupposes an annotation system...
     */
    public function section_path() { return ''; }
    
    //
    // Admin Login/Sessions
    
    /**
     * Returns true if a valid admin login is required to perform the specified $action.
     *
     * @param $action
     * @return true if admin login is required, false otherwise.
     */
    protected function is_admin_login_required($action) {
        return true;
    }
    
    protected function ensure_admin_logged_in() {
        if ($this->is_admin_login_required($this->action_name) && !$this->is_admin_logged_in()) {
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
                $this->admin_user = \zing\cms\auth\ZingUser::find($id);
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