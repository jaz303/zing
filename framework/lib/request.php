<?php
namespace zing;

class Request
{
    public static function factory() {
        
    }
    
    private $path;
    private $method;
    
    public function path() { return $this->path; }
    public function method() { return $this->method; }
}
?>