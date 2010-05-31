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
    private $plugin = null;
    
    public function __construct($dir) {
        
        $this->directory = $dir;
        
        $metadata_json = file_get_contents($this->directory . '/plugin.json');
        if (!$metadata_json) {
            throw new InvalidPluginException("couldn't load metadata");
        }
        
        $this->metadata = json_decode($metadata_json, true);
        if (!$this->metadata)  {
            throw new InvalidPluginException("couldn't decode JSON metadata");
        }
        
        if (!$this->metadata('id')) {
            throw new InvalidPluginException("plugin ID is missing");
        }
        
        if (!Utils::is_valid_plugin_id($this->metadata('id'))) {
            throw new InvalidPluginException("plugin ID is invalid");
        }
        
        if (!isset($this->metadata['version'])) {
            throw new InvalidPluginException("plugin version is missing");
        }
        
        $this->class_name = \zing\lang\Introspector::first_class_in_file($this->directory . '/plugin.php');
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
     * Technically this should be unique but that isn't currently enforced.
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
     * Returns an array of strings/dependency objects.
     */
    public function dependencies() { return $this->metadata('dependencies', array()); }
    
    public function metadata($key = null, $default = null) {
        if ($key === null) {
            return $this->metadata;
        } else {
            return array_key_exists($key, $this->metadata) ? $this->metadata[$key] : $default;
        }
    }
    
    public function load() {
        if (!class_exists($this->class_name)) {
            require $this->directory . '/plugin.php';
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