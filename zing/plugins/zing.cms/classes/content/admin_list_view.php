<?php
namespace zing\cms\content;

abstract class AdminListHandler
{
    protected $spec;
    
    public function __construct(ModelSpecification $ms) {
        $this->spec = $ms;
    }
    
    protected function actions_for_row($row) {
        return array(
            'edit' => array(
                'caption' => 'Edit',
                'confirmation' => false
            ),
            'delete' => array(
                'caption' => 'Delete',
                'confirmation' => true
            )
        );
    }
    
    
}
?>