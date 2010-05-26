<?php
namespace zing\generator;

class DefaultLocator
{
    public function locate_generators() {
        
        $generators = array();
        
        foreach ($this->generator_paths() as $gp) {
            foreach (glob($gp . '/*.php') as $file) {
                $generator_name = preg_replace('/\.php$/', '', basename($file));
                $generators[$generator_name] = array(
                    'file' => $file,
                    'class' => \zing\lang\Introspector::first_class_in_file($file)
                );
            }
        }
        
        return $generators;
        
    }
    
    private function generator_paths() {
        
        $paths = array(ZING_ROOT . '/framework/generators');
        
        // TODO: this should delegate to plugin loading mechanism
        // (which is currently unable to provide a list of directories)
        
        $plugin_dirs = glob(ZING_PLUGIN_DIR . '/*', GLOB_ONLYDIR);
        foreach ($plugin_dirs as $pd) {
            if (!\zing\FileUtils::is_ignored_directory($pd)) {
                $generators_dir = $pd . '/generators';
                if (is_dir($generators_dir)) {
                    $paths[] = $generators_dir;
                }
            }
        }
        
        return $paths;
        
    }
}
?>