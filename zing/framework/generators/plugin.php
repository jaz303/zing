<?php
namespace zing\generator;

class PluginGenerator extends \zing\generator\Generator
{
    public function description() {
        return "Create a skeleton plugin";
    }
    
    protected function parse_args(array $args) {
        if (count($args) < 1) {
            throw new \InvalidArgumentException("Usage: script/generate plugin plugin_id [plugin_title]");
        }
        
        $this->plugin_id        = array_shift($args);
        $this->plugin_class     = str_replace('-', '_', \Inflector::camelize(str_replace('.', '_', $this->plugin_id)));
        $this->plugin_title     = count($args) ? array_shift($args) : $this->plugin_id;
        
        // TODO: this path probably shouldn't be hardcoded
        $this->plugin_dir       = 'vendor/plugins/' . $this->plugin_id;
        
        if (!\zing\plugin\Utils::is_valid_plugin_id($this->plugin_id)) {
            throw new \InvalidArgumentException("'{$this->plugin_id}' is not a valid plugin ID");
        }
        
        $manager = \zing\plugin\Manager::create_with_default_locator();
        if ($manager->is_plugin_installed($this->plugin_id)) {
            throw new \InvalidArgumentException("'{$this->plugin_id}' is already installed");
        }
    }

    protected function manifest() {
        return array(
            $this->plugin_dir . '/plugin.json'      => $this->dir . '/plugin_template/plugin.json',
            $this->plugin_dir . '/plugin.php'       => $this->dir . '/plugin_template/plugin.php',
            $this->plugin_dir . '/README'           => $this->dir . '/plugin_template/README',
            $this->plugin_dir . '/classes/'         => true,
            $this->plugin_dir . '/files/'           => true,
            $this->plugin_dir . '/db/migrations/'   => true
        );
    }
}
?>