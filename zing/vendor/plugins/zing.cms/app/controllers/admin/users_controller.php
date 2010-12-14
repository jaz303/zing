<?php
namespace admin;

use \zing\cms\ZingUser;

class UsersController extends \zing\cms\admin\BaseController
{
    public function _index() {
        $this->users = ZingUser::find_all();
    }
    
    public function _delete() {
        $this->user = ZingUser::find($this->params['id']);
        if ($this->user->get_id() == $this->logged_in_admin_id()) {
            $this->flash('warning', 'You cannot delete your own user account');
        } else {
            $this->user->delete();
            $this->flash('success', 'User ' . $this->user->get_username() . ' deleted successfully');
        }
        $this->redirect_to($this->admin_users_url());
    }
    
    public function _edit() {
        $this->user = ZingUser::find($this->params['id']);
        if ($this->request->is_post()) {
            
        }
    }
    
    protected function admin_users_url() {
        return \zing\cms\helpers\admin\URLHelper::admin_url(':users');
    }
}
?>