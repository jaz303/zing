<?= start_context_menu() ?>
  <?= context_menu_item('table', 'List Users', admin_users_url(), $action_name == 'index') ?>
  <? if (in_array($action_name, array('edit', 'delete'))) { ?>
    <?= context_menu_item('pencil', 'Edit User', admin_edit_user_url($user), $action_name == 'edit') ?>
    <?= context_menu_item('minus_circle', 'Delete User', admin_delete_user_url($user), $action_name == 'delete') ?>
  <? } else { ?>
    <?= context_menu_item('plus_circle', 'Add User', admin_new_user_url(), $action_name == 'create') ?>
  <? } ?>
<?= end_context_menu() ?>
