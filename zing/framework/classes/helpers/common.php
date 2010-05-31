<?php
namespace zing\helpers;

class DebugHelper
{
    public static function debug_dump($object, $caption = null) {
        
        ob_start();
        var_dump($object);
        $dumped = ob_get_clean();
        
        return self::debug_print($dumped, $caption);
        
    }
    
    public static function debug_print($text, $caption = null, $start_line = 1) {
        
        $html = '';
        
        if ($caption) {
            $html .= "<div style='padding:5px; font: bold 11px/1 Helvetica, Arial; color: black; background: #cdcdcd'>$caption</div>";
        }
        
        $html .= "<div style='height: 300px; overflow: auto; border: 1px solid #cdcdcd;'><pre style='padding:0;margin:0'>";
        $html .= "<ul style='margin:0;padding:0'>";
        
        $lines = explode("\n", $text);
        $places = ceil(log10(count($lines)));
        
        foreach ($lines as $ix => $line) {
            $bg = $ix & 1 ? '#f0f0f0' : '#f8f8f8';
            $html .= "<li style='margin:0;padding:3px;background-color:$bg'><b>" . sprintf("%0{$places}d", $ix + $start_line) . ":</b> " . htmlentities($line) . "</li>";
        }
        
        $html .= "</ul>";
        $html .= "</pre></div>";
        
        return $html;
        
    }
}

class HTMLHelper
{
    public static function h($string) {
        return htmlspecialchars($string);
    }
    
    public static function i($image, $attributes = array()) {
        $attributes['src'] = AssetHelper::image_path($image);
        $attributes += array('alt' => '');
        return self::empty_tag('img', $attributes);
    }
    
    /**
     * content helper, accepts a function argument for $content
     */
    public static function c($name, $content, $attributes = array()) {
        $chunks = parse_simple_selector($name);
        if (isset($chunks['name'])) {
            $name = $chunks['name'];
            unset($chunks['name']);
        } else {
            $name = 'div';
        }
        $attributes += $chunks;
        return self::tag($name, is_callable($content) ? $content() : $content, $attributes);
    }
    
    public static function tag($name, $content, $attributes = array()) {
        $html = "<$name";
        foreach ($attributes as $k => $v) {
            $html .= ' ' . $k . '="' . htmlspecialchars($v) . '"';
        }
        $html .= ">$content</$name>";
        return $html;
    }
    
    public static function opening_tag($name, $attributes = array()) {
        $html = "<$name";
        foreach ($attributes as $k => $v) {
            $html .= ' ' . $k . '="' . htmlspecialchars($v) . '"';
        }
        $html .= '>';
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
    
    public static function link_to($html, $url, $options = array()) {
        $options['href'] = $url;
        return self::tag('a', $html, $options);
    }

    public static function mail_to($html, $address = null, $options = array()) {
        if (is_array($address)) {
            $options = $address;
            $address = null;
        }
        if ($address === null) {
            $address = $html;
        }
        return self::link_to($html, "mailto:$address", $options);
    }
    
    public static function error_messages($errors) {
        return \zing\view\Base::active()->render_partial(':errors', array('errors' => $errors));
    }
}

class FormHelper
{
    public static function start_form($action = '', $options = array()) {
        $options += array('method' => 'post');
        $options['action'] = $action;
        if (isset($options['multipart'])) {
            $options['enctype'] = 'multipart/form-data';
            unset($options['multipart']);
        }
        return HTMLHelper::opening_tag('form', $options);
    }
    
    public static function end_form() {
        return "</form>";
    }
    
    public static function label($caption, $options = array()) {
        return HTMLHelper::tag('label', $caption, $options);
    }
    
    public static function hidden_field($name, $value = '', $options = array()) {
        $options['type'] = 'hidden';
        $options['name'] = $name;
        $options['value'] = $value;
        return HTMLHelper::empty_tag('input', $options);
    }
    
    public static function hidden_fields($array, $prefix = '') {
        $html = '';
        foreach ($array as $k => $v) {
            $name = strlen($prefix) ? "{$prefix}[$k]" : $k;
            if (is_enumerable($v)) {
                $html .= self::hidden_fields($v, $name);
            } else {
                $html .= self::hidden_field($name, $v);
            }
        }
        return $html;
    }
    
    public static function text_field($name, $value = '', $options = array()) {
        $options['type'] = 'text';
        $options['name'] = $name;
        $options['value'] = $value;
        return HTMLHelper::empty_tag('input', $options);
    }
    
    public static function password_field($name, $value = '', $options = array()) {
        $options['type'] = 'password';
        $options['name'] = $name;
        $options['value'] = $value;
        return HTMLHelper::empty_tag('input', $options);
    }
    
    public static function textarea($name, $value = '', $options = array()) {
        $options['name'] = $name;
        return HTMLHelper::tag('textarea', $value, $options);
    }
    
    public static function file_field($name, $options = array()) {
        return HTMLHelper::empty_tag('input', array(
            'type'  => 'file',
            'name'  => $name
        ) + $options);
    }
    
    public static function check_box($name, $checked = false, $options = array()) {
        $options['type'] = 'checkbox';
        $options['name'] = $name;
        $options += array('value' => 1);
        if ($checked) $options['checked'] = 'checked';
        return self::hidden_field($name, 0) . HTMLHelper::empty_tag('input', $options);
    }

    public static function radio_button($name, $value, $current_value = null, $options = array()) {
        $options['type'] = 'radio';
        $options['name'] = $name;
        $options['value'] = $value;
        if ($value == $current_value || $current_value === true) $options['checked'] = 'checked';
        return HTMLHelper::empty_tag('input', $options);
    }
    
    public static function select($name, $value, $choices, $options = array()) {
        $options['name'] = $name;
        $html = HTMLHelper::opening_tag('select', $options) . "\n";
        foreach ($choices as $k => $v) {
            $s = $k == $value ? ' selected=\"selected\"' : '';
            $k = htmlentities($k, ENT_QUOTES);
            $v = htmlspecialchars($v);
            $html .= "<option value=\"$k\"{$s}>$v</option>\n";
        }
        $html .= "</select>\n";
        return $html;
    }
    
    public static function country_select($name, $value = null, $options = array()) {
        $preferred = isset($options['preferred']) ? $options['preferred'] : null;
        $separator = isset($options['separator']) ? $options['separator'] : '-----';
        unset($options['preferred']);
        unset($options['separator']);
        return self::select($name, $value, \ISO_Country::names($preferred, $separator), $options);
    }
    
    public static function submit_button($text) {
        return "<input type='submit' value='$text' />";
    }
}

class AssetHelper
{
    public static function asset_path($name, $type, $extension = null) {
        if ($name[0] == '.' || $name[0] == '/' || preg_match('|^https?://|', $name)) {
            return $name;
        }
        $asset = '/' . $type . '/' . $name;
        if ($extension && (strpos($name, '.') === false)) {
            $name .= '.' . $extension;
        }
        return $asset;
    }
    
    public static function stylesheet_path($stylesheet) {
        return self::asset_path($stylesheet, 'stylesheets', 'css');
    }
    
    public static function javascript_path($javascript) {
        return self::asset_path($javascript, 'javascripts', 'js');
    }
    
    public static function image_path($image) {
        return self::asset_path($image, 'images', null);
    }
    
    public static function stylesheet_link_tag($css, $options = array()) {
        $options['href'] = self::stylesheet_path($css);
        $options += array('rel' => 'stylesheet', 'type' => 'text/css');
        return HTMLHelper::empty_tag('link', $options);
    }

    public static function javascript_include_tag($js, $options = array()) {
        $options['src'] = self::javascript_path($js);
        $options += array('type' => 'text/javascript');
        return HTMLHelper::tag('script', '', $options);
    }
    
    public static function stylesheet_collection($collection_name) {
        $html = '';
        foreach (\zing\view\Base::$stylesheet_collections[$collection_name] as $stylesheet) {
            if ($stylesheet[0] == ':') {
                $html .= self::stylesheet_collection(substr($stylesheet, 1));
            } else {
                $html .= self::stylesheet_link_tag($stylesheet) . "\n";
            }
        }
        return $html;
    }
    
    public static function javascript_collection($collection_name) {
        $html = '';
        foreach (\zing\view\Base::$javascript_collections[$collection_name] as $js) {
            if ($js[0] == ':') {
                $html .= self::javascript_collection(substr($js, 1));
            } else {
                $html .= self::javascript_include_tag($js) . "\n";
            }
        }
        return $html;
    }
}
?>