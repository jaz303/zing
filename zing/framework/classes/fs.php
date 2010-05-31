<?php
namespace zing;

class FileUtils
{
    public static function relativize_path($path, $relative_to) {
        
        if (!self::is_absolute_path($relative_to)) {
            throw new \InvalidArgumentException("'$relative_to' is not an absolute path");
        }
        
        // No point of reference so just return it unaltered
        if (!self::is_absolute_path($path)) {
            return $path;
        }
        
        if (!($path = realpath($path))) {
            throw new \InvalidArgumentException("Could not expand path '$path'");
        }
        
        $relative_to = self::add_trailing_slash($relative_to);
        
        if (strpos($path, $relative_to) === 0) {
            return substr($path, strlen($relative_to));
        }
        
        throw new \InvalidArgumentException("sorry this function is lame and only deals with a single case");
        
    }
    
    // TODO: this is unix-specific
    public static function is_absolute_path($path) {
        return $path[0] == '/';
    }
    
    public static function add_trailing_slash($path) {
        return rtrim($path, '/') . '/';
    }
    
    public static function is_ignored_directory($directory) {
        return in_array(basename($directory), array('.svn', '.git', 'CVS'));
    }
}
?>