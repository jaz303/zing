<?php
$builder = new \zing\cms\helpers\AdminStandardFormBuilder("/foo", "post");

$builder->set_data(array('forename' => 'Jason', 'surname' => 'Frame', 'bio' => "blah blah blah"));
$builder->set_prefix('person');

$builder->start_group('Group 1');

$builder->text_field('forename')
        ->note('This is your forename')
        ->label('Forename')
        ->display_hint('half');

$builder->text_field('surname')
        ->required()
        ->note('This is your surname')
        ->label('Surname')
        ->display_hint('half');
        
$builder->text_area('bio')
        ->note('Write your biography here')
        ->label('Biography');
        
$builder->end_group();

$builder->start_group('Group 2');

$builder->text_field('forename')
        ->note('This is your forename')
        ->label('Forename')
        ->display_hint('half');

$builder->text_field('surname')
        ->note('This is your surname')
        ->label('Surname')
        ->display_hint('half');
        
$builder->rich_text_area('bio')
        ->note('Write your biography here')
        ->label('Biography');
        
$builder->end_group();

echo $builder->to_html();
?>