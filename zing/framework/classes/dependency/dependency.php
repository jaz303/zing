<?php
namespace zing\dependency;

class Dependency
{
    private $groups = array();
    
    /**
     * ">=2.0.0 <3 !2.1.1, =4.2.3"
     * Accepts either:
     *   any version >= 2.0.0 and < 3.0.0, specifically excluding 2.1.1
     *   OR
     *   version 4.2.3
     */
    public function __construct($string) {
        $string = trim($string);
        if (!empty($string)) {
            $groups = explode(',', $string);
            foreach ($groups as $group_string) {
                $group = array();
                foreach (preg_split('/\s+/', trim($group_string)) as $atom) {
                    if (preg_match('/^(<|<=|=|>|>=|\!|~>)(' . Version::VERSION_CHUNKER . ')$/', $atom, $matches)) {
                        $group[] = new Atom($matches[1], new Version($matches[2]));
                    } else {
                        throw new \InvalidArgumentException("invalid dependency atom: $atom");
                    }
                }
                $this->groups[] = $group;
            }
        }
    }
    
    public function accepts($v) {
        $v = Version::coerce($v);
        if (empty($this->groups)) {
            return true;
        } else {
            foreach ($this->groups as $group) {
                $ok = true;
                foreach ($group as $atom) {
                    if (!$atom->accepts($v)) {
                        $ok = false;
                        break;
                    }
                }
                if ($ok) {
                    return true;
                }
            }
            return false;
        }
    }
    
    public function toString() {
        return implode(', ', array_map(function($group) {
            return implode(' ', array_map(function($atom) {
                return $atom->toString();
            }, $group));
        }, $this->groups));
    }
}

class Atom
{
    public $operator;
    public $version;
    
    public function __construct($operator, Version $version) {
        $this->operator = $operator;
        $this->version = $version;
    }
    
    public function accepts(Version $v) {
        $cmp = $v->compare_to($this->version);
        switch ($this->operator) {
            case '<':   return $cmp < 0;
            case '<=':  return $cmp <= 0;
            case '=':   return $cmp == 0;
            case '>':   return $cmp > 1;
            case '>=':  return $cmp >= 0;
            case '!':   return $cmp != 0;
            case '~>':  return $v->is_same_minor_version($this->version);
            default:    throw new \IllegalArgumentException("invalid operator: $this->operator");
        }
    }
    
    public function toString() {
        return "{$this->operator}{$this->version->toString()}";
    }
}
?>