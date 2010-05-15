<?php
namespace zing\plugin;

class PluginException extends \Exception {}

abstract class Plugin
{
    private $directory;
    private $metadata = null;
    
    public function __construct($directory) {
        $this->directory = $directory;
    }
    
    /**
     * Returns the string ID of this plugin (the basename of its directory)
     * Technically this should be unique but that isn't currently enforced.
     *
     * @return unique string ID of this plugin
     */
    public function id() { return basename($this->directory); }
    
    public function metadata($key = null, $default = null) {
        if ($this->metadata === null) {
            $json = file_get_contents($this->directory . '/plugin.json');
            if (!$json || !($meta = json_decode($json, true))) {
                throw new PluginException("can't get metadata for plugin {$this->id()}");
            }
            if (!isset($meta['version'])) {
                throw new PluginException("plugin metadata missing required version attribute");
            }
            $this->metadata = $meta;
        }
        if ($key === null) {
            return $this->metadata;
        } else {
            return array_key_exists($key, $this->metadata) ? $this->metadata[$key] : $default;
        }
    }
    
    /**
     * Returns the version of this plugin
     */
    public function version() { return $this->metadata('version'); }
    
    /**
     * Returns the friendly title of this plugin
     * Defaults to the plugin's ID but you can override to return anything
     */
    public function title() { return $this->metadata('title', $this->id()); }

    /**
     * Returns an array of strings/dependency objects.
     */
    public function dependencies() { return $this->metadata('dependencies', array()); }
    
    /**
     * Returns true if this plugin has any exports of type $thing.
     * For example, a plugin exporting dashboard widgets would return true
     * to the call $plugin->has('dashboard_widgets'). Doing this instead of
     * having a method for each export future proofs plugins against changes
     * to the interface.
     *
     * A plugin claiming to export $thing must implement the corresponding
     * method get_exported_$thing()
     *
     */
    public function has_exported($thing) { return method_exists($this, "get_$thing"); }
    
    /**
     * Perform any post-install tasks here
     */
    public function post_install() {}
    
    public function get_class_path() { return $this->directory . '/classes'; }
    public function get_file_path() { return $this->directory . '/files'; }
    public function get_migration_path() { return $this->directory . '/db/migrations'; }
    
    public function has_classes() { return is_dir($this->get_class_path()); }
    public function has_files() { return is_dir($this->get_file_path()); }
    public function has_migrations() { return is_dir($this->get_migration_path()); }
}
?>