<?php
namespace zing\cms\content;

// TODO: link node to script and/or controller/action
// TODO: ability to protect nodes to prevent renaming

class Node
{
    private static $db          = null;
    
    public static function find($id) {
        $res = self::$db->q("SELECT * FROM zing_cms_node WHERE id = {i}", $id);
        return new self($res->first_row());
    }
    
    public static function root() {
        $res = self::$db->q("SELECT * FROM zing_cms_node WHERE parent_id IS NULL");
        return new self($res->first_row());
    }
    
    public static function find_by_path($path) {
        
    }
    
    /**
     * This is where the magic happens. You can query you content using CSS selectors.
     * 
     *
     */
    public static function query($query) {
        
    }
    
    private $id                 = null;
    
    private $parent_id          = null;
    private $slug               = '';
    
    private $original_parent_id;
    private $original_slug;
    
    private $translation_root   = false;
    private $locale_code        = null;
    
    private $child_sort_field   = 'position';     // title | created_at | updated_at | published_at | position
    private $child_sort_reverse = false;
    
    private $title              = '';
    private $medium_title       = '';
    private $short_title        = '';
    
    private $position           = 0;
    private $published_at       = null;
    private $created_at         = null;
    private $updated_at         = null;
    
    private $page_table         = null;
    private $page_id            = null;
    
    private $path;                              // calculated
    private $depth;                             // calculated
    
    private $parent             = null;         // cached
    private $page               = null;         // cached
    
    //
    //
    
    public function get_id() { return $this->id; }
    private function set_id($i) { $this->id = $i === null ? null : (int) $i; }
    
    //
    //
    
    public function get_parent_id() { return $this->parent_id; }
    public function get_slug() { return $this->slug; }
    
    public function set_parent_id($p) { $this->parent_id = $p === null ? null : (int) $p; $this->original_parent_id = $this->parent_id; $this->parent = null; }
    public function set_slug($s) { $this->slug = trim($s); $this->original_slug = $this->slug; }
    
    //
    //
    
    public function is_translation_root() { return $this->translation_root; }
    public function get_locale_code() { return $this->locale_code; }
    
    public function set_translation_root($t) { $this->translation_root = (bool) $t; }
    public function set_locale_code($lc) { $this->locale_code = trim($lc); if (empty($this->locale_code)) $this->locale_code = null; } 
    
    //
    //
    
    public function get_child_sort_field() { return $this->child_sort_field; }
    public function is_child_sort_reverse() { return $this->child_sort_reverse; }
    
    public function set_child_sort_field($f) { $this->child_sort_field = trim($f); }
    public function set_child_sort_reverse($r) { $this->child_sort_reverse = (bool) $r; }
    
    //
    //
    
    public function get_title() { return $this->title; }
    public function get_medium_title() { return $this->medium_title; }
    public function get_short_title() { return $this->short_title; }
    
    public function set_title($t) { $this->title = trim($t); }
    public function set_medium_title($t) { $this->medium_title = trim($t); }
    public function set_short_title($t) { $this->short_title = trim($t); }
    
    //
    //
    
    public function get_position() { return $this->position; }
    public function get_published_at() { return $this->published_at; }
    public function get_created_at() { return $this->created_at; }
    public function get_updated_at() { return $this->updated_at; }
    
    public function set_position($p) { $this->position = (int) $p; }
    public function set_published_at($p) { $this->published_at = \Date_Time::parse($p); }
    private function set_created_at($c) { $this->created_at = \Date_Time::parse($c); }
    private function set_updated_at($u) { $this->created_at = \Date_Time::parse($u); }
    
    //
    // Page
    
    public function get_page_table() { return $this->page_table; }
    public function get_page_id() { return $this->page_id; }
    
    public function get_page() {
        if ($this->page === null) {
            if ($this->page_table && $this->page_id) {
                $this->page = self::$db->q("SELECT * FROM {$this->page_table} WHERE id = {i}", $this->page_id)->first();
            }
        }
        return $this->page;
    }
    
    public function set_page_table($page_table) { $this->page_table = $page_table; $this->page = null; }
    public function set_page_id($page_id) { $this->page_id = $page_id; $this->page = null; }
    
    public function __construct($row = null) {
        if ($this->row === null) {
            $this->set_attributes($row);
        }
    }
    
    public function set_attributes(array $attributes) {
        
        
        
    }
    
    //
    // Persistence
    
    public function save() {
        if (!$this->is_valid()) {
            die('invalid'); // TODO: throw correct exception
        }
        if ($this->id === null) {
            $this->created_at = new Date_Time;
            
        } else {
            $this->updated_at = new Date_Time;
        }
    }
    
    public function delete() {
        foreach ($this->children() as $child) $child->delete();
        self::$db->x("DELETE FROM zing_cms_node WHERE id = {i}", $this->id);
        $this->id = null;
    }
    
    //
    // Validations
    
    private $errors;
    
    public function get_errors() { return $this->errors; }
    
    public function is_valid() {
        
        $this->errors = new \Errors;
        
        if ($this->parent_id === null) {
            $this->slug = '';
        }
        
        if ($this->translation_root && strlen($this->locale_code)) {
            $this->errors->add_to_base('Translation root and locale cannot both be specificed for a node');
        }
        
        if (!in_array($this->child_sort_field, array('title', 'created_at', 'updated_at', 'published_at', 'position'))) {
            $this->errors->add('child_sort_field', 'is not in the list');
        }
        
        if (strlen($this->title) == 0) {
            $this->errors->add('title', 'cannot be blank');
        }
        
        if (!preg_match('/^\w+$/', $this->page_table)) {
            $this->errors->add('page_table', 'is invalid');
        } elseif (!self::$db->new_schema_builder()->table_exists($this->page_table)) {
            $this->errors->add('page_table', 'is invalid');
        }
        
        return $this->errors->ok();
        
    }
    
    //
    //
    
    public function parent() {
        if ($this->parent_id === null) {
            return null;
        } elseif ($this->parent === null) {
            $this->parent = self::find($this->parent_id);
        }
        return $this->parent;
    }
    
    public function children($sort_field = null, $sort_reverse = null) {
        if ($sort_field === null)   $sort_field = $this->child_sort_field;
        if ($sort_reverse === null) $sort_reverse = $this->child_sort_reverse;
        $direction = $sort_reverse ? 'DESC' : 'ASC';
        return self::$db->q("
            SELECT * FROM zing_cms_node WHERE parent_id = {i} ORDER BY $sort_field $direction
        ", $this->id)->mode('object', '\\zing\\cms\\content\\Node')->stack();
    }
    
    public function node_with_relative_path($path) {
        
    }
    
    //
    //
    
    public function translate($language_code) {
        
    }
    
}

?>