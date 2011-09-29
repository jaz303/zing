<?= start_sidebar_menu(array('title' => 'Editables')) ?>
  <? foreach (\zing\cms\admin\editables\Registry::instance() as $type => $editable) { ?>
    <?= sidebar_menu_item($editable['icon'],
                          $editable['title'],
                          admin_editable_url($type),
                          $controller_name == $type) ?>
  <? } ?>
<?= end_sidebar_menu() ?>