<?php
namespace admin\cms\table_editing;

class AbstractController extends \zing\cms\admin\BaseController
{
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
            $this->collection = $this->find_all($page, $rpp);
        }
        
        $this->render('view', '/admin/cms/table_editing/index');
        
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
    
    public function find_all($page, $rpp) {
        $res = \GDB::instance()->q($this->sql_for_find_all());
        if ($rpp) $res->paginate($rpp, $page);
        return $res;
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
    
    
    
    
    
    
    
}
?>