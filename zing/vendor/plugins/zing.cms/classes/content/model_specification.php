<?php
namespace zing\cms\content;

abstract class ModelSpecification
{
    protected $db;
    
    private $table_name = null;
    public function table_name() {
        if ($this->table_name === null) {
            $class = get_class($this);
            $this->table_name = substr($class, strpos($class, '_'));
        }
        return $this->table_name;
    }
    
    public function human_name() {
        
    }
    
    public function human_name_plural() {
        return $this->human_name() . 's';
    }
    
    public function allowed_in_page_tree() {
        return false;
    }
    
    public function prototype() {
        return array();
        //return array(
        //  'name' => array(
        //      'field_type'        => 'string',                    // if present, gets a row in the DB
        //      'default'           => '',                          // default value
        //      'input'             => array('text_input', 1, 2, 3) // input helper
        //      'note'              => '...',                       // text to display under field in form
        //      'label'             => '...',                       // label text
        //      'title'             => '...',                       // input hint
        //      'required'          => true,
        //      'unique'            => false,                       // false | true | scope string | scope array
        //      'filter'            => 'trim_or_null | not_null | ucwords',
        //      'timestamp'         => true                         // true | 'update' | 'create'
        //  )
        // )
    }
    
    public function default_values() {
        $defaults = array();
        foreach ($this->prototype() as $k => $v) {
            if (array_key_exists('default', $v)) {
                $defaults[$k] = $v['default'];
            }
        }
        return $defaults;
    }
    
    public function required_fields() {
        $fields = array();
        foreach ($this->prototype() as $k => $v) {
            if (isset($v['required']) && $v['required']) $fields[] = $k;
        }
        return $fields;
    }
    
    public function valid_fields() {
        $fields = array();
        foreach ($this->prototype() as $k => $v) {
            if (isset($v['field_type'])) $fields[] = $k;
        }
        return $fields;
    }
    
    // TODO: unique fields
    // TODO: how this model can be used as a lookup
    // TODO: scopes
    // TODO: form renderer class
    // TODO: tab specifications... maybe
    // TODO: composite inputs
    
    public function find_row($id) {
        return $this->db->q("SELECT * FROM {$this->table_name()} WHERE id = {i}", $id)->first_row();
    }
    
    /**
     * Returns array of asset collections that can be attached to this table.
     *
     * collection_name =>
     *   title => string
     *   restrict_to => ("image" | "document" | "pdf" | "video" | "audio" | mime_type)*
     *   max => integer
     */
    public function asset_groups() {
        return array();
    }
    
    /**
     * Returns array of sections accepting content blocks
     *
     * section_name => section_title
     */
    public function content_sections() {
        return array();
    }
}

?>