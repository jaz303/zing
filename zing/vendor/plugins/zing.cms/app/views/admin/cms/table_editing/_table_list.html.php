<? start_sidebar_menu(array('title' => 'Tables')) ?>
  <? foreach ($registry->editable_tables() as $table => $meta) { ?>
    <?= sidebar_menu_item($meta['icon'],
                          $meta['title'],
                          admin_url(':tables/' . $table),
                          $controller_name == $table) ?>
  <? } ?>
<? end_sidebar_menu() ?>