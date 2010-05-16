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
        return self::asset_path($javascript, 'javascript', 'js');
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
}
?>