<?php
namespace ff\routing;

class Router
{
    public static function draw($R, $fn) {
        $router = new self($R);
        $fn($router);
    }
    
    private $R;
    public function __construct($R) {
        $this->R = $R;
    }
    
    public function defaults() {
        $this->sessions();
        $this->home();
        $this->module('core');
        $this->module('cms');
    }
    
    public function editable($type_id, $arg1, $arg2 = null) {
        $route = array('action' => 'index', 'id' => null);
        if ($arg2) {
            $route['namespace'] = $arg1;
            $route['controller'] = $arg2;
        } else {
            $route['controller'] = $arg1;
        }
        $this->R->connect("admin/cms/content/$type_id/:action/:id", $route);
    }
    
    public function sessions() {
        $this->R->connect('admin/sessions/:action', array(
            'controller'    => 'sessions',
            'namespace'     => 'admin'
        ));
    }
    
    public function home() {
        $this->R->connect('admin', array(
            'controller'    => 'dashboard',
            'namespace'     => 'admin\core',
            'action'        => 'index'
        ));
    }
    
    public function module($module_name) {
        $this->R->connect("admin/$module_name/:controller/:action/:id", array(
            'namespace'     => "admin\\$module_name",
            'action'        => 'index',
            'id'            => null
        ));
    }
    
    public function assets() {
        $this->R->connect('a/:source/:id/:profile', array(
            'action'        => 'show',
            'namespace'     => 'zing\cms\assets',
            'controller'    => 'assets',
            'profile'       => 'original'
        ));
    }
}
?>