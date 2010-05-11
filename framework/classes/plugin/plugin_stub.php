<?php
namespace zing\plugin;

/**
 * PluginStub represents a system plugin which has not yet been loaded.
 */
class PluginStub
{
    private $directory;
    
    public function __construct($plugin_directory) {
        $this->directory = $plugin_directory;
    }
    
    public function load() {
        require Utils::initialiser_for_path($this->directory);
    }
}
?>