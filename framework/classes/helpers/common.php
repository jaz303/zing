<?php
namespace zing\helpers;

class HTMLHelper
{
    public static function h($string) {
        return htmlspecialchars($string);
    }
    
    public static function tag($name, $content, $attributes = array()) {
        $html = "<$name";
        foreach ($attributes as $k => $v) {
            $html .= ' ' . $k . '="' . htmlspecialchars($v) . '"';
        }
        $html .= ">$content</$name>";
        return $html;
    }
    
    public static function empty_tag($name, $attributes = array()) {
        $html = "<$name";
        foreach ($attributes as $k => $v) {
            $html .= ' ' . $k . '="' . htmlspecialchars($v) . '"';
        }
        $html .= ' />';
        return $html;
    }
}

class FormHelper
{
    public static function hidden_input($name, $value = '', $options = array()) {
        $options['type'] = 'hidden';
        $options['value'] = $value;
        return HTMLHelper::empty_tag('input', $options);
    }
    
    public static function text_input($name, $value = '', $options = array()) {
        $options['type'] = 'text';
        $options['value'] = $value;
        return HTMLHelper::empty_tag('input', $options);
    }
    
    public static function textarea($name, $value = '', $options = array()) {
        $options['name'] = $name;
        return HTMLHelper::tag('textarea', $value, $options);
    }
}

class AssetHelper
{
    public static function stylesheet_link_tag($css, $options = array()) {
        $options['href'] = self::url_for_stylesheet($css);
        $options['rel'] = 'stylesheet';
        $options['type'] = 'text/css';
        return HTMLHelper::empty_tag('link', $options);
    }

    public static function javascript_include_tag($js, $options = array()) {
        $options['src'] = self::url_for_javascript($js);
        $options['type'] = 'text/javascript';
        return HTMLHelper::tag('script', '', $options);
    }
}
?>