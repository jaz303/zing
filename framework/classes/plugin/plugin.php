<?php
namespace zing\plugin;

interface Plugin
{
    /**
     * Returns the unique string ID of this plugin
     *
     * @return unique string ID of this plugin
     */
    public function id();
    
    /**
     * Returns the version of this plugin
     */
    public function version();
    
    /**
     * Returns the friendly title of this plugin
     */
    public function title();
    
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
    public function attribution();
 
    /**
     * Returns an array of strings/dependency objects.
     */
    public function dependencies();
    
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
    public function has($thing);
    
}
?>