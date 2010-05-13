<?php
namespace zing\plugin;

abstract class Plugin
{
    private $directory;
    
    public function __construct($directory) {
        $this->directory = $directory;
    }
    
    /**
     * Returns the string ID of this plugin (the basename of its directory)
     * Technically this should be unique but that isn't currently enforced.
     *
     * @return unique string ID of this plugin
     */
    public function id() { return basename($this->directory); }
    
    /**
     * Returns the version of this plugin
     */
    public abstract function version();
    
    /**
     * Returns the friendly title of this plugin
     * Defaults to the plugin's ID but you can override to return anything
     */
    public function title() { return $this->id(); }
    
    /**
     * Returns an array of attribution information for this plugin.
     * All fields are optional.
     *
     * array(
     *   'email' => 'foo@bar.com', // canonical email address for this plugin
     *   'url' => 'http://bar.com/myplugin' // canonical URL for this plugin
     *   'copyright' => '2009 Magic Lamp',
     *   'authors' => array(
     *     array('name' => 'Jason Frame',
     *           'email' => 'jason@onehackoranother.com',
     *           'url' => 'http://onehackoranother.com')
     *   )
     * )
     */
    public function attribution() { return array(); }

    /**
     * Returns an array of strings/dependency objects.
     */
    public function dependencies() { return array(); }
    
    /**
     * Returns true if this plugin has any exports of type $thing.
     * For example, a plugin exporting dashboard widgets would return true
     * to the call $plugin->has('dashboard_widgets'). Doing this instead of
     * having a method for each export future proofs plugins against changes
     * to the interface.
     *
     * A plugin claiming to export $thing must implement the corresponding
     * method get_exported_$thing()
     *
     */
    public function has_exported($thing) { return method_exists($this, "get_$thing"); }
    
    /**
     * Perform any post-install tasks here
     */
    public function post_install() {}
    
    public function get_class_path() { return $this->directory . '/classes'; }
    public function get_file_path() { return $this->directory . '/files'; }
    public function get_migration_path() { return $this->directory . '/db/migrations'; }
}
?>