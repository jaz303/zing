<?php
namespace zing\cms;

class ZingUser
{
    //
    // Statics
    
    public static function hash_password($salt, $password) {
        return sha1("$salt:$password"); // TODO: bcrypt
    }
    
    public static function find($id) {
        $class = get_called_class();
        $db = \GDB::instance();
        $row = $db->q("SELECT * FROM zing_user WHERE id = {i}", $id)->first_row();
        if ($row) {
            return new $class($row);
        } else {
            return null;
        }
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
    
    private $id                 = null;
    private $username           = "";
    private $forename           = "";
    private $surname            = "";
    private $email              = "";
    private $password_hash      = null;
    private $salt               = null;
    
    public function get_id() { return $this->id; }
    public function get_username() { return $this->username; }
    public function get_forename() { return $this->forename; }
    public function get_surname() { return $this->surname; }
    public function get_email() { return $this->email; }
    protected function get_password_hash() { return $this->password_hash; }
    protected function get_salt() { return $this->salt; }
    
    protected function set_id($v) { $this->id = (int) $v; }
    public function set_username($v) { $this->username = trim($v); }
    public function set_forename($v) { $this->forename = trim($v); }
    public function set_surname($v) { $this->surname = trim($v); }
    public function set_email($v) { $this->email = trim($v); }
    protected function set_password_hash($v) { $this->password_hash = $v; }
    protected function set_salt($v) { $this->salt = $v; }
    
    public function __construct($attributes = null) {
        if (is_array($attributes)) {
            $this->set_all_attributes($attributes);
            if (isset($attributes['id'])) {
                $this->set_id($attributes['id']);
            }
        }
    }
    
    public function is_password($password) {
        return strcmp($this->password_hash, self::hash_password($this->salt, $password)) == 0;
    }
    
    protected function public_attribute_names() {
        return array('username', 'forename', 'surname', 'email');
    }
    
    protected function protected_attribute_names() {
        return array('password_hash', 'salt');
    }
    
    public function set_all_attributes(array $attributes) {
        $this->set_public_attributes($attributes);
        $this->set_protected_attributes($attributes);
    }
    
    public function set_public_attributes(array $attributes) {
        $this->__set_attributes($attributes, $this->public_attribute_names());
    }
    
    public function set_protected_attributes(array $attributes) {
        $this->__set_attributes($attributes, $this->protected_attribute_names());        
    }
    
    protected function __set_attributes(array $attributes, array $valid_attributes) {
        foreach ($valid_attributes as $v) {
            if (array_key_exists($v, $attributes)) {
                $this->{"set_$v"}($attributes[$v]);
            }
        }
    }
    
    //
    // Persistence
    
    public function delete() {
        \GDB::instance()->x("DELETE FROM zing_user WHERE id = {i}", $this->id);
        $this->set_id(null);
        return true;
    }
}
?>