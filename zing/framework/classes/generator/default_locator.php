<?php
namespace zing\generator;

class DefaultLocator
{
    public function locate_generators() {
        
        $generators = array();
        
        foreach ($this->generator_paths() as $gp) {
            foreach (glob($gp . '/*.php') as $file) {
                $generators[] = array(
                    'name'  => preg_replace('/\.php$/', '', basename($file)),
                    'file'  => $file,
                    'class' => \zing\lang\Introspector::first_class_in_file($file)
                );
            }
        }
        
        return $generators;
        
    }
    
    private function generator_paths() {
        
        $paths = array(ZING_ROOT . '/framework/generators');
        
        $pm = \zing\plugin\Manager::instance();
        foreach ($pm->plugins() as $plugin) {
            if ($plugin->has_generators()) {
                $paths[] = $plugin->generator_path();
            }
        }
        
        return $paths;
        
    }
}
?>