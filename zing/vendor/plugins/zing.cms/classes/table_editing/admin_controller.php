<?php
namespace admin\cms\table_editing;

class AbstractController extends \zing\cms\admin\BaseController
{
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
    protected $collection = null;
    
    /**
     * The ID of the row that was loaded from the database, if the current
     * action is dealing with a single row.
     */
    protected $id = null;
    
    /**
     * The row that was loaded from the database, if the current action is
     * dealing with a single row.
     *
     * This is an associative array, mapping field => value
     */
    protected $row = null;
    
    protected function init() {
        parent::init();
        $this->table_name = $this->get_table_name();
        $this->registry   = new \zing\cms\table_editing\Registry();
    }
    
    //
    // Actions
    
    public function _index() {
        
        $scope  = $this->param('scope', null);
        $page   = (int) $this->param('page', 1);
        $rpp    = $this->param('per_page', $this->default_per_page());
        
        if ($page < 1) $page = 1;
        if (!is_numeric($rpp)) $rpp = null;
        
        if ($scope) {
            // TODO: support scopes!
        } else {
            $this->collection = &$this->find_all($page, $rpp);
        }
        
        $this->render('view', '/admin/cms/table_editing/index');
        
    }
    
    public function _create() {
        
        
        
        $this->render('view', '/admin/cms/table_editing/create');
        
    }
    
    public function _delete() {
      
        if ($this->request->is_post()) {
            if ($this->delete_row($this->param('id'))) {
                $this->redirect_to_list('success', 'Row deleted successfully');
            }
        }
        
        if (!$this->has_performed()) {
            //$this->find_data();
            //$this->render('view', '/admin/tables/delete');
        }
      
    }
    
    //
    // Flow Control
    
    public function url_for_table_action($action, $id = null) {
        // FIXME: need indirection to generate admin URLs (see admin_url() helper)
        $url = "/admin/tables/{$this->table_name}/$action";
        if ($id) $url .= "/$id";
        return $url;
    }
    
    protected function redirect_to_list($flash_type = null, $flash_message = null) {
        if ($flash_type) $this->flash($flash_type, $flash_message);
        $this->redirect_to($this->url_for_table_action('index'));
    }
    
    //
    // Configuration
    
    protected $table_name;
    
    protected function get_table_name() {
        // FIXME: only replace at end of string
        return str_replace('_controller', '', zing_class_name($this));
    }
    
    //
    // General
  
    public function singular() { return $this->table_name; }
    public function plural() { return $this->singular() . 's'; }
    public function describe_row($row) { return "the {$this->singular()}"; }
    
    //
    // List Views
  
    public function default_per_page() { return 25; }
    public function per_page_options() { return array('All', 10, 25, 50, 100); }
    
    public function list_row_mapping() { return array('id' => 'ID'); }
    public function format_list_value($field, $value, $index) { return htmlentities($value); }
    
    public function list_max_action_count() { return 2; }
    
    public function list_actions_for_row($row) {
        return array(
            'edit' => array(
                'caption' => 'Edit',
                'icon'    => 'Pencil',
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
    // Finders
    
    protected function sql_for_find_all() {
        return "SELECT * FROM {$this->table_name}";
    }
    
    public function &find_all($page, $rpp) {
        $res = \GDB::instance()->q($this->sql_for_find_all());
        if ($rpp) $res->paginate($rpp, $page);
        return $this->collection_for_result($res);
    }
    
    /**
     * Converts a GDBResult into a collection suitable for use in the views
     *
     * @param $res GDBResult to convert to a collection array
     * @return collection array
     */
    protected function &collection_for_result($res) {
        
        $collection = array(
            'rows'          => $res->stack(),
            'page'          => $page,
            'per_page'      => $res->rpp(),
            'page_count'    => $res->page_count(),
            'row_count'     => $res->row_count()
        );
        
        $ids = array();
        foreach ($collection['rows'] as &$row) {
            $ids[] = $row['id'];
        }
        
        $this->after_find_collection($collection, $ids);
        
        return $collection;
    
    }
    
    //
    // Deletion
    
    /**
     * Returns the SQL to delete a single record.
     * You can return an array of queries if you need to delete rows from associated
     * tables.
     */
    protected function sql_for_delete_row($id) {
        return "DELETE FROM {$this->table_name} WHERE id = " . (int) $id;
    }
    
    protected function delete_row($id) {
        // FIXME: handle errors gracefully
        $db = \GDB::instance();
        foreach ((array) $this->sql_for_delete_row($id) as $query) {
            $db->x($query);
        }
        return true;
    }
    
    //
    // Form Definition
    
    protected static $DEFAULT_TYPE_INPUTS = array(
        'string'                => 'text_field',
        'integer'               => 'text_field',
        'float'                 => 'text_field',
        'boolean'               => 'check_box',
        'date'                  => 'date_field',
        'date_time'             => 'date_time_field'
    );
    
    /**
     * If you want the generated form to be split into logical sections, override
     * this method to return an array of the form:
     *
     * group_id => array('title' => 'Group title', 'fields' => array('field_1', 'field_2', 'field_3'))
     */
    protected function admin_form_groups() { return null; }
    
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
    
    protected function before_validate() { }
    protected function before_validate_on_create() { }
    protected function before_validate_on_update() { }
    
    protected function after_validate_on_update() { }
    protected function after_validate_on_create() { }
    protected function after_validate() { }
    
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