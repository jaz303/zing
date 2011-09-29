<?= $this->render_partial('editables_list') ?>

<?= start_context_menu() ?>
  <?= context_menu_item('table', 'List ' . $controller->plural(), $controller->url_for_list(), $action_name == 'index') ?>
  <? if (in_array($action_name, array('update', 'delete'))) { ?>
    <?= context_menu_item('pencil', 'Edit ' . $controller->singular(), $controller->url_for_edit($id), $action_name == 'update') ?>
    <?= context_menu_item('minus_circle', 'Delete ' . $controller->singular(), $controller->url_for_delete($id), $action_name == 'delete') ?>
  <? } else { ?>
    <?= context_menu_item('plus_circle', 'New ' . $controller->singular(), $controller->url_for_new(), $action_name == 'create') ?>
  <? } ?>
<?= end_context_menu() ?>
