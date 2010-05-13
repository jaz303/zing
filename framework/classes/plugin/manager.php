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
        
        $plugin_path = $plugin_stub->directory();
        
        $plugin_class_path = $plugin_path . '/classes';
        if (is_dir($plugin_class_path)) {
            $relative_class_path = null;
            \zing\sys\Config::add_class_path($relative_class_path);
        }
        
        $plugin_file_path = $plugin_path . '/files';
        if (is_dir($plugin_file_path)) {
            shell_exec("cp -R $plugin_file_path/* " . ZING_ROOT);
        }
        
        $cmd  = "cd " . ZING_ROOT . "; ";
        $cmd .= "./script/phake core:regenerate_autoload_map";
        shell_exec($cmd);
        
        $plugin = $plugin_stub->plugin();
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