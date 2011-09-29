<?php
namespace admin\core;

use \zing\cms\auth\ZingUser;
use \zing\cms\admin\helpers\URLHelper as URLHelper;

class UsersController extends \zing\cms\admin\BaseController
{
    public function section_path() { return 'core.security'; }
  
    public function _index() {
        $this->users = ZingUser::find_all();
    }
    
    public function _delete() {
        $this->user = ZingUser::find($this->params['id']);
        if ($this->user->get_id() == $this->logged_in_admin_id()) {
            $this->flash('warning', 'You cannot delete your own user account');
            $this->redirect_to($this->admin_users_url());
        } elseif ($this->request->is_post()) {
            $this->user->delete();
            $this->flash('success', 'User ' . $this->user->get_username() . ' deleted successfully');
            $this->redirect_to($this->admin_users_url());
        }
        
    }
    
    public function _create() {
        $this->user = new ZingUser;
        if ($this->request->is_post()) {
            $this->user->set_public_attributes($this->params['user']);
            if ($this->user->is_valid()) {
                $this->user->save();
                $this->flash('success', 'User ' . $this->user->get_username() . ' created successfully');
                $this->redirect_to($this->admin_users_url());
            }
        }
    }
    
    public function _edit() {
        $this->user = ZingUser::find($this->params['id']);
        if ($this->request->is_post()) {
            if (is_empty($this->params['user']['password'])) {
                unset($this->params['user']['password']);
                unset($this->params['user']['password_confirmation']);
            }
            $this->user->set_public_attributes($this->params['user']);
            if ($this->user->is_valid()) {
                $this->user->save();
                $this->flash('success', 'User ' . $this->user->get_username() . ' updated successfully');
                $this->redirect_to($this->admin_users_url());
            }
        }
    }
    
    protected function admin_users_url() {
        return URLHelper::admin_users_url();
    }
}
?>