<?php
namespace zing\plugin;

class Manager
{
    protected $stubs = null;
    
    public function plugins() {
        $this->locate();
        $plugins = array();
        foreach ($this->stubs as $stub) {
            $plugins[] = $stub->plugin();
        }
        return $plugins;
    }
    
    public function plugin_classes() {
        $this->locate();
        $plugin_classes = array();
        foreach ($this->stubs as $stub) {
            $plugin_classes[] = $stub->class_name();
        }
        return $plugin_classes;
    }
    
    public function install(PluginStub $plugin_stub) {
        
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
    
    private function locate() {
        if ($this->stubs === null) {
            $locator = new Locator;
            $this->stubs = $locator->locate_plugins();
        }
    }
}
?>