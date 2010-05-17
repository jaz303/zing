<?php
namespace zing\lang;

class Reflection
{
    public static function concrete_descendants_of($parent_name) {
        if (interface_exists($parent_name)) {
            return self::concrete_implementations_of($parent_name);
        } elseif (class_exists($parent_name)) {
            return self::concrete_subclasses_of($parent_name);
        } else {
            die('fail');
        }
    }
    
    public static function concrete_implementations_of($interface_name) {
        $impl = array();
        foreach (get_declared_classes() as $class_name) {
            $rc = new \ReflectionClass($class_name);
            if (self::is_concrete($rc) && $rc->implementsInterface($interface_name)) {
                $impl[] = $class_name;
            }
        }
        return $impl;
    }
    
    public static function concrete_subclasses_of($parent_class_name) {
        $subclasses = array();
        foreach (get_declared_classes() as $class_name) {
            if (!is_subclass_of($class_name, $parent_class_name)) {
                continue;
            }
            if (self::is_concrete(new \ReflectionClass($class_name))) {
                $subclasses[] = $class_name;
            }
        }
        return $subclasses;
    }
    
    public static function is_concrete(\ReflectionClass $rc) {
        return !$rc->isInterface() && !$rc->isAbstract();
    }
}
?>