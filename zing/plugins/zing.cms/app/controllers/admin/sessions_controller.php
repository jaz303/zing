<?php
namespace admin;

class SessionsController extends \zing\cms\admin\BaseController
{
    protected function is_admin_login_required() {
        return false;
    }
    
    protected function init() {
        parent::init();
        $this->layout('admin/session');
    }
    
    public function _login() {
        if ($this->is_admin_logged_in()) {
            $this->redirect_to_admin_dashboard();
        } else {
            if ($this->request->is_post()) {
                $user = \zing\cms\ZingUser::authenticate_by_username($this->params['username'],
                                                                     $this->params['password']);
                if ($user) {
                    $this->log_in_admin($user);
                    $this->flash('success', 'Login successful');
                    $this->redirect_to_admin_dashboard();
                } else {
                    $this->flash_now('error', 'Invalid username/password');
                }
            }
        }
    }
    
    public function _logout() {
        $this->log_out_admin();
        $this->redirect_to_admin_login();
    }
    
    public function _reset_password() {
    }
}
?>