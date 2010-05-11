<?php
namespace zing\plugin;

class Initialiser
{
    public function init() {
        
        $this->locate();
        
        foreach (Utils::declared_plugin_classes() as $plugin_class) {
            echo "$plugin_class\n";
        }
        
    }
    
    protected function locate() {
        $locator = new Locator;
        foreach ($locator->locate_plugins() as $plugin_stub) {
            $plugin_stub->load();
        }
    }
}
?>