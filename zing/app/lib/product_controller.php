<?php
namespace admin\cms\table_editing;

class ProductController extends AbstractController
{
    //
    // Define extra actions here. Defaults are: index, create, update, delete
    
    //
    //  
    
    public function list_row_mapping() { 
        return array('id' => 'ID', 'title' => 'Title');
    }
}
?>