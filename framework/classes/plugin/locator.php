<?php
namespace zing\plugin;

/**
 * A Locator finds all installed plugins.
 */
class Locator
{
    /**
     * Returns an array of PluginStub instances, one per installed plugin, by
     * searching the plugin roots.
     *
     * @todo recursive search
     */
    public function locate_plugins() {
        
        $plugins = array();
        
        foreach ($this->plugin_roots() as $plugin_path) {
            $dh = opendir($plugin_path);
            while (($file = readdir($dh)) !== false) {
                if ($file == '.' || $file == '..') continue;
                $abs_plugin_path = $plugin_path . '/' . $file;
                if (Utils::is_plugin($abs_plugin_path)) {
                    $plugins[] = new PluginStub($abs_plugin_path);
                }
            }
            closedir($dh);
        }
        
        return $plugins;

    }
    
    /**
     * Returns an array of all root directories in which plugins might be located.
     *
     * @todo make this configurable
     */
    public function plugin_roots() {
        return array(ZING_PLUGIN_DIR);
    }
}
?>