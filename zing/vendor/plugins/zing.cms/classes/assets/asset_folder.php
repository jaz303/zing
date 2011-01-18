<?php
namespace zing\cms\assets;

class AssetFolder extends \zing\cms\Model
{
    public static function table_name() { return 'asset_folder'; }
    
    public static function find_roots() {
        return static::find_all_where('parent_id IS NULL', 'title ASC');
    }
    
    private $parent_id      = null;
    private $title          = "";
    
    public function get_parent_id() { return $this->parent_id; }
    public function get_title() { return $this->title; }
    
    public function set_parent_id($p) { $this->parent_id = int_or_null($p); }
    public function set_title($t) { $this->title = trim($t); }
    
    protected function type_hinted_attributes() {
        return array(
            'i:parent_id'   => $this->parent_id,
            's:title'       => $this->title,
        );
    }
    
    public function get_children() {
        return static::find_all_where(array('parent_id = {i}', $this->get_id()), 'title ASC');
    }
    
    public function get_assets() {
        return Asset::find_all_by_folder_id($this->get_id());
    }
    
    protected function before_delete() {
        foreach ($this->get_assets() as $asset) $asset->delete();
        foreach ($this->get_children() as $child) $child->delete();
    }
    
    protected function do_validation() {
        if (is_blank($this->title)) {
            $this->errors->add('title', 'cannot be blank');
        }
    }
}
?>