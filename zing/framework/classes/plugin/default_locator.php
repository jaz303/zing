<?php
namespace zing\plugin;

class DefaultLocator
{
    public function locate_plugins() {
        
        $plugins = array();
        
        foreach ($this->plugin_paths() as $pp) {
            foreach (glob($pp . '/*', GLOB_ONLYDIR) as $dir) {
                $plugin_php  = $dir . '/plugin.php';
                $plugin_json = $dir . '/plugin.json';
                
                if (Utils::is_plugin($dir)) {
                    try {
                        $stub = new Stub($dir);
                        $plugins[$stub->id()] = $stub;
                    }  catch (InvalidPluginException $ipe) {
                        // just swallow it
                        // we're only interested in locating valid plugins,
                        // not dealing with broken ones.
                    }
                }
            }
        }
        
        return $plugins;
        
    }
    
    private function plugin_paths() {
        return array(ZING_PLUGIN_DIR);
    }
}
?>