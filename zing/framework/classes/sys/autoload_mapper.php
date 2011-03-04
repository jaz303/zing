<?php
namespace zing\sys;

class AutoloadMapper
{
    private $root               = null;
    private $variable           = 'static $map';
    private $rules              = array();
    
    public function set_root($root) { $this->root = rtrim($root, '/') . '/'; }
    public function set_variable($variable) { $this->variable = $variable; }
    
    public function add_rule($path, $extensions = 'php') {
        if (\zing\FileUtils::is_absolute_path($path)) {
            if (!($path = \zing\FileUtils::relativize_path($path, $this->root))) {
                return;
            }
        }
        
        $this->rules[] = array(
            'path'          => rtrim($path, '/') . '/',
            'extensions'    => (array) $extensions
        );
    }
    
    public function files() {
        $files = array();
        foreach ($this->rules as $rule) {
            $stack = array($this->root . $rule['path']);
            $extensions = $rule['extensions'];
            while (count($stack)) {
                $dir = array_pop($stack);
                if (!($dh = @opendir($dir))) continue;
                while (($file = readdir($dh)) !== false) {
                    if ($file[0] == '.') continue;
                    $extension = (($p = strrpos($file, '.')) !== false) ? substr($file, $p + 1) : '';
                    $path = $dir . $file;
                    if (is_dir($path)) {
                        $stack[] = $path . '/';
                    } elseif (in_array($extension, $extensions)) {
                        $files[] = $path;
                    }
                }
                closedir($dh);
            }
        }
        return $files;
    }
    
    public function build_map() {
        $map = array();
        foreach ($this->files() as $file) {
            foreach ($this->scan_file($file) as $file_class) {
                $map[$file_class] = str_replace($this->root, '', $file);
            }
        }
        return $map;
    }
    
    public function write($file, $indent = "\t") {
        
        $declaration = $this->variable . ' = ' . var_export($this->build_map(), true) . ";\n";
        $declaration = preg_replace('/^/m', $indent, $declaration);
        
        require_once __DIR__ . '/source_block_writer.php';
        SourceBlockWriter::remove_block_from_file($file, 'zing.autoload-map');
        SourceBlockWriter::append_block_to_file($file, 'zing.autoload-map', $declaration);
    
    }
    
    // technique learned from:
    // http://stackoverflow.com/questions/928928/determining-what-classes-are-defined-in-a-php-class-file
    // (replaced with state machine to implement namespace support)
    private function scan_file($file) {
        
        $classes    = array();
        $namespace  = '';
        $state      = 'out';
        $skip_next  = false;
        
        foreach (token_get_all(file_get_contents($file)) as $token) {
            if ($token[0] == T_HALT_COMPILER) {
                break;
            }
            switch ($state) {
                case 'out':
                    if ($token[0] == T_NAMESPACE) {
                        $namespace = '';
                        $state = 'ns1';
                    } elseif ($token[0] == T_CLASS || $token[0] == T_INTERFACE) {
                        $state = 'c1';
                    } elseif ($token[0] == T_COMMENT || $token[0] == T_DOC_COMMENT) {
                        if (strpos($token[1], 'zing-autoload-ignore') !== false) {
                            $skip_next = true;
                        }
                    }
                    break;
                case 'ns1':
                    if ($token[0] == T_WHITESPACE) {
                        $state = 'ns2';
                    } else {
                        $state = 'out';
                    }
                    break;
                case 'ns2':
                    if ($token[0] == T_STRING || $token[0] ==  T_NS_SEPARATOR) {
                        $namespace .= is_array($token) ? $token[1] : $token;
                    } else {
                        $state = 'out';
                    }
                    break;
                case 'c1':
                    if ($token[0] == T_WHITESPACE) {
                        $state = 'c2';
                    } else {
                        $state = 'out';
                    }
                    break;
                case 'c2':
                    if ($token[0] == T_STRING) {
                        if (!$skip_next) {
                            $class_name = $token[1];
                            if ($namespace) $class_name = $namespace . '\\' . $class_name;
                            $classes[] = $class_name;
                        }
                        $skip_next = false;
                    }
                    $state = 'out';
                    break;
            }
        }
        
        return $classes;
    
    }
}
?>