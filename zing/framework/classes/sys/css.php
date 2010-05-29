<?php
namespace zing\sys;

class CSS
{
    public static function write_font_styles($css) {
        file_put_contents(ZING_PUBLIC_DIR . '/stylesheets/fonts.css', trim($css) . "\n\n", FILE_APPEND);
    }
    
    public static function install_font_file($source) {
        copy($source, ZING_PUBLIC_DIR . '/stylesheets/' . basename($source));
    }
}
?>