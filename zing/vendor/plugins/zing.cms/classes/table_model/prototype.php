<?php
namespace zing\cms\table_model;

class Prototype
{
	/**
	 * Maps type names to implementing classes.
	 * You can modify this via config files to support custom data types.
	 */
	public static $FIELD_TYPES = array(
		'boolean'		=> '\zing\cms\table_model\BooleanField',
		'date'			=> '\zing\cms\table_model\DateField',
		'date_time'		=> '\zing\cms\table_model\DateTimeField',
		'float'			=> '\zing\cms\table_model\FloatField',
		'integer'		=> '\zing\cms\table_model\IntegerField',
		'string'		=> '\zing\cms\table_model\StringField',
	);
	
	private $fields = array();
	private $form_groups = null;
	
	/**
	 * Add a field to this Prototype
	 *
	 * @param $type type name of field
	 * @param $name unique name of field
	 * @return the new field object
	 */
	public function field($type, $name) {
		$class = self::$FIELD_TYPES[$type];
		$field = new $class($name);
		$this->fields[$field->get_name()] = $field;
		return $field;
	}
	
	/**
	 * Returns an associative array of default values for this prototype, suitable
	 * for initialising a form.
	 *
	 * @return associative array of field_name => default_value
	 */
	public function default_row() {
		$out = array();
		foreach ($this->fields as $name => $field) {
			$out[$name] = $field->get_default_value();
		}
		return $out;
	}
	
	public function group($name) {
		
	}
}

abstract class Field
{
	private $name;
	
	private $caption			= null;
	private $note				= '';
	
	private $default_value_set 	= false;
	private $default_value;
	
	private $filters			= array();
	private $validations		= array();
	
	private $required			= false;
	
	public function __construct($name) {
		$this->name = $name;
	}
	
	//
	//
	
	public function coerce_form_input($value) {
		if (is_empty($value) && $this->empty_is_null()) {
			
		}
	}
	
	public function is_blank($value) {
	    return is_blank($value);
	}
	
	//
	// Chainable API
	
	public function default_value($value) {
		$this->default_value_set = true;
		$this->default_value = $this->coerce($value);
		return $this;
	}
	
	public function caption($caption) {
		$this->caption = $caption;
		return $this;
	}
	
	public function note($note) {
		$this->note = $note;
		return $this;
	}
	
	public function filter($filters) {
		$args = func_get_args();
		array_shift($args);
		
		if (is_string($filters)) $filters = explode(',', $filters);
		
		foreach ((array) $filters as $filter) {
			if (is_lambda($filter)) {
				$this->filters[] = array('lambda' => $filter, 'args' => $args);
			} else {
				$this->filters[] = array('method' => $filter, 'args' => $args);
			}
		}
		
		return $this;
	}
	
	public function empty_is_null() { $this->filter('empty_is_null'); return $this; }
	public function blank_is_null() { $this->filter('blank_is_null'); return $this; }
	
	public function validate($validator, $options = array()) {
		if (is_scalar($options)) $options = array('value' => $options);
		if (is_lambda($validator)) {
			$this->validations[] = array('lambda' => $validator, 'options' => $options);
		} else {
			$this->validations[] = array('method' => $validator, 'options' => $options);
		}
		return $this;
	}
	
	public function required() { $this->validate('required'); $this->required = true; return $this; }
	
	//
	// Accessors
	
	public function get_name() { return $this->name; }
	public function is_required() { return $this->required; }
	
	public function get_caption() {
		if ($this->caption === null) {
			return \Inflector::humanize($this->name);
		} else {
			return $this->caption;
		}
	}
	
	public function has_note() { return strlen($this->note) > 0; }
	public function get_note() { return $this->note; }
	
	public abstract function get_type_default_value();
	
	public function get_default_value() {
		if ($this->default_value_set) {
			return $this->default_value;
		} else {
			return $this->get_type_default_value();
		}
	}
	
	//
	// Filtering
	
	public function filter_value($value) {
		foreach ($this->filters as $filter) {
			if (isset($filter['lambda'])) {
				$value = call_user_func_array($filter['lambda'], $filter['args']);
			} else {
				$value = call_user_func_array(array($this, 'filter_' . $filter['method']), $filter['args']);
			}
		}
		return $value;
	}
	
	public function filter_empty_is_null($vlaue) { return is_empty($value) ? null : $value; }
	public function filter_blank_is_null($value) { return is_blank($value) ? null : $value; }
	
	//
	// Validation
	
	/**
	 * Returns true if $value is valid.
	 *
	 * @param $value value to validate
	 * @param $row row from value originates. In case you need to base validation on value of other field.
	 * @return true if $value is valid, string or array of strings indicating error messages if invalid
	 */
	public function is_valid($value, &$row) {
		$errors = array();
		foreach ($this->validations as $validator) {
			if (isset($validator['lambda'])) {
				$result = $validator['lambda']($value, $row, $validator['options']);
			} else {
				$method = 'validate_' . $validator['method'];
				$result = $this->$method($value, $row, $validator['options']);
			}
			if ($result !== true) {
				$errors[] = $result;
			}
		}
		return empty($errors) ? true : $errors;
	}
	
	public function validate_required($v, &$r) {
		return $this->is_blank($v) ? 'cannot be blank' : true;
	}
	
	public function validate_inclusion($v, &$r, $options) {
		$subject = $options['collection'];
		if (is_callable($subject)) $subject = $subject();
		if (is_array($subject)) {
			$result = in_array($v, $subject);
		} else { // assume object!
			$result = $subject->includes($v);
		}
		return $result ? true : 'is not included in the list';
	}
	
	public function validate_exlcusion($v, &$r, $options) {
		$result = $this->validate_inclusion($v, $r, $subject);
		return is_string($result) ? true : 'is included in the list';
	}
}

class BooleanField extends Field
{
	public function coerce_form_value($v) { return $v ? true : false; }
	public function get_type_default_value() { return false; }
	
	public function validate_acceptance($v, &$r) { return $v ? true : 'must be accepted'; }
}

class DateField extends Field
{
	public function coerce_form_value($v) { return \Date::from_request($v); }
	public function get_type_default_value() { return new \Date; }
}

class DateTimeField extends Field
{
	public function coerce_form_value($v) { return \Date_Time::from_request($v); }
	public function get_type_default_value() { return new \Date_Time; }
}

abstract class NumericField extends Field
{
	protected function filter_abs($v) { return abs($v); }
	protected function filter_floor($v) { return floor($v); }
	protected function filter_ceil($v) { return ceil($v); }
	
	protected function validate_gt($v, &$r, $options) { return $v > $options['value'] ? true : "must be greater than $options[value]"; }
	protected function validate_gte($v, &$r, $options) { return $v >= $options['value'] ? true : "must be greater than or equal to $options[value]"; }
	protected function validate_lt($v, &$r, $options) { return $v < $options['value'] ? true : "must be less than $options[value]"; }
	protected function validate_lte($v, &$r, $options) { return $v <= $options['value'] ? true : "must be less than or equal to $options[value]"; }
}

class FloatField extends NumericField
{
	public function coerce_form_value($v) { return (float) $v; }
	public function get_type_default_value() { return 0.0; }
}

class IntegerField extends NumericField
{
	public function coerce_form_value($v) { return (int) $v; }
	public function get_type_default_value() { return 0; }
}

class StringField extends Field
{
	public function coerce_form_value($v) { return (string) $v; }
	public function get_type_default_value() { return ''; }
	
	protected function validate_email($v, $options) {
		$options += array('use_dns' => false);
		return is_email($v, $options['use_dns']);
	}
	
	protected function filter_trim($v) { return trim($v); }
	protected function filter_ltrim($v) { return ltrim($v); }
	protected function filter_rtrim($v) { return rtrim($v); }
	protected function filter_max_length($v, $len) { return substr($v, 0, $len); }
	protected function filter_uppercase($v) { return strtoupper($v); }
	protected function filter_lowercase($v) { return strtolower($v); }
	
	protected function validate_matches($v, $options) { return preg_match($options['value'], $v) ? true : 'does not match'; }
	protected function validate_longer_than($v, $options) { return strlen($v) > $options['value'] ? true : "must be longer than $options[value] characters"; }
	protected function validate_shorter_than($v, $options) { return strlen($v) < $options['value'] ? true : "must be shorter than $options[value] characters"; }
}
?>