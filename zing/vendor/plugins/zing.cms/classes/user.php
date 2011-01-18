<?php
namespace zing\cms;

class ZingUser extends \zing\cms\Model
{
    //
    // Statics
    
    public static function table_name() { return 'zing_user'; }
    
    public static function hash_password($salt, $password) {
        return sha1("$salt:$password"); // TODO: bcrypt
    }
    
    public static function find_by_email($email) {
        return static::find_one('email', 's', $email);
    }
    
    public static function find_by_username($username) {
        return static::find_one('username', 's', $username);
    }
    
    public static function find_all() {
        return \GDB::instance()->q("SELECT * FROM zing_user ORDER BY username ASC")
                               ->mode('object', '\zing\cms\ZingUser');
    }
    
    public static function authenticate($user, $password) {
        if ($user->is_password($password)) {
            return $user;
        } else {
            return false;
        }
    }
    
    public static function authenticate_by_username($username, $password) {
        $class = get_called_class();
        $db = \GDB::instance();
        $row = $db->q("SELECT * FROM zing_user WHERE username = {s}", $username)->first_row();
        if ($row) {
            return self::authenticate(new $class($row), $password);
        } else {
            return false;
        }
    }
    
    public static function authenticate_by_email($email, $password) {
        $class = get_called_class();
        $db = \GDB::instance();
        $row = $db->q("SELECT * FROM zing_user WHERE email = {s}", $email)->first_row();
        if ($row) {
            return self::authenticate(new $class($row), $password);
        } else {
            return false;
        }
    }
    
    //
    // 
    
    private $username               = "";
    private $forename               = "";
    private $surname                = "";
    private $email                  = "";
    private $password_hash          = null;
    private $salt                   = null;
    private $created_at             = null;
    private $updated_at             = null;
    
    private $password               = null;
    private $password_confirmation  = null;
    
    public function get_username() { return $this->username; }
    public function get_forename() { return $this->forename; }
    public function get_surname() { return $this->surname; }
    public function get_email() { return $this->email; }
    protected function get_password_hash() { return $this->password_hash; }
    protected function get_salt() { return $this->salt; }
    public function get_created_at() { return $this->created_at; }
    public function get_updated_at() { return $this->updated_at; }
    
    public function get_password() { return $this->password; }
    public function get_password_confirmation() { return $this->password_confirmation; }
    
    public function set_username($v) { $this->username = trim($v); }
    public function set_forename($v) { $this->forename = trim($v); }
    public function set_surname($v) { $this->surname = trim($v); }
    public function set_email($v) { $this->email = trim($v); }
    protected function set_password_hash($v) { $this->password_hash = $v; }
    protected function set_salt($v) { $this->salt = $v; }
    protected function set_created_at($dt) { $this->created_at = $dt; }
    protected function set_updated_at($dt) { $this->updated_at = $dt; }
    
    public function set_password($password) {
        // TODO: generate random salt
        $salt = 'aaa';
        $this->password = (string) $password;
        $this->set_salt($salt);
        $this->set_password_hash(self::hash_password($this->salt, $this->password));
    }
    
    public function set_password_confirmation($password_confirmation) {
        $this->password_confirmation = (string) $password_confirmation;
    }
    
    public function is_password($password) {
        return strcmp($this->password_hash, self::hash_password($this->salt, $password)) == 0;
    }
    
    protected function public_attribute_names() {
        return array('username', 'forename', 'surname', 'email', 'password', 'password_confirmation');
    }
    
    protected function protected_attribute_names() {
        return array('password_hash', 'salt', 'created_at', 'updated_at');
    }
    
    //
    // Persistence
    
    protected function type_hinted_attributes() {
        return array(
            's:username'                => $this->get_username(),
            's:forename'                => $this->get_forename(),
            's:surname'                 => $this->get_surname(),
            's:password_hash'           => $this->get_password_hash(),
            's:salt'                    => $this->get_salt(),
            's:email'                   => $this->get_email(),
            'dt:created_at'             => $this->get_created_at(),
            'dt:updated_at'             => $this->get_updated_at()
        );
    }
    
    //
    // Validation

    protected function do_validation() {
        if (is_blank($this->username)) {
            $this->errors->add('username', 'cannot be blank');
        } else if (!preg_match('/^[a-z][a-z0-9_-]{2,}$/i', $this->username)) {
            $this->errors->add('username', 'is invalid');
        } else if (($existing = self::find_by_username($this->username)) && ($existing->get_id() != $this->get_id())) {
            $this->errors->add('username', 'has already been taken');
        }
        
        if (!is_email($this->email)) {
            $this->errors->add('email', 'is invalid');
        } else if (($existing = self::find_by_email($this->email)) && ($existing->get_id() != $this->get_id())) {
            $this->errors->add('email', 'has already been taken');
        }
        
        if (strlen($this->forename) == 0) {
            $this->errors->add('forename', 'cannot be blank');
        }
        
        if (strlen($this->surname) == 0) {
            $this->errors->add('surname', 'cannot be blank');
        }
        
        if ($this->is_new_record()) {
            if (is_empty($this->password)) {
                $this->errors->add('password', 'cannot be blank');
            }
        }
        
        if ($this->password_confirmation !== null && strcmp($this->password, $this->password_confirmation) != 0) {
            $this->errors->add('password_confirmation', 'does not match');
        }
    }
}
?>