<?php
namespace zing\plugin;

class Manager
{
    public static function create_with_default_locator() {
        global $_ZING;
        $locator_class = $_ZING['zing.plugin.locator'];
        return new self(new $locator_class);
    }
    
    private $locator;
    private $plugin_stubs = null;
    
    public function __construct($locator) {
        $this->locator = $locator;
    }
    
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
    
    public function install(Stub $stub) {
        
        $plugin = $plugin_stub->plugin();
        
        if ($plugin->has_classes()) {
            $relative_class_path = null;
            \zing\sys\Config::add_class_path($relative_class_path);
        }
        
        if ($plugin->has_files()) {
            shell_exec("cp -R {$plugin->get_file_path()}/* " . ZING_ROOT);
        }
        
        $cmd  = "cd " . ZING_ROOT . "; ";
        $cmd .= "./script/phake core:regenerate_autoload_map";
        shell_exec($cmd);
        
        $plugin->post_install();
        
    }
    
    //
    //
    
    protected function locate() {
        if ($this->plugin_stubs === null) {
            $this->plugin_stubs = $this->locator->locate_plugins();
            ksort($this->plugin_stubs);
        }
    }
}
?>