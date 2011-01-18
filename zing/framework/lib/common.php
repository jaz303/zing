<?php
//
// Simple Validations

/**
 * Checks a value for emptiness.
 *
 * Our definition of empty:
 * array - empty if no elements
 * string -> empty if zero-length
 * null -> empty
 * integer, float, boolean, object, resource -> not empty
 *
 * @param $value value to check for emptiness
 * @return true if $value is empty, false otherwise
 */
function is_empty($value) {
    if (is_array($value)) {
        return empty($value);
    } elseif (is_string($value)) {
        return strlen($value) == 0;
    } else {
        return $value === null;
    }
}

/**
 * Checks a value for blankness.
 *
 * A blank value is one which is empty, a string containing only whitespace, or
 * an object whose is_blank() method returns true.
 *
 * @param $value value to check for blankness
 * @return true if $value is blank, false otherwise
 */
function is_blank($value) {
    if (is_object($value) && method_exists($value, 'is_blank')) return $value->is_blank();
    if (is_string($value)) $value = trim($value);
    return is_empty($value);
}
// 
// function is_integer($value) {
//     if (is_integer($value)) return true;
//     return preg_match('/^\s*-?\d+\s*$/', $value);
// }
// 
// function is_numeric($value) {
//     if (is_integer($value) || is_float($value)) return true;
//     return preg_match('/^\s*-?\d+(\.\d+)?\s*$/', $value);
// }

function is_email($email, $use_dns = false) {
    if (!preg_match('/^[^\s@]+@([a-z0-9-]+(\.[a-z0-9-]+)+)$/i', $email, $matches)) {
		return false;
	} elseif ($use_dns) {
	    $tmp = array();
	    return getmxrr($matches[1], $tmp) || dns_get_record($matches[1], DNS_A);
	} else {
	    return true;
	}
}

//
// Coercion

/**
 * Convert value to integer or null
 *
 * @param $i value to make into an integer
 * @return null if $i is null, (int) $i otherwise
 */
function int_or_null($i) {
    return $i === null ? null : (int) $i;
}

/**
 * Convert value to float or null
 *
 * @param $f value to make into a float
 * @return null if $f is null, (float) $f otherwise
 */
function float_or_null($f) {
    return $f === null ? null : (float) $f;
}

/**
 * Trim a string then constrain its length.
 *
 * @param $str string
 * @param $len max length
 * @return string, trimmed then reduced to length $len
 */
function trim_to($str, $len) {
    return substr(trim($str), 0, $len);
}

/**
 * Trim a string, optionally constrain to a given length, or return null
 *
 * @param $str string
 * @param $len optional max length
 * @return null if $str is null, trimmed string with maximal length $len otherwise.
 */
function trim_or_null($str, $len = null) {
    if ($str === null) return null;
    return trim_to($str, $len ? $len : strlen($str));
}

/**
 * Trim a string, optional constrain to a given length, returning the modified string,
 * or null if the resultant string is empty.
 *
 * @param $str string
 * @param $len optional max length
 * @return $str, trimmed and constrained. Returns null if $str is empty after processing.
 */
function trim_to_null($str, $len = null) {
    $str = trim_to($str, $len ? $len : strlen($str));
    return strlen($str) ? $str : null;
}
//
// Support

/**
 * Turn some representation of a URL into a string.
 * Scalar parameters are coerced to strings and returned.
 * Any other argument will be passed to url_for(), which should be implemented by
 * your application.
 */
function generate_url($u) {
    return is_scalar($u) ? (string) $u : url_for($u);
}

function is_lambda($thing) {
    return is_object($thing) && method_exists($thing, '__invoke');
}

function is_enumerable($thing) {
    return is_array($thing) || is_object($thing);
}

//
// Array paths (docs coming soon)

function array_path($array, $path, $default = null) {
    $path = explode('.', $path);
    while ($key = array_shift($path)) {
        if (!isset($array[$key])) return $default;
        $array = $array[$key];
    }
    return $array;
}

function array_path_unset(&$array, $path) {
    $tmp = & $array;
    $path = explode('.', $path);
    while (count($path) > 1) {
        $key = array_shift($path);
        if (!isset($tmp[$key])) return;
        $tmp = & $tmp[$key];
    }
    unset($tmp[array_shift($path)]);
}

function array_without_path($array) {
    $args = func_get_args();
    array_shift($args);
    foreach ($args as $path) array_path_unset($array, $path);
    return $array;
}

function array_path_replace(&$array, $path, $value) {
    $tmp = & $array;
    $path = explode('.', $path);
    while (count($path) > 1) {
        $key = array_shift($path);
        if (!isset($tmp[$key])) $tmp[$key] = array();
        $tmp = & $tmp[$key];
    }
    $tmp[array_shift($path)] = $value;
}

function array_path_to_name($path) {
    $bits = explode('.', $path);
    $out  = array_shift($bits);
    while (count($bits)) $out .= '[' . array_shift($bits) . ']';
    return $out;
}

/**
 * Generate a query string from an array, optionally replacing and/or removing
 * elements from the array (referenced by path).
 *
 * @param $array array to turn into a query string
 * @param $replace map of paths to replace, and their new values
 * @param $remvoe array of paths to remove
 */
function query_string(array $array, $replace = null, $remove = null) {
    if ($replace !== null) {
        foreach ((array) $replace as $path => $v) {
            array_path_replace($array, $path, $v);
        }
    }
    if ($remove !== null) {
        foreach ((array) $remove as $path) {
            array_path_unset($array, $path);
        }
    }
    return array_url_encode($array);
}

/**
 * Generate a query string fragment.
 *
 * one string param  - returned as-is
 * one array param   - passed to array_url_encode() and returned
 * two string params - returns $arg1=$arg2
 *
 * @param $arg1
 * @param $arg2
 * @return query string fragment
 */
function query_string_fragment($k, $v = null) {
    if ($v === null) {
        if (is_array($k)) {
            return array_url_encode($k);
        } else {
            return (string) $k;
        }
    } else {
        return urlencode($k) . '=' . urlencode($v);
    }
}

function url_append($url, $k, $v = null) {
    $sep = (strpos($url, '?') === false) ? '?' : '&';
    return $url . $sep . query_string_fragment($k, $v);
}

function query_string_append($query_string, $k, $v = null) {
    return $query_string . (strlen($query_string) ? '&' : '') . query_string_fragment($k, $v);
}

function array_url_encode($array, $omit = null) {
    $out = array();
    _array_url_encode_recurse($array, $out, $omit, '');
    return implode('&', $out);
}

function _array_url_encode_recurse($src, &$dst, $omit, $prefix) {
    foreach ($src as $k => $v) {
        if ($k === $omit) continue;
        $name = strlen($prefix) ? "{$prefix}[$k]" : $k;
        if (is_enumerable($v)) {
            _array_url_encode_recurse($v, $dst, $omit, $name);
        } else {
            $dst[] = urlencode($name) . '=' . urlencode($v);
        }
    }
}

//
// Functional programming primitives

// returns the arity of the given closure
function arity($lambda) {
    $r = new ReflectionObject($lambda);
    $m = $r->getMethod('__invoke');
    return $m->getNumberOfParameters();
}

function every($iterable, $lambda) {
    if (arity($lambda) < 2) {
        foreach ($iterable as $i) $lambda($i);
    } else {
        foreach ($iterable as $k => $v) $lambda($k, $v);
    }
}

function every_with_index($iterable, $lambda) {
    $c = 0;
    if (arity($lambda) < 3) {
        foreach ($iterable as $i) $lambda($i, $c++);
    } else {
        foreach ($iterable as $k => $v) $lambda($k, $v, $c++);
    }
}

function map($iterable, $lambda) {
    $out = array();
    foreach ($iterable as $v) $out[] = $lambda($v);
    return $out;
}

function kmap($iterable, $lambda) {
    $out = array();
    foreach ($iterable as $k => $v) $out[$k] = $lambda($v);
    return $out;
}

// returns true iff $lambda($v) returns true for all values $v in $iterable
function all($iterable, $lambda) {
    foreach ($iterable as $v) {
        if (!$lambda($v)) return false;
    }
    return true;
}

// returns true iff $lambda($v) returns true for any value $v in $iterable
function any($iterable, $lambda) {
    foreach ($iterable as $v) {
        if ($lambda($v)) return true;
    }
    return false;
}

function inject($iterable, $memo, $lambda) {
    if (arity($lambda) < 3) {
        foreach ($iterable as $v) $memo = $lambda($memo, $v);
    } else {
        foreach ($iterable as $k => $v) $memo = $lambda($memo, $k, $v);
    }
    return $memo;
}

// filters $iterable, returning only those values for which $lambda($v) is true
function filter($iterable, $lambda) {
    $out = array();
    foreach ($iterable as $v) if ($lambda($v)) $out[] = $v;
    return $out;
}

// as filter(), but preserves keys
function kfilter($iterable, $lambda) {
    $out = array();
    foreach ($iterable as $k => $v) if ($lambda($v)) $out[$k] = $v;
    return $out;
}

// filters $iterable, removing those values for which $lambda($v) is true
function reject($iterable, $lambda) {
    $out = array();
    foreach ($iterable as $v) if (!$lambda($v)) $out[] = $v;
    return $out;
}

// as reject(), but preserves keys
function kreject($iterable, $lambda) {
    $out = array();
    foreach ($iterable as $k => $v) if (!$lambda($v)) $out[$k] = $v;
    return $out;
}

//
// Filesystem

// Recursively create a directory without whining if it already exists
function mkdir_p($directory, $mode = 0777) {
    if (!is_dir($directory)) {
        mkdir($directory, $mode, true);
    }
}

// Recursively delete a file/directory
function rm_rf($file) {
    if (is_dir($file)) {
        if (!($dh = opendir($file))) {
            return false;
        }
        while (($entry = readdir($dh)) !== false) {
            if ($entry == '.' || $entry == '..') continue;
            if (!rm_rf($file . DIRECTORY_SEPARATOR . $entry)) {
                closedir($dh);
                return false;
            }
        }
        closedir($dh);
        return rmdir($file);
    } else {
        return unlink($file);
    }
}

//
// Some parsing stuff

/**
 * Parse a selector of the form #foo.bar.baz into constituent ID and classes.
 * An array argument will be returned unchanged.
 */
function parse_simple_selector($s) {
    if (!is_array($s)) {
        preg_match('/^([\w-]+)?(#([\w-]+))?((\.[\w-]+)*)$/', $s, $matches);
        $s = array();
        if (!empty($matches[1])) $s['name'] = $matches[1];
        if (!empty($matches[3])) $s['id'] = $matches[3];
        if (!empty($matches[4])) $s['class'] = trim(str_replace('.', ' ', $matches[4]));
    }
    return $s;
}

function parse_options($options) {
    if (is_array($options)) {
        return $options;
    } else {
        $parser = new \zing\lang\OptionParser;
        return $parser->parse($options);
    }
}
?>