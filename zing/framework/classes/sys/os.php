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