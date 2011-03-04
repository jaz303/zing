<?php
namespace zing\cms;

abstract class Model
{
    public static function table_name() {
        throw new \UnsupportedOperationException("no table defined!");
    }
    
    public static function find($id) {
        $record = static::find_one('id', 'i', $id);
        if (!$record) throw new \NotFoundException("Couldn't find record with ID = $id");
        return $record;
    }
    
    protected static function find_one($field, $type, $value) {
        $class = get_called_class();
        $table = static::table_name();
        
        $db = \GDB::instance();
        $row = $db->q("SELECT * FROM $table WHERE $field = {" . $type . "}", $value)->first_row();
        
        if ($row) {
            return new $class($row);
        } else {
            return null;
        }
    }
    
    public static function find_all_where($conditions, $order = null) {
        $class = get_called_class();
        $table = static::table_name();
        
        $db = \GDB::instance();
        if (is_array($conditions)) {
            $conditions = call_user_func_array(array($db, 'auto_quote_query'), $conditions);
        }
        
        $sql = "SELECT * FROM $table WHERE $conditions";
        if ($order) {
            $sql .= " ORDER BY $order";
        }
        
        $res = $db->q($sql);
        
        $out = array();
        foreach ($res as $row) {
            $out[] = new $class($row);
        }
        
        return $out;
    }
    
    //
    // Constructor
    
    public function __construct($attributes = null) {
        if (is_array($attributes)) {
            $this->set_all_attributes($attributes);
            if (isset($attributes['id'])) {
                $this->set_id($attributes['id']);
            }
        }
    }
    
    //
    // ID
    
    protected $id = null;
    
    public function get_id() { return $this->id; }
    protected function set_id($id) { $this->id = $id === null ? null : (int) $id; }
    
    //
    // Attributes
    
    public function set_attribute($name, $value) {
        return $this->{"set_$name"}($value);
    }
    
    public function try_set_attribute($name, $value) {
        $method = "set_$name";
        if (method_exists($this, $method)) {
            return $this->$method($value);
        } else {
            return null;
        }
    }
    
    protected function public_attribute_names() { return array(); }
    protected function protected_attribute_names() { return array(); }
    
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
                $this->set_attribute($v, $attributes[$v]);
            }
        }
    }
    
    //
    // Persistence
    
    public function get_table_name() {
        return static::table_name();
    }
    
    public function is_new_record() {
        return $this->id === null;
    }
    
    public function save() {
        
        // FIXME: should restore old values of created_at/updated_at as necessary
        
        if (!$this->is_valid()) {
            die();
        }
        
        if ($this->before_save() === false) {
            return false;
        }
        
        $db = \GDB::instance();
        $now = new \Date_Time;
        $this->try_set_attribute('updated_at', $now);
        
        $db->begin();
        try {
            if ($this->before_save() === false) {
                $db->rollback();
                return false;
            }
            if ($this->is_new_record()) {
                if ($this->before_create() === false) {
                    $db->rollback();
                    return false;
                }
                $this->try_set_attribute('created_at', $now);
                $this->id = $db->insert($this->get_table_name(), $this->type_hinted_attributes());
                $this->after_create();
            } else {
                if ($this->before_update() === false) {
                    $db->rollback();
                    return false;
                }
                $db->update($this->get_table_name(), $this->type_hinted_attributes(), 'i:id', $this->id);
                $this->after_update();
            }
            $this->after_save();
            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
        
        return true;
        
    }
    
    public function delete() {
        if ($this->before_delete() === false) {
            return false;
        }
        \GDB::instance()->x("DELETE FROM {$this->get_table_name()} WHERE id = {i}", $this->id);
        $this->set_id(null);
        $this->after_delete();
        return true;
    }
    
    protected function type_hinted_attributes() {
        return array();
    }
    
    //
    // Validation
    
    protected $errors = null;
    
    public function is_valid() {
        $this->errors = new \Errors;
        $this->before_validation();
        $this->do_validation();
        $this->after_validation();
        return $this->errors->ok();
    }
    
    public function errors() {
        return $this->errors;
    }
    
    /**
     * Override this method to perform custom validation.
     * Populate `$this->errors`.
     */
    protected function do_validation() {}
    
    //
    // Hooks
    
    protected function before_validation() { }
    protected function after_validation() { }
    
    protected function before_save() { }
    protected function before_create() { }
    protected function before_update() { }
    protected function after_update() { }
    protected function after_create() { }
    protected function after_save() { }
    
    protected function before_delete() { }
    protected function after_delete() { }
}
?>