<?php
namespace zing\archive;

class UnsupportedAlgorithmException extends \Exception {}
class OperationFailedException extends \Exception {}

class Support
{
    private static $drivers = array(
        'zip-native'        => 'zing\\archive\\ZipNative',
        'zip-cli-nix'       => 'zing\\archive\\ZipCliNix'
    );
    
    public static function supported_drivers() {
        $drivers = array();
        foreach (self::$drivers as $driver => $class) {
            if (call_user_func(array($class, 'is_available'))) {
                $drivers[] = $driver;
            }
        }
        return $drivers;
    }
    
    public static function supported_algorithms() {
        $algs = array();
        foreach (self::supported_drivers() as $d) {
            $algs[call_user_func(array(self::$drivers[$d], 'algorithm'))] = true;
        }
        return array_keys($algs);
    }
    
    public static function is_supported_algorithm($algorithm) {
        foreach (self::supported_algorithms() as $a) {
            if ($a == $algorithm) return true;
        }
        return false;
    }
    
    public static function driver_for_algorithm($algorithm) {
        if (!self::is_supported_algorithm($algorithm)) {
            throw new UnsupportedAlgorithmException;
        }
        foreach (self::$drivers as $driver => $class) {
            if (call_user_func(array($class, 'algorithm')) == $algorithm) {
                return new $class;
            }
        }
    }
}

class ZipNative
{
    public static function is_available() {
        return class_exists('ZipArchive', false);
    }
    
    public static function algorithm() {
        return 'zip';
    }
    
    public function extract($archive, $directory, $options = array()) {
        $zip = new \ZipArchive;
        if (!$zip->open($archive)) {
            throw new OperationFailedException("could not open zip archive $archive");
        }
        if (!$zip->extractTo($directory)) {
            $zip->close();
            throw new OperationFailedException("could not extract zip archive to $directory");
        }
        $zip->close();
    }
}

class ZipCliNix
{
    public static function is_available() {
        $rz = $ru = 1;
        $tmp = array();
        exec("which zip", $tmp, $rz);
        exec("which unzip", $tmp, $ru);
        return $rz == 0 && $ru == 0;
    }
    
    public static function algorithm() {
        return 'zip';
    }
    
    public function extract($archive, $directory, $options = array()) {
        
        $options    += array('overwrite' => false);
        
        $flags      = "-qq ";
        $flags      .= $options['overwrite'] ? "-o " : "-n ";
        
        $archive    = escapeshellarg($archive);
        $directory  = escapeshellarg($directory);
        $tmp        = array();
        
        exec("unzip $flags $archive -d $directory", $tmp, $retval);
        
        // according to the man pages, error codes 1 + 2 may still result in
        // successful extraction. we'll err towards optimism and assume this
        // was the case.
        if ($retval > 2) {
            throw new OperationFailedException;
        }
    }
}
?>