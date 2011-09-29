<?= error_messages($user) ?>

<?php
$url = $user->is_new_record() ? ':core/users/create' : (':core/users/edit/' . $user->get_id());
$url = admin_url($url);

$builder = new \zing\cms\admin\helpers\StandardFormBuilder($url, 'post');

$builder->set_prefix('user');
$builder->set_errors($user->errors());
$builder->set_context($user, 'get');

$builder->cancel_url(admin_users_url());

$builder->start_group('User');
  
  $builder->text_field('username')
          ->label('Username')
          ->required()
          ->note('Must be unique')
          ->display_hint('half');
  
  $builder->text_field('email')
          ->label('Email')
          ->required()
          ->note('Must be unique')
          ->display_hint('half');
  
  $builder->text_field('forename')
          ->label('Forename')
          ->required()
          ->display_hint('half');
  
  $builder->text_field('surname')
          ->label('Surname')
          ->required()
          ->display_hint('half');

$builder->end_group();

$builder->start_group('Password');
  
  $builder->password_field('password')
          ->label('New Password')
          ->display_hint('half')
          ->note('Only enter if you wish to change ' . h($user->get_forename()) . '\'s password');
  
  $builder->password_field('password_confirmation')
          ->label('Confirm New Password')
          ->display_hint('half');

$builder->end_group();

echo $builder->to_html();
?>