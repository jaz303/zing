<?php
namespace zing\generator;

abstract class Generator
{
    private $__name;
    private $__directory;
    
    public function name() { return $this->__name; }
    public function directory() { return $this->__directory; }
    public function description() { return "(untitled generator)"; }
    
    /**
     * @param $name name for this generator
     */
    public function set_name($name) {
        $this->__name = $name;
    }
    
    /**
     * @param $directory root directory for this generator
     */
    public function set_directory($directory) {
        $this->__directory = $directory;
    }
    
    public function invoke(array $args) {
        $this->parse_args($args);
        $this->copy_manifest();
        $this->after_copy();
        foreach ($this->tasks() as $task) {
            \zing\sys\Utils::invoke_task($task);
        }
    }
    
    /**
     * Override this method to handle arguments to your generator.
     * Assign as instance variables.
     */
    protected function parse_args(array $args) {}
    
    /**
     * Override to do extra work after files have been copied
     */
    protected function after_copy() {}
    
    /**
     * Phake tasks to run after generation
     */
    protected function tasks() {
        return array();
    }
    
    protected function copy_manifest() {
        foreach ($this->manifest() as $target => $source) {
            if ($source === true) {
                if ($target[strlen($target) - 1] == '/') {
                    mkdir_p($target);
                } else {
                    mkdir_p(dirname($target));
                    touch($target);
                }
            } else {
                $target = ZING_ROOT . '/' . ltrim($target, '/');
                mkdir_p(dirname($target));
                file_put_contents($target, $this->render_template($source));
            }
        }
    }
    
    /**
     * Return a an array of files.
     * Keys are target locations, relative to ZING_ROOT
     * Values are absolute paths to source templates
     */
    protected function manifest() {
        return array();
    }
    
    protected function render_template($path) {
        
        $content = file_get_contents($path);
        
        $content = str_replace('<?', '######o-php######', $content);
        $content = str_replace('?>', '######c-php######', $content);
        $content = str_replace('<%', '<?', $content);
        $content = str_replace('%>', '?>', $content);
        
        $content = $this->perform_render($content);
        
        $content = str_replace('######c-php######', '?>', $content);
        $content = str_replace('######o-php######', '<?', $content);
        
        return $content;
        
    }
    
    protected function perform_render($__source__) {
        foreach ($this as $k => $v) $$k = $v;
        ob_start();
        eval('?>' . $__source__);
        return ob_get_clean();
    }
}
?>