<?php
namespace zing\sys;

class OS
{
    public static $EXECUTABLE_PATHS = array(
        'unix' => array(
            '/bin',
        	'/sbin',
        	'/usr/bin',
        	'/usr/sbin',
        	'/opt/bin',
        	'/opt/sbin',
        	'/opt/local/bin',
        	'/opt/local/sbin'
        )
    );
    
    public static function flavor() {
        return 'unix'; // wishful thinking
    }
    
    public static function is_unix() {
        return self::flavor() == 'unix';
    }
    
    public static function find_executable_anywhere($name) {
        $path = self::find_executable_in_path($name);
        if (!$path) {
            $path = self::find_executable($name);
        }
        return $path;
    }
    
    public static function find_executable_in_path($name) {
        if (self::flavor() == 'unix') {
            $result = trim(`which $name`);
            return $result ? $result : null;
        }
        return null;
    }
    
    public static function find_executable($name) {
        $candidate_paths = self::$EXECUTABLE_PATHS[self::flavor()];
        foreach ($candidate_paths as $cp) {
            $cf = $cp . DIRECTORY_SEPARATOR . $name;
            if (is_executable($cf)) {
                return $cf;
            }
        }
        return null;
    }
}
?>