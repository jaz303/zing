<?php
namespace zing\sys;

class JS
{
    public static function write_application_onready_block($comment, $code) {
        
        $source  = "\n";
        $source .= "//\n";
        $source .= "// $comment\n";
        $source .= "\n";
        $source .= "$(function() {\n";
        $source .= preg_replace('/^/', "\t", trim($code)) . "\n";
        $source .= "});\n";
        
        file_put_contents(ZING_PUBLIC_DIR . '/javascripts/application.js', $source, FILE_APPEND);
        
    }
}
?>