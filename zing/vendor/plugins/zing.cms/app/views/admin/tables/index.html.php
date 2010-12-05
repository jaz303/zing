
<?php
$headers  = $table->row_mapping();
$fields   = array_keys($headers);
?>

<style type='text/css'>
  button {
    background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#e0e0e0), to(#e0e0e0), color-stop(.6,#d0d0d0));
    border: 2px solid #ddd;
    font-size: 10px;
    font-weight: bold;
    line-height: 1;
    padding: 3px 8px;
    text-shadow: 0 -1px 1px #222
    -moz-border-radius: 5px;
    -webkit-border-radius: 5px;
    color: #404040;
  }
  
  button:hover { 
    background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#c0c0c0), to(#c0c0c0), color-stop(.6,#b0b0b0));
  }
  
  button * { vertical-align: middle; }

  .table-list { width: 100%; font-size: 11px; border-collapse: separate; border-spacing: 2px; }
    .table-list th,
    .table-list td { padding: 5px; vertical-align: middle; }
    .table-list th { text-align: left;  }
    .table-list td { }
    
  .table-list thead tr { }
    .table-list thead th {
      background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#666666), to(#666666), color-stop(.6,#333));
      color: white;
    }
    
  .table-list tbody tr { }
    .table-list tbody tr:nth-child(odd) td { background: #f0f0f0; }
    .table-list tbody tr:nth-child(even) td { background: #f4f4f4; }
  
  .table-list .actions { }
    .table-list .actions form { float: left; margin-right: 5px; }
  
</style>

<table class='table-list' cellspacing='2' cellpadding='0' border='0'>
  <thead>
    <tr>
      <? foreach ($headers as $k => $v) { ?>
        <th><?= $v ?></th>
      <? } ?>
      <th colspan='<?= $interface->max_action_count() ?>'>Actions</th>
    </tr>
  </thead>
  <tbody>
    <? $ix = 0; foreach ($collection['rows'] as $row) { ?>
      <tr>
        <? foreach ($fields as $f) { ?>
          <td><?= $interface->format_list_value($f, $row[$f], $ix) ?></td>
        <? } ?>
        <td class='actions'>
          <div>
            <? foreach ($interface->actions_for_row($row) as $name => $action) { ?>
              <form method='<?= $action['method'] ?>' action='<?= $controller->url_for_table_action($name, $row['id']) ?>'>
                <button><?= icon($action['icon']) ?> <span><?= $action['caption'] ?></span></button>
              </form>
            <? } ?>
          </div>
        </td>
      </tr>
    <? } ?>
  </tbody>
</table>

<form method='get'>
  <select name='per_page' onchange='this.parentNode.submit()'>
    <? foreach ($interface->per_page_options() as $o) { ?>
      <option <?= $o == $collection['per_page'] ? 'selected="selected"' : '' ?>><?= $o ?></option>
    <? } ?>
  </select>
</form>