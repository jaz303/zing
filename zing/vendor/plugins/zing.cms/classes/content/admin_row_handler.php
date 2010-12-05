<?php
namespace zing\cms\content;

abstract class AdminRowHandler
{
    protected $id;
    protected $spec;
    protected $prototype;
    protected $data;
    
    //
	// Callbacks
	
	protected function before_validate() { }
	protected function before_validate_on_create() { }
	protected function before_validate_on_update() { }
	
	// before_ callbacks can return boolean false to abort save
	protected function before_save() { }
	protected function before_create() { }
	protected function before_update() { }
	
	protected function after_create() { }
	protected function after_update() { }
	protected function after_save() { }
	
	protected function before_delete() { }
	protected function after_delete() { }
	
	// perform any custom validation in here
	// use $this->add_error($message) to add an error (and prevent record from
	// being saved)
	protected function custom_validate() { }
	
	//
	//
    
    public function __construct(ModelSpecification $ms) {
        $this->spec = $ms;
        $this->prototype = $this->spec->prototype();
    }
    
    public function go($action, $method, $data) {
		$this->id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'];
		if (isset($_REQUEST['action'])) {
			$this->action = $_REQUEST['action'];
		} else {
			$this->action = $this->id ? 'add' : 'edit';
		}
		$method = $request->method();
		$action_method = "{$this->action}_{$method}";
		
		$action_method = "{$this->action}_{$method}";
		if (method_exists($this, $handler_method)) {
			$this->$handler_method();		
		} elseif (method_exists($this, $this->action)) {
			$this->{$this->action}();
		}
		
	}
	
	protected function add_get() {
		$this->data = $this->spec->default_values();
	}
	
	protected function add_post() {
		$this->data = $this->post_data();
		if ($this->validate()) {
			if ($this->before_save() === false || $this->before_create() === false) {
				return;
			}
			
			$db = Database::instance();
			$quoted = $this->quoted_data();
			
			if ($ctf = $this->create_timestamp_field()) {
				$quoted[$ctf] = 'NOW()';
			}
			
			$sql = "INSERT INTO {$this->table_name()} (";
			$sql .= implode(',', array_keys($quoted));
			$sql .= ") VALUES (";
			$sql .= implode(',', array_values($quoted));
			$sql .= ")";
			
			$db->exec($sql);
			
			$this->id = mysql_insert_id();
			
			$this->after_create();
			$this->after_save();
			
			flash('success', 'Content was created successfully');
			redirect_to(url_for_content_edit($this->table_name(), $this->row_id()));
		}
	}
	
	protected function edit_get() {
		$this->data = $this->find_row();
	}
	
	protected function edit_post() {
		$this->data = $this->post_data();
		if ($this->validate()) {
			if ($this->before_save() === false || $this->before_update() === false) {
				return;
			}
			
			$db = Database::instance();
			$quoted = $this->quoted_data();
			
			if ($utf = $this->update_timestamp_field()) {
				$quoted[$utf] = 'NOW()';
			}
			
			$sql = "UPDATE {$this->table_name()} SET";
			$sep = " ";
			foreach ($quoted as $field => $value) {
				$sql .= $sep . "$field = $value";
				$sep = ", ";
			}
			
			$db->exec($sql);
			
			$this->after_update();;
			$this->after_save();
			
			flash('success', 'Content was updated successfully');
			redirect_to(url_for_content_edit($this->table_name(), $this->row_id()));
		}
	}
	
	protected function delete_post() {
		
		$this->before_delete();
		
		$db = Database::instance();
		$sql = "DELETE FROM {$this->table_name()} WHERE id = " . (int) $this->row_id();
		$db->exec($sql);
		
		$this->after_delete();
		
		flash('success', 'Content was deleted successfully');
		redirect_to(url_for_content_list($this->table_name()));
	
	}
	
	
	protected function validate() {
		
		$this->errors = array();
		
		$this->before_validate();
		if ($this->is_new_record()) {
			$this->before_validate_on_create();
		} else {
			$this->before_validate_on_update();
		}
		
		$data = $this->data();
		
		foreach ($this->required_fields() as $field) {
			if (!$data[$field]) {
				$this->add_error(AdminInflector::humanize($field) . ' cannot be blank');
			}
		}
		
		foreach ($this->unique_fields() as $field) {
			$db = Database::instance();
			$sql = "SELECT id FROM {$this->table_name()} WHERE $field = " . $db->quoteString($data[$field]);
			$existing_id = $db->firstResult($sql);
			if ($existing_id && ($this->is_new_record() || $existing_id != $this->row_id())) {
				$this->add_error(AdminInflector::humanize($field) . ' must be unique');
			}
		}
		
		$this->custom_validate();
		
		return !$this->has_errors();
	
	}
	
	// Returns an array of data safe for SQL
	protected function quoted_data() {
		$db = Database::instance();
		$quoted = array();
		$data   = $this->data();
		foreach ($this->valid_fields() as $field) {
			if (isset($data[$field])) {
				if (is_numeric($data[$field])) {
					$quoted[$field] = $data[$field];
				} else {
					$quoted[$field] = $db->quoteString($data[$field]);
				}
			} else {
				$quoted[$field] = 'NULL';
			}
		}
		return $quoted;
	}
	
	
	
	protected function filter($field, $value) {
	    if (isset($this->prototype[$field]['filter'])) {
	        $value = \zing\cms\Utils::apply_filter($this->prototype[$field]['filter'], $value);
	    }
	    if (method_exists($this, "filter_$field")) {
	        $value = $this->{"filter_$field"}($value);
	    }
	    return $value;
	}
}
?>