<?php
namespace zing\plugin;

/**
 * PluginStub represents a system plugin which has not yet been loaded.
 */
class PluginStub
{
    public static function initialiser_for_path($plugin_path) {
        $candidate = $plugin_path . '/plugin.php';
        return is_file($candidate) ? $candidate : null;
    }
    
    public static function is_plugin($plugin_path) {
        return file_exists(self::initialiser_for_path($plugin_path));
    }
    
    private $directory;
    private $class_name;
    
    public function __construct($plugin_directory) {
        $this->directory    = $plugin_directory;
        $this->class_name   = '';
        
        $initialiser = self::initialiser_for_path($this->directory);
        foreach (file($initialiser) as $line) {
            if (preg_match('/namespace\s+([^;{\s]+)/', $line, $matches)) {
                $this->class_name .= $matches[1] . '\\';
            } elseif (preg_match('/class\s+([^{\s]+)/', $line, $matches)) {
                $this->class_name .= $matches[1];
            }
        }
        
        if (!$this->class_name) {
            throw new Exception("expected $initialiser to define a plugin class");
        }
        
        if ($this->class_name[0] != '\\') {
            $this->class_name = '\\' . $this->class_name;
        }
    }
    
    public function directory() { return $this->directory; }
    public function class_name() { return $this->class_name; }
    
    public function load() {
        if (!class_exists($this->class)) {
            require self::initialiser_for_path($this->directory);
        }
    }
    
    public function plugin() {
        $this->load();
        $class = $this->class_name;
        return new $class($this->directory);
    }
}
?>