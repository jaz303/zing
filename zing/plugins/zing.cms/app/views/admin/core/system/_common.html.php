<?= start_sidebar_menu() ?>
  <?= sidebar_menu_item('table', 'Overview', ':core/system', $action_name == 'index'); ?>
  <?= sidebar_menu_item('database', 'Database', ':core/system/database', $action_name == 'database'); ?>
<?= end_sidebar_menu() ?>
