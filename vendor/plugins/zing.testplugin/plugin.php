<?php
namespace foo\bar;

class ZingTestPlugin extends \zing\plugin\BlankPlugin
{
    public function id() {
        return "zing.testplugin";
    }
    
    public function version() {
        return "0.0.1";
    }
}
?>