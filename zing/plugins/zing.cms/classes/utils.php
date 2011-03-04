<?php
namespace zing\cms;

class FilterAbortException extends Exception {}

class Utils
{
    /**
     * Applies a filter specification to a value
     *
     * A valid filter spec is something like:
     * trim | str_replace('_', ' ', _)
     *
     * This would trim the value then replace underscores with spaces. Note the use of
     * _ as the 3rd parameter to str_replace, to denote the value currently being
     * operated upon.
     *
     * Filters can also be specified as ->foo() or ::foo() where object and class
     * contexts have been specified.
     *
     * @param $spec filter to apply; can either be a string or a lambda function
     * @param $value value to filter
     * @param $object_context for string filters, this is the object instance upon
     *        which to call any filters prefixed with '->'
     * @param $class_context for string filters, this is the class upon which to
     *        call any filters prefixed with '::'
     * @return filtered value
     */
    function apply_filter($spec, $value, $object_context = null, $class_context = null) {
    	try {
    	    if (is_lambda($spec)) {
    	        $value = $spec($value);
    	    } else {
    	        foreach (array_map('trim', explode('|', $spec)) as $f) {
        			if (($p = strpos($f, '(')) !== false) {
        			    $args = array();
        				$m = substr($f, 0, $p);
        				$a = preg_replace('/((^|,)\s*)_/', '$1"--filter^sentinel--"', substr($f, $p + 1, -1));
        				$args = json_decode('[' . $a . ']');
        				if (($i = array_search("--filter^sentinel--", $args)) !== false) $args[$i] = $value;
        			} else {
        			    $m = $f;
        			    $args = array($value);
        			}
        			if ($m[0] == '-' && $m[1] == '>') {
        				$m = array($object_context, substr($m, 2));
        			} elseif (strpos($m, '::') === 0) {
        				$m = $class_context . $m;
        			}
        			$value = call_user_func_array($m, $args);
        		}
    	    }
    	} catch (FilterAbortException $fae) {}
    	return $value;
    }
}
?>