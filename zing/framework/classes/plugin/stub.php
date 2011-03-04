<?php
namespace zing\plugin;

class InvalidPluginException extends \Exception {}

/**
 * Stub represents a system plugin which has not yet been loaded.
 */
class Stub
{
    private $directory;
    private $class_name;
    private $metadata;
    private $plugin         = null;
    private $dependencies   = null;
    
    public function __construct($dir) {
        
        $this->directory = $dir;
        
        $metadata_json = file_get_contents($this->directory . '/zing/plugin.json');
        if ($metadata_json === false) {
            throw new InvalidPluginException("Error loading plugin metadata");
        }
        
        $this->metadata = json_decode($metadata_json, true);
        if (!$this->metadata)  {
            throw new InvalidPluginException("Error decoding JSON metadata");
        }
        
        if (!$this->metadata('id')) {
            throw new InvalidPluginException("Plugin ID not found");
        }
        
        if (!Utils::is_valid_plugin_id($this->metadata('id'))) {
            throw new InvalidPluginException("Plugin ID is invalid");
        }
        
        if (!isset($this->metadata['version'])) {
            throw new InvalidPluginException("Plugin version is missing");
        }
        
        $this->class_name = \zing\lang\Introspector::first_class_in_file($this->directory . '/zing/plugin.php');
        if (!$this->class_name) {
            throw new InvalidPluginException("plugin primary class is missing");
        }
        
    }
    
    /**
     * Returns the root directory of this plugin
     */
    public function directory() { return $this->directory; }
    
    /**
     * Returns the primary class name of this plugin
     */
    public function class_name() { return $this->class_name; }
    
    /**
     * Returns the string ID of this plugin (the basename of its directory)
     *
     * @return unique string ID of this plugin
     */
    public function id() { return $this->metadata('id'); }
    
    /**
     * Returns the version of this plugin
     */
    public function version() { return $this->metadata('version'); }
    
    /**
     * Returns the friendly title of this plugin
     */
    public function title() { return $this->metadata('title', $this->id()); }
    
    /**
     * Returns an array of the authors of this plugin
     */
    public function authors() { return $this->metadata('authors', array()); }

    /**
     * Returns an array of Dependency objects.
     */
    public function dependencies() {
        if ($this->dependencies === null) {
            $this->dependencies = array_map(function($string_dep) {
                return new Dependency($string_dep);
            }, $this->metadata('dependencies', array()));
        }
        return $this->dependencies;
    }
    
    public function metadata($key = null, $default = null) {
        if ($key === null) {
            return $this->metadata;
        } else {
            return array_key_exists($key, $this->metadata) ? $this->metadata[$key] : $default;
        }
    }
    
    public function load() {
        if (!class_exists($this->class_name)) {
            require $this->directory . '/zing/plugin.php';
        }
    }
    
    public function plugin() {
        if ($this->plugin === null) {
            $this->load();
            $class = $this->class_name;
            $this->plugin = new $class($this);
        }
        return $this->plugin;
    }
}
?>