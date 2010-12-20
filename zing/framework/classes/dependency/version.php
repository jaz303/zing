<?php
namespace zing\dependency;

class Version
{
    const VERSION_CHUNKER = '(\d+)(\.(\d+)(\.(\d+)([a-zA-Z][\w-]*)?)?)?';
    
    public static function coerce($version) {
        if (is_object($version)) {
            if ($version instanceof Version) {
                return $version;
            } else {
                return $version->to_version();
            }
        } else {
            return new Version((string) $version);
        }
    }
    
    public $major;
    public $minor;
    public $patch;
    public $tag;
    
    public function __construct($string) {
        if (preg_match('/^' . self::VERSION_CHUNKER . '$/', $string, $matches)) {
            $this->major = (int) $matches[1];
            $this->minor = (int) $matches[3];
            $this->patch = (int) $matches[5];
            $this->tag   = (string) $matches[6];
        } else {
            throw new \IllegalArgumentException("invalid version string: $string");
        }
    }
    
    public function is_same_minor_version(Version $v) {
        return ($this->major == $v->major) && ($this->minor == $v->minor);
    }
    
    public function compare_to(Version $v) {
        if (($cmp = $this->major - $v->major) != 0) return $cmp;
        if (($cmp = $this->minor - $v->minor) != 0) return $cmp;
        if (($cmp = $this->patch - $v->patch) != 0) return $cmp;
        return strcmp($this->tag, $v->tag);
    }
    
    public function is_less_than(Version $v) {
        return $this->compare_to($v) < 0;
    }
    
    public function is_greater_than($v) {
        return $this->compare_to($v) > 0;
    }
    
    public function equals(Version $v) {
        return $this->compare_to($v) == 0;
    }
}
?>