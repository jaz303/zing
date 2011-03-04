<?php
namespace zing\plugin;

class PluginNotFoundException extends \Exception {}
class DuplicatePluginException extends \Exception {}

class Manager
{
    private static $instance = null;
    
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }
    
    private $plugin_stubs = null;
    
    public function is_plugin_installed($plugin_id) {
        $this->locate();
        return isset($this->plugin_stubs[$plugin_id]);
    }
    
    public function rescan() {
        $this->plugin_stubs = null;
        $this->locate();
    }
    
    public function stubs() {
        $this->locate();
        return $this->plugin_stubs;
    }
    
    public function load_all() {
        $this->locate();
        foreach ($this->plugin_stubs as $stub) {
            $stub->plugin();
        }
    }
    
    public function plugin($plugin_id) {
        $this->locate();
        if (!isset($this->plugin_stubs[$plugin_id])) {
            throw new PluginNotFoundException("plugin not found - $plugin_id");
        }
        return $this->plugin_stubs[$plugin_id]->plugin();
    }
    
    public function plugins() {
        $this->locate();
        $plugins = array();
        foreach ($this->plugin_stubs as $stub) {
            $plugins[$stub->id()] = $stub->plugin();
        }
        return $plugins;
    }
    
    //
    //
    
    protected function locate() {
        if ($this->plugin_stubs === null) {
            $this->plugin_stubs = array();
            foreach ($GLOBALS['_ZING']['zing.plugin.locators'] as $locator_class) {
                $locator = new $locator_class;
                foreach ($locator->locate_plugins() as $stub) {
                    if (isset($this->plugin_stubs[$stub->id()])) {
                        throw new DuplicatePluginException("duplicate plugin - {$stub->id()}");
                    }
                    $this->plugin_stubs[$stub->id()] = $stub;
                }
            }
        }
    }
}
?>