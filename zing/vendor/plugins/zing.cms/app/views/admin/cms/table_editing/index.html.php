<?= $this->render_partial('table_list') ?>

<? $headers = $controller->list_row_mapping(); ?>
<? $fields  = array_keys($headers); ?>

<table class='table-list' cellspacing='0' cellpadding='0' border='0'>
  <thead>
    <tr>
      <? foreach ($headers as $field => $caption) { ?>
        <th><?= $caption ?></th>
      <? } ?>
      <th class='actions'>Actions</th>
    </tr>
  </thead>
  <? if (count($collection)) { ?>
    <? $c = 0; foreach ($collection as $row) { ?>
      <tr>
        <? foreach ($fields as $f) { ?>
          <td><?= $controller->format_list_value($f, $row[$f], $c++) ?></td>
        <? } ?>
        <td class='actions'>
          <? foreach ($controller->list_actions_for_row($row) as $action => $meta) { ?>
            <a href='<?= $controller->url_for_table_action($action, $row['id']) ?>'
               data-method='<?= $meta['method'] ?>'
            <? if ($meta['confirm']) { ?>
               data-confirm='<?= htmlentities($meta['confirm'], ENT_QUOTES) ?>'
            <? } ?>
            ><?= icon($meta['icon']) ?> <span><?= $meta['caption'] ?></span></a>
          <? } ?>
        </td>
      </tr>
    <? } ?>
  <? } else { ?>
    <tr>
      <th colspan='<?= count($headers) + 1 ?>' class='empty'>No <?= $controller->plural() ?> found</th>
    </tr>
  <? } ?>
</table>