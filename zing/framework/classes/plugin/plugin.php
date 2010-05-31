<?php
namespace zing\plugin;

abstract class Plugin
{
    protected $stub;
    protected $directory;
    
    public function __construct(Stub $stub) {
        $this->stub = $stub;
        $this->directory = $this->stub->directory();
    }
    
    public function id() { return $this->stub->id(); }
    public function version() { return $this->stub->version(); }
    public function title() { return $this->stub->title(); }
    public function dependencies() { return $this->stub->dependencies(); }
    public function metadata($key = null, $default = null) { return $this->stub->metadata($key, $default); }

    /**
     * Returns true if this plugin has any exports of type $thing.
     * For example, a plugin exporting dashboard widgets would return true
     * to the call $plugin->has_exported('dashboard_widgets'). Doing this instead of
     * having a method for each export future proofs plugins against changes
     * to the interface.
     *
     * A plugin claiming to export $thing must implement the corresponding
     * method get_$thing()
     *
     */
    public function has_exported($thing) { return method_exists($this, "get_$thing"); }
    
    public function get_class_paths() { return array($this->directory . '/classes'); }
    public function get_file_path() { return $this->directory . '/files'; }
    public function get_migration_path() { return $this->directory . '/db/migrations'; }
    
    public function has_classes() {
        foreach ($this->get_class_paths() as $path) {
            if (is_dir($path)) return true;
        }
        return false;
    }
    
    public function has_files() { return is_dir($this->get_file_path()); }
    public function has_migrations() { return is_dir($this->get_migration_path()); }
    
    /**
     * Perform any post-install tasks here
     */
    public function post_install() {}
}
?>