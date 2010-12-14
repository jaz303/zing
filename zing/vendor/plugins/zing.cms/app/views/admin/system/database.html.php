<table class='table-list'>
  <thead>
    <tr>
      <th>Table</th>
      <th>Engine</th>
      <th class='numeric'>Rows</th>
      <th class='numeric'>Data Size</th>
      <th class='numeric'>Index Size</th>
    </tr>
  </thead> 
  <tbody>
    <? foreach ($usage['tables'] as $table_name => $table_info) { ?>
      <tr>
        <th><code><?= $table_name ?></code></th>
        <td><code><?= $table_info['engine'] ?></code></td>
        <td class='numeric'><code><?= $table_info['rows'] ?></code></td>
        <td class='numeric'><code><?= $table_info['data_size'] ?></code></td>
        <td class='numeric'><code><?= $table_info['index_size'] ?></code></td>
      </tr>
    <? } ?>
  </tbody>
</table>