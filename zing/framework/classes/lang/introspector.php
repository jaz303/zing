<?php
namespace zing\lang;

/**
 * Introspector is concerned with extracting information from PHP source code
 * without loading it.
 */
class Introspector
{
    /**
     * Returns the fully-qualified name of the first class defined
     * in a given PHP source file
     *
     * @param $file full path to file
     * @return fully-qualified name of first class defined in $file,
     *         or null if no class is defined.
     */
    public static function first_class_in_file($file) {
        
        $class_name = '';
        
        foreach (file($file) as $line) {
            if (preg_match('/namespace\s+([^;{\s]+)/', $line, $matches)) {
                $class_name .= $matches[1] . '\\';
            } elseif (preg_match('/class\s+([^{\s]+)/', $line, $matches)) {
                $class_name .= $matches[1];
                break;
            }
        }
        
        if (!$class_name) {
            return null;
        } else {
            if ($class_name[0] != '\\') {
                $class_name = '\\' . $class_name;
            }
            return $class_name;
        }
        
    }
}
?>