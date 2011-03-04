<?php
namespace zing\cms\routing;

class Router
{
    public static function draw_routes($R) {
        foreach (array_slice(func_get_args(), 1) as $k) {
            switch ($k) {
                case 'admin':

                    // Route for generic table editing
                    $R->connect('admin/tables/:controller/:action/:id',
                        array('namespace'   => 'admin\\cms\\table_editing',
                              'action'      => 'index',
                              'id'          => null)
                    );
                    
                    // Generic admin route
                    $R->connect('admin/:controller/:action/:id',
                        array('namespace' => 'admin', 'action' => 'index', 'id' => null)
                    );
                    
                    break;
                
                case 'assets':
                
                    // Asset warping...
                    $R->connect('a/:source/:id/:profile',
                        array('action'      => 'show',
                              'namespace'   =>'\\zing\\cms\\assets',
                              'controller'  => 'assets',
                              'profile'     => 'original')
                    );
                    
                    break;
            
            }
        }
    }
}
?>