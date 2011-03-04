<?php
class AdminInflector
{
	public static function humanize($string) {
		return ucfirst(str_replace('_', ' ', $string));
	}
	
	public static function titleize($string) {
		return ucwords(self::humanize($string));
	}
}

/**
 * DRY'ing up form structure HTML
 */
class AdminFormBuilder
{
	private $sequence = 0;
	
	protected function seq() { return ++$this->sequence; }
	
	public function auto_form($spec, $data) {
		$html  = $this->start_box();
		foreach ($spec as $field => $field_spec) {
			if (!isset($field_spec['label'])) {
				$field_spec['label'] = AdminInflector::titleize($field);
			}
			if (!isset($field_spec['value'])) {
				$field_spec['value'] = $data[$field];
			}
			if (!preg_match('/hidden/', $field_spec['input'])) {
				$html .= $this->label($field_spec['label']);
			}
			
			$args = array($field, $field_spec['value']);
			if (isset($field_spec['args'])) {
				foreach ($field_spec['args'] as $arg) {
					$args[] = $arg;
				}
			}
			
			$input = $field_spec['input'];
			if (strpos($input, '::')) {
				$method = $input;
			} else {
				$method = array($this, $input);
			}
			
			$html .= call_user_func_array($method, $args);
		}
		$html .= $this->end_box();
		$html .= $this->buttons();
		return $html;
	}
	
	public function form_title($title) {
		return <<<HTML
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="50%" height="46" align="left" valign="middle"><h5>{$title}</h5></td>
    <td width="50%" height="46" align="right" valign="top"></td>
  </tr>
</table>
HTML;
	}
	
	public function start_box() {
		return "<div class='gray_box'>";
	}
	
	public function end_box() {
		return "</div>";
	}
	
	public function label($text) {
		return "<p class='formfield_title'><label>$text:</label></p>";
	}
	
	public function rich_text_input($name, $value = '') {
		return "<textarea name='$name' class='tinymce'>$value</textarea><br />";
	}
	
	public function text_input($name, $value = '') {
		$value = htmlentities($value, ENT_QUOTES);
		return $this->wrap_input("<input type='text' name='$name' value='$value' />");
	}
	
	/**
	 * Datetime input
	 * 
	 * @param $name name of form input
	 * @param $value current date, defaults to time(). Accepts timestamps and anything
	 *               parseable by strtotime().
	 */
	public function datetime_input($name, $value = null) {
		
		if ($value === null) $value = time();
		if (!is_numeric($value)) $value = strtotime($value);
		
		$formatted_value = date('d/m/Y H:i', $value);
		$value = date("c", $value);
		
		$id_input	= "dt-" . $this->seq();
		$id_display	= "dt-" . $this->seq();
		$id_button	= "dt-" . $this->seq();
		
		$html  = "<span class='x-datetime-picker'>\n";
		$html .= "  <input id='$id_display' type='text' value='$formatted_value' style='width:150px'  />\n";
		$html .= "  <input id='$id_input' type='hidden' name='$name' value='$value' />\n";
		$html .= "  <a href='#' id='$id_button'><img src='/cms/library/jscalendar-1.0/img.gif' /></a>\n";
		$html .= "</span>\n";
		
		return $html;
	
	}
	
	public function checkbox($name, $checked = false) {
		$html  = $this->hidden_input($name, '0');
		$html .= "<input type='checkbox' name='$name' value='1'";
		$html .= $checked ? ' checked="checked"' : '';
		$html .= " />";
		return $html;
	}
	
	public function hidden_input($name, $value) {
		$name = htmlspecialchars($name);
		$value = htmlspecialchars($value);
		return "<input type='hidden' name='$name' value='$value' />";
	}
	
	public function hidden_inputs(array $vals) {
		$html = "";
		foreach ($vals as $k => $v) {
			$html .= $this->hidden_input($k, $v);
		}
		return $html;
	}
	
	public function yes_no_select($name, $value) {
		return $this->select($name, $value, array('1' => 'Yes', '0' => 'No'));
	}
	
	/**
	 * Creates a <select> tag
	 *
	 * @param $name form input name
	 * @param $value current value of input (pass NULL to accept default)
	 * @param $choices array of choices, keys become option value attributes,
	 *        values become option tag contents.
	 * @return HTML string
	 */
	public function select($name, $value, $choices) {
		$out  = "<select name='$name'>\n";
		foreach ($choices as $k => $v) {
			$selected = $k == $value ? " selected='selected'" : '';
			$k = htmlspecialchars($k);
			$v = htmlspecialchars($v);
			$out .= "  <option value='$k'$selected>$v</option>\n";
		}
		$out .= "</select>\n";
		return $out;
	}
	
	/**
	 * Creates a lookup select box based on an SQL query.
	 * SQL query must project two fields, "key" and "value".
	 *
	 * @param $name form input name
	 * @param $value current value of input (pass NULL to accept default)
	 * @param $sql SQL query
	 * @return HTML string
	 */
	public function lookup($name, $value, $choices) {
		$res = mysql_query($sql) or die(mysql_error());
		$choices = array();
		while ($row = mysql_fetch_assoc($res)) {
			$choices[$row['key']] = $row['value'];
		}
		return $this->select($name, $choices, $value);
	}
	
	public function buttons() {
		return <<<HTML
<div class="next_prev_holder">
  <table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td align="left" valign="top"><label></label></td>
    <td align="right" valign="top">
      <label>
        <input name="submit" type="image" class="submit" src="/cms/library/images/submit.gif" />
      </label>
    </td>
  </tr>
</table>		
HTML;
	}
	
	public function wrap_input($html) {
		return "<p>$html</p>";
	}
}

/**
 * Subclass this and override to generate filter views for edit_content.php
 *
 */
abstract class AdminFilterView
{
	const FREE_TEXT_HINT = "free text search...";
	
	//
	// From params
	
	protected $url_vars					= null;
	protected $sort_mode				= null;
	protected $page						= null;
	protected $search_text				= null;
	
	//
	// Calculated
	
	protected $page_count;
	protected function get_page_count() { return $this->page_count; }
	
	//
	//
	
	public function __construct() {
		
		$this->url_vars = $_SERVER['QUERY_STRING'];
		$this->url_vars = preg_replace("/delete=[0-9]+\&?/","", $this->url_vars);
		
		$this->sort_mode = $_GET['sort'];
		$this->page = isset($_GET['page']) ? ((int) $_GET['page']) : 1;
		$this->search_text = isset($_GET['searchstr']) ? $_GET['searchstr'] : '';
		if ($this->search_text == self::FREE_TEXT_HINT) {
			$this->search_text = '';
		}
		
		$this->init();
	
	}
	
	protected function init() {}
	
	//
	// Parameters
	
	protected function get_url_vars() { return $this->url_vars; }
	protected function get_sort_mode() { return $this->sort_mode; }
	protected function get_page() { return $this->page; }
	protected function get_search_text() { return $this->search_text; }
	
	
	//
	// Essentials
	
	protected abstract function get_table();
	protected abstract function get_query_sql();
	
	//
	// Configuration
	
	protected function get_page_title() { return "Edit Content"; }
	
	/**
	 * Returns the text for the add new record link. Return null to disable
	 * the add new record link.
	 *
	 * @return add new record header text
	 */
	protected function get_add_title() { return "New Content"; }
	
	protected function get_blurb_text() { return "Filter the list using the drop-down menus below."; }
	
	protected function get_sort_options() { return array(); }
	protected function get_default_sort_order() { return 'id ASC'; }
	
	protected function get_free_text_search_fields() { return array(); }
	protected function has_free_text_search() { return count($this->get_free_text_search_fields()) > 0; }
	
	protected function get_column_mapping() { return array(); }
	
	protected function get_max_action_count() { return 2; }
	
	/**
	 * Returns an array of actions for the given row
	 *
	 * Each action is an array, with the following keys:
	 * action		- form action
	 * method		- form method
	 * name			- human-readable action description
	 * class		- CSS class for action button
	 * confirm		- confirmation message, false to disable
	 * args			- key/value array of extra args to pass to action handler (optional)
	 *
	 * @param $row input row
	 * @return array of actions for given row
	 */
	protected function get_actions_for_row($row) {
		return array(
			'edit' => array(
				'action'	=> 'add_edit_content.php', 
				'method'	=> 'get',
				'name'		=> 'Edit',
				'class'		=> 'edit_projectLink',
				'confirm'	=> false,
				'args'		=> array(
					'table'		=> $this->get_table(),
					'id'		=> $row['id']
				)
			),
			'delete' => array(
				'action'	=> 'act_on_content.php',
				'method'	=> 'post',
				'name'		=> 'Delete',
				'class'		=> 'remove_projectLink',
				'confirm'	=> 'Are you sure you wish to delete this item?',
				'args'		=> array(
					'action'	=> 'delete',
					'table'		=> $this->get_table(),
					'id'		=> $row['id']
				)
			),
		);
	}
	
	protected function get_rows_per_page() { return 30; }
	
	protected function get_width_for_field($key) {
		return (100 - ($this->get_max_action_count() * $this->get_width_for_action())) / count($this->get_column_mapping());
	}
	
	protected function get_width_for_action() { return 9; }
	
	protected function color_for_row($row, $count) { return $count % 2 == 0 ? '#cdecc4' : '#daf1d2'; }
	protected function format_value($field, $value, $count) { return htmlentities($value); }
	
	/**
	 * Returns an array of parameters to be appended to every URL generated inside
	 * this class.
	 *
	 * There is no need to include $_GET['table'] in this array, it is handled automatically.
	 *
	 * @return array of parameters/values names to be append to generated URLs
	 */
	protected function append_params() { return array(); }
	
	//
	//
	
	protected function header() {
		$html  = "";
		
		$html .= "<h4>{$this->get_page_title()}</h4>\n";
		
		if ($add_title = $this->get_add_title()) {
			$url = $this->rewrite_url(url_for_content_add($this->get_table()));
			$html .= "<p><a href='$url' class='newProject'>$add_title</a>";
		}
		
		$html .= "<p style='clear:both'></p>\n";
		$html .= "<p>{$this->get_blurb_text()}</p>\n";
		
		$html .= "<form name='mainform' action='{$_SERVER['PHP_SELF']}' method='get'>\n";
		$html .= "  <input type='hidden' name='table' value='{$this->get_table()}' />\n";
		
		foreach ($this->append_params() as $k => $v) {
			$k = htmlspecialchars($k);
			$v = htmlspecialchars($v);
			$html .= "  <input type='hidden' name='$k' value='$v' />\n";
		}
		
		$html .= "  <table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
		$html .= "    <tr>\n";
		
		//
		// Sort options
		
		$html .= "      <td width='30%' align='left'><label>\n";
		$html .= "        <select name='sort' onchange='document.mainform.submit();'>\n";
		
		foreach ($this->get_sort_options() as $k => $v) {
			
			$html .= "           <option value='$k'";
			if ($this->get_sort_mode() == $k) $html .= " selected='selected'";
			$html .= ">" . htmlentities($v) . "</option>\n";
			
			$k .= "_desc";
			$v .= " (z-a)";
			
			$html .= "           <option value='$k'";
			if ($this->get_sort_mode() == $k) $html .= " selected='selected'";
			$html .= ">" . htmlentities($v) . "</option>\n";
		
		}
		
		$html .= "        </select>\n";
		$html .= "      </label></td>\n";
		
		$html .= "      <td with='20%' align='left' valign='middle'>\n";
		
		//
		// Free-text search
		
		
		if ($this->has_free_text_search()) {
			$st = ($this->get_search_text()) ? htmlentities($this->get_search_text()) : self::FREE_TEXT_HINT;
			$html .= "      <td width='20%' align='left' valign='middle'>\n";
			$html .= "        <input type='text' onfocus=\"clearField('freetext','" . self::FREE_TEXT_HINT . "');\" id='freetext' value='{$st}' name='searchstr' />\n";
			$html .= "      </td>\n";
		}
		
		//
		//
		
		$html .= "      </td>\n";
		$html .= "      <td width='40%' align='left' valign='middle'>&nbsp;</td>\n";
		$html .= "    </tr>\n";
		$html .= "  </table>\n";
		
		$html .= "</form>\n"; // This tag is started in edit_content.php ...???
		
		return $html;
	}
	
	protected function body() {
		
		$html  = "<table cellpadding='0' cellspacing='0' width='100%' class='project_table'>\n";
		
		//
		// Header rows
		
		$html .= "<tr>\n";
		foreach ($this->get_column_mapping() as $key => $header) {
			$html .= "<td align='left' valign='middle' width='{$this->get_width_for_field($key)}%'><strong>$header</strong></td>\n";
		}
		if ($this->get_max_action_count() > 0) {
			$html .= "<td colspan='{$this->get_max_action_count()}' align='left' valign='middle'><strong>Actions</strong></td>\n";
		}
		$html .= "</tr>\n";
		
		//
		// Content
		
		$counter = 0;
		
		foreach ($this->rows as $row) {
			$bg = $this->color_for_row($row, $counter);
			
			$html .= "<tr>\n";
		
			foreach ($this->get_column_mapping() as $key => $__ignore__) {
				$value = $this->format_value($key, $row[$key], $counter);
				$html .= "  <td align='left' valign='middle' bgcolor='$bg'>$value</td>\n";
			}
			
			foreach ($this->get_actions_for_row($row) as $action) {
				
				$class = $action['confirm'] ? 'x-with-confirmation' : '';
				$confirmation_message = is_string($action['confirm']) ? $action['confirm'] : 'Are you sure?';
				
				$html .= "  <td align='center' valign='middle' bgcolor='$bg'>\n";
				$html .= "    <form method='{$action['method']}' action='{$action['action']}' class='$class'>\n";
				$html .= "      <span class='x-confirmation-message' style='display:none'>$confirmation_message</span>\n";
				
				if (isset($action['args'])) {
					foreach ($action['args'] as $k => $v) {
						$html .= "      <input type='hidden' name='$k' value='$v' />\n";
					}
				}
				
				foreach ($this->append_params() as $k => $v) {
					$html .= "      <input type='hidden' name='$k' value='$v' />\n";
				}
				
				$html .= "      <input type='submit' value='' class='{$action['class']}' alt='{$action['name']}' width='58' height='20' border='0' />\n";
				
				$html .= "    </form>\n";
				$html .= "  </td>\n";
			
			}
		
			$html .= "</tr>\n";
			
			$counter++;
		}
		
		//
		//
		
		$html .= "</table>\n";
		
		return $html;
		
	}
	
	protected function footer() {
		
		$html  = "";
		
		if ($this->get_page_count() > 1) {
			
			$html .= "<div class='page_numbers'>\n";
			$html .= "  <ul>\n";
			
			for ($i = 1; $i <= $this->get_page_count(); $i++) {
				$class = ($i == $this->get_page()) ? 'tabcurrent' : '';
				$html .= "    <li><a href='edit_content.php?page=$i&{$this->get_url_vars()}' class='$class'>$i</a></li>\n";
			}
			
			$html .= "  </ul>\n";
			$html .= "</div>\n";
			
		}
		
		return $html;
		
	}
	
	public function render() {
		$this->do_query();
		$html  = $this->header();
		$html .= $this->body();
		$html .= $this->footer();
		return $html;
	}
	
	protected function do_query() {
		
		$sql = $this->get_query_sql();
		
		if ($this->get_search_text()) {
			$keywords = split(' ', $this->get_search_text());
			$connector = stripos($sql, 'WHERE') === false ? 'WHERE' : 'AND';
			foreach ($keywords as $k) {
				$k = strtolower(preg_replace('/[^a-zA-Z0-9-]/', '', $k));
				$sql .= " $connector (";
				$ors = array();
				foreach ($this->get_free_text_search_fields() as $f) {
					$ors[] = "($f LIKE '%$k%')";
				}
				$sql .= implode(' OR ', $ors);
				$sql .= ")";
				$connector = 'AND';
			}
		}
		
		if ($this->get_sort_mode()) {
			$sort = $this->get_sort_mode();
			if (preg_match('/_desc$/', $sort)) {
				$sort = str_replace('_desc', ' DESC', $sort);
			}
		} else {
			$sort = $this->get_default_sort_order();
		}
		
		$res = mysql_query($sql) or die(mysql_error());
		
		$this->page_count = ceil(mysql_num_rows($res) / $this->get_rows_per_page());
		
		if ($sort) {
			$sql .= " ORDER BY $sort";
		}
		
		$start_limit = ($this->get_page() - 1) * $this->get_rows_per_page(); 
		$end_limit = $this->get_rows_per_page();
		$sql .= sprintf(" LIMIT %d, %d", $start_limit, $end_limit);
		
		$res = mysql_query($sql) or die(mysql_error());
		$this->rows = array();
		while ($row = mysql_fetch_assoc($res)) $this->rows[] = $row;
		
	}
	
	protected function rewrite_url($url) {
		foreach ($this->append_params() as $k => $v) {
			$url = url_set_param($url, $k, $v);
		}
		return $url;
	}
}

/**
 * AdminRowHandler can read and write rows to the database
 */
class AdminRowHandler
{
	private $id			= null;		// Row ID we're working with
	private $action		= null;		// Current action (defaults to "add" or "edit")
	private $data		= null;		// Data we're working with
	
	//
	// Public API
	
	// Record ID we are editing, if any
	public function row_id() { return $this->id; }
	
	// Are we working on a new record? (this is purely based on the presence of row ID)
	public function is_new_record() { $row_id = $this->row_id(); return empty($row_id); }
	
	// Returns the action the user is performing
	public function action() { return $this->action; }
	
	// Returns array of form data, or a single value
	public function data($key = null) { return $key ? $this->data[$key] : $this->data; }
	
	// Returns left column to display with this form
	public function left_column() { return 'left_column'; }
	
	//
	// Pretty necessary configuration
	
	protected function default_values() {
		return array(
			'title' => ''
		);	
	}
	
	protected function required_fields() {
		return array('title');	
	}
	
	protected function unique_fields() {
		return array('title');
	}
	
	protected function valid_fields() {
		return array('title');
	}
	
	protected function create_timestamp_field() { return null; }
	protected function update_timestamp_field() { return null; }
	
	// You can return an array here to get a free form
	public function form_spec() {
		return null;
	}
	
	//
	// Overridable defaults
	
	protected function find_row() {
		return $this->find_row_by_sql($this->table_name(), $this->row_id());
	}
	
	protected function find_row_by_sql($table, $id) {
		$id = (int) $id;
		$sql = "SELECT * FROM $table WHERE id = $id";
		return Database::instance()->firstRow($sql);
	}
	


	
	//
	// Status
	
	private $errors = array();
	private $success_message = null;
	
	protected function add_error($e) { $this->errors[] = $e; }
	public function has_errors() { return count($this->errors) > 0; }
	public function errors() { return $this->errors; }
	
	public function success_message() { return $this->success_message; }
	
	//
	// Work
	
	public function go() {
		
		$this->id = empty($_REQUEST['id']) ? null : (int) $_REQUEST['id'];
	
		if (isset($_REQUEST['action'])) {
			$this->action = $_REQUEST['action'];		
		} else {
			$this->action = $this->is_new_record() ? 'add' : 'edit';
		}
		
		$method = strtolower($_SERVER['REQUEST_METHOD']);
		
		
	
	}
	
	
	
	
	
	
	
	protected function post_data() {
		return get_magic_quotes_gpc() ? $this->remove_that_shit($_POST) : $_POST;
	}
	
	private function remove_that_shit($v) {
		if (is_array($v)) {
			return array_map(array($this, 'remove_that_shit'), $v);
		} else {
			return stripslashes($v);
		}
	}
}
?>