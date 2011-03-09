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
    
    public function directory() { return $this->stub->directory(); }
    public function id() { return $this->stub->id(); }
    public function version() { return $this->stub->version(); }
    public function title() { return $this->stub->title(); }
    public function authors() { return $this->stub->authors(); }
    public function copyright() { return $this->stub->copyright(); }
    public function url() { return $this->stub->url(); }
    public function email() { return $this->stub->email(); }
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
    
    public function candidate_class_paths() { return 'classes'; }
    public function candidate_file_path() { return 'files'; }
    public function candidate_migration_path() { return 'db/migrations'; }
    public function candidate_generator_path() { return 'generators'; }
    
    public function class_paths() {
        $out = array();
        foreach ((array) $this->candidate_class_paths() as $ccp) {
            $ccp = $this->directory . DIRECTORY_SEPARATOR . $ccp;
            if (is_dir($ccp)) $out[] = $ccp;
        }
        return $out;
    }
    
    public function file_path() {
        $cfp = $this->directory . DIRECTORY_SEPARATOR . $this->candidate_file_path();
        return is_dir($cfp) ? $cfp : null;
    }
    
    public function migration_path() {
        $cmp = $this->directory . DIRECTORY_SEPARATOR . $this->candidate_migration_path();
        return is_dir($cmp) ? $cmp : null;
    }
    
    public function generator_path() {
        $cgp = $this->directory . DIRECTORY_SEPARATOR . $this->candidate_generator_path();
        return is_dir($cgp) ? $cgp : null;
    }
    
    public function has_classes() { return count($this->class_paths()) > 0; }
    public function has_files() { return $this->file_path() !== null; }
    public function has_migrations() { return $this->migration_path() !== null; }
    public function has_generators() { return $this->generator_path() !== null; }
    
    /**
     * Perform any post-install tasks here
     */
    public function post_install() {}
}
?>