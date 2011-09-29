<?php
namespace zing\cms\admin\editables;

use zing\cms\admin\helpers\URLHelper as URLHelper;

abstract class BaseController extends \zing\cms\admin\BaseController
{
    /**
     * The ID of the object we're dealing with (for update/delete operations)
     */
    public $id              = null;
    
    /**
     * The object we're dealing with (for update/delete operations)
     */
    public $object          = null;
    
    /**
     * The collection that was loaded from the database, if the current action
     * is dealing with a collection.
     *
     * This is an associative array with the following keys:
     *   rows           => array of row data
     *   page           => page number of current page
     *   per_page       => rows displayed on each page
     *   page_count     => total number of pages
     *   row_count      => total number of rows (across all pages)
     */
    public $collection      = null;
    
    /**
     * Any operation resulting in error should store error information here,
     * as an \Errors instance.
     */
    public $errors          = null;
    
    //
    // 
    
    public function _index() {
        
        $page   = (int) $this->param('page', 1);
        $rpp    = $this->param('per_page', $this->default_per_page());
        
        if ($page < 1) $page = 1;
        if (!is_numeric($rpp)) $rpp = null;
        
        $this->collection = $this->find_all($page, $rpp);
        
        $this->render('view', '/admin/cms/editables/index');
        
    }
    
    public function _create() {
        $this->object = $this->new_object();
        if ($this->request->is_post()) {
            $this->perform_create($this->param('object'));
            if (!$this->has_errors()) {
                $this->redirect_to_list('success', "New {$this->singular()} created successfully");
            }
        }
        if (!$this->has_performed()) {
            $this->render('view', '/admin/cms/editables/create');
        }
    }
    
    public function _update() {
        $this->load_object();
        if ($this->request->is_post()) {
            $this->perform_update($this->param('object'));
            if (!$this->has_errors()) {
                $this->redirect_to_list('success', ucfirst("{$this->singular()} updated successfully"));
            }
        }
        if (!$this->has_performed()) {
            $this->render('view', '/admin/cms/editables/update');
        }
    }
    
    public function _delete() {
        $this->load_object();
        if ($this->request->is_post()) {
            $this->perform_delete();
            if (!$this->has_errors()) {
                $this->redirect_to_list('success', ucfirst("{$this->singular()} deleted successfully"));
            }
        }
        if (!$this->has_performed()) {
            $this->render('view', '/admin/cms/editables/delete');
        }
    }
    
    // sanitize an object ID. return null if object ID is invalid.
    protected function sanitize_id($id) { return ((int)$id > 0) ? ((int)$id) : null; }
    
    protected function has_errors() {
        return ($this->errors !== null) && (!$this->errors->ok());
    }
    
    protected function set_error($message) {
        $this->errors = new \Errors;
        $this->errors->add_to_base($message);
    }
    
    protected function load_object() {
        $id = $this->sanitize_id($this->param('id'));
        if ($id !== null) {
            $this->object = $this->find_one($id);
            $this->id = $id;
        }
    }
    
    //
    // Object implementation
    
    // returns the unique type identifier for the object type we're editing, e.g. 'product', 'order'.
    abstract protected function type_id();
    
    // find and return objects - return format is array as per $collection, above
    abstract protected function find_all($page, $rpp);
    
    // find and return single object. should throw a NotFoundException if object cannot be found.
    abstract protected function find_one($id);
    
    // return a new object.
    abstract protected function new_object();
    
    // create implementation
    abstract protected function perform_create($params);
    
    // update implementation
    abstract protected function perform_update($params);
    
    // delete implementation
    abstract protected function perform_delete();
    
    // override this method with your model's validation logic.
    // validation errors are reported through $this->errors
    protected function validate() {}
    
    //
    // Redirection
    
    public function url_for_action($action = null, $id = null) {
        return URLHelper::admin_editable_action_url($this->type_id(), $action, $id);
    }
    
    public function url_for_list() { return $this->url_for_action(); }
    public function url_for_new() { return $this->url_for_action('create'); }
    public function url_for_edit($id) { return $this->url_for_action('update', $id); }
    public function url_for_delete($id) { return $this->url_for_action('delete', $id); }
    
    public function redirect_to_list($flash_type = null, $flash_message = null) {
        if ($flash_type) $this->flash($flash_type, $flash_message);
        $this->redirect_to($this->url_for_list());
    }
    
    //
    // Descriptions
    
    public function singular()          { return 'object'; }
    public function plural()            { return $this->singular() . 's'; }
    public function describe($object)   { return $this->singular(); }
    
    //
    // List view configuration
    
    /**
     * Returns the list view delegate for this controller. List view delegates
     * are responsible for customising the list display.
     * Default behaviour is to return the controller itself.
     */
    public function get_list_view_delegate() { return $this; }
    
    public function default_per_page() { return 25; }
    public function per_page_options() { return array('All', 10, 25, 50, 100); }
    
    public function list_row_mapping() { return array('id' => 'ID'); }
    public function format_list_value($field, $value, $index) { return htmlentities($value); }
    
    public function list_max_action_count() { return 2; }
    public function list_actions_for_object($object) {
        return array(
            'update' => array(
                'caption' => 'Edit',
                'icon'    => 'pencil',
                'method'  => 'get',
                'confirm' => false
            ),
            'delete' => array(
                'caption' => 'Delete',
                'icon'    => 'cross',
                'method'  => 'post',
                'confirm' => 'Please confirm you wish to delete this ' . $this->singular() . ':'
            )
        );
    }
    
    //
    // Form
    
    public function get_form_builder() {
        $builder = $this->create_form_builder();
        $this->configure_form($builder);
        $this->build_form($builder);
        return $builder;
    }
    
    protected function configure_form($form) {
        $form->set_context($this->object);
    }
    
    protected function build_form($form) {
        
    }
    
    public function form_builder_class() {
        return '\zing\cms\admin\helpers\StandardFormBuilder';
    }
    
    public function create_form_builder() {
        $class = $this->form_builder_class();
        $builder = new $class;
        $builder->set_action(($this->id === null) ? $this->url_for_new() : $this->url_for_edit($this->id));
        $builder->set_method('post');
        $builder->set_prefix('object');
        $builder->cancel_url($this->url_for_list());
        if ($this->has_errors()) {
            $builder->set_errors($this->errors);
        }
        return $builder;
    }
    
    //
    // Section
    
    public function section_path() {
        return "cms.content.{$this->type_id()}";
    }
}

//
// SQLTableController is an auto-editor for SQL tables

abstract class SQLTableController extends BaseController
{
    protected $table_name = null;
    
    protected function table_name() {
        if ($this->table_name === null) $this->table_name = $this->infer_table_name();
        return $this->table_name;
    }
    
    protected function infer_table_name() {
        return str_replace('_controller', '', zing_class_name($this));
    }
    
    protected function type_id() {
        return $this->table_name();
    }
    
    protected function db() {
        return \GDB::instance();
    }
    
    // find and return objects - return format is array as per $collection, above
    protected function find_all($page, $rpp) {
        $res = \GDB::instance()->q($this->sql_for_find_all());
        if ($rpp) $res->paginate($rpp, $page);
        return $this->collection_for_gdb_result($res);
    }
    
    /**
     * Converts a GDBResult into a collection suitable for use in the views
     *
     * @param $res GDBResult to convert to a collection array
     * @return collection array
     */
    protected function &collection_for_gdb_result($res) {
        
        $collection = array(
            'rows'          => $res->stack(),
            'page'          => $res->page(),
            'per_page'      => $res->rpp(),
            'page_count'    => $res->page_count(),
            'row_count'     => $res->row_count()
        );
        
        // TODO: filter support
        
        $ids = array();
        foreach ($collection['rows'] as &$row) {
            $ids[] = $row['id'];
        }
        
        $this->after_find_collection($collection, $ids);
        
        return $collection;
    
    }
    
    // find and return single object. should throw a NotFoundException if object cannot be found.
    protected function find_one($id) {
        $row = $this->db()
                    ->q($this->sql_for_find_one($id))
                    ->first_row();
                    
        if (!$row) {
            throw new \NotFoundException("Couldn't find row with ID=" . $id);
        } else {
            return $row;
        }
    }
    
    // return a new object.
    protected function new_object() {
        return array();
    }
    
    // create implementation
    protected function perform_create($params) {
        $this->object = array_merge($this->object, $params);
        $this->errors = new \Errors;
        $this->validate();
        if (!$this->has_errors()) {
            $this->exec_sql($this->sql_for_insert($this->object));
        }
    }
    
    // update implementation
    protected function perform_update($params) {
        $this->object = array_merge($this->object, $params);
        $this->errors = new \Errors;
        $this->validate();
        if (!$this->has_errors()) {
            $this->exec_sql($this->sql_for_update($this->id, $this->object));
        }
    }
    
    // delete implementation
    protected function perform_delete() {
        $this->exec_sql($this->sql_for_delete_row($this->id));
    }
    
    //
    // SQL generation
    
    protected function sql_for_find_one($id) {
        return "SELECT * FROM {$this->table_name()} WHERE id = " . (int) $id;
    }
    
    protected function sql_for_find_all() {
        return "SELECT * FROM {$this->table_name()}";
    }
    
    protected function sql_for_insert($object) {
        $values = $this->quoted_attributes($object);
        $fields = implode(', ', array_keys($values));
        $values = implode(', ', array_values($values));
        return "INSERT INTO {$this->table_name()} ($fields) VALUES ($values)";
    }
    
    protected function sql_for_update($id, $object) {
        $values = $this->quoted_attributes($object);
        $updates = array();
        foreach ($values as $k => $v) $updates[] = "$k = $v";
        return "UPDATE {$this->table_name()} SET " . implode(', ', $updates) . " WHERE id = " . (int) $id;
    }
    
    protected function quoted_attributes($object) {
        $out = array();
        foreach ($this->persisted_attributes() as $attribute => $type) {
            if (array_key_exists($attribute, $object)) {
                $out["{$type}:{$attribute}"] = $object[$attribute];
            }
        }
        return $this->db()->auto_quote_array($out);
    }
    
    protected function persisted_attributes() {
        return array();
    }
    
    /**
     * Returns the SQL to delete a single record.
     * You can return an array of queries if you need to delete rows from associated
     * tables.
     */
    protected function sql_for_delete_row($id) {
        return "DELETE FROM {$this->table_name()} WHERE id = " . (int) $id;
    }
    
    //
    // SQL exec
    
    protected function exec_sql($sql) {
        $db = $this->db();
        $db->transaction(function() use($db, $sql) {
            foreach ((array) $sql as $query) $db->x($query);
        });
    }
    
    //
    // Overridable Callbacks
    
    /**
     * Post-process a single row after loading it from the DB.
     * You can override this method to add/format the raw row data.
     *
     * @param $row row to process
     */
    protected function after_find(array &$row) { }
    
    /**
     * Post-process a collection after loading it from the DB.
     * You can override this methods to add/format the raw row data.
     * 
     * @param $collection collection array
     * @param $ids convenience array of row IDs, e.g. for retrieving associated rows in a single query
     */
    protected function after_find_collection(array &$collection, array $ids) { }
    
    //
    //
    
    protected function collection_select_options($table, $options = array()) {
        return \zing\cms\admin\helpers\BaseHelper::collection_select_options($table, $options);
    }
}
?>