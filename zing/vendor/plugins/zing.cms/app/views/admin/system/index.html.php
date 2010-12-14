
<table class='table-list'>
  <thead>
    <tr>
      <th>Parameter Name</th><th>Parameter Value</th>
    </tr>
  </thead>
  <tr>
    <th>Zing! Version</th><td><?= ZING_VERSION ?></td>
  </tr>
  <tr>
    <th>Signature</th><td><?= ZING_SIGNATURE ?></td>
  </tr>
  <tr>
    <th>Defined Environments</th>
    <td><code><?= implode(', ', zing_environments()) ?></code></td>
  </tr>
  <tr>
    <th>Active Environment</th>
    <td><code><?= ZING_ENV ?></code></td>
  </tr>
  <?php
    $constants = array(
      'ZING_FRAMEWORK_DIR',
      'ZING_ROOT',
      'ZING_CONFIG_DIR',
      'ZING_PUBLIC_DIR',
      'ZING_TMP_DIR',
      'ZING_APP_DIR',
      'ZING_DATA_DIR',
      'ZING_VIEW_DIR',
      'ZING_CACHE_DIR',
      'ZING_COMPILED_DIR',
      'ZING_VENDOR_DIR',
      'ZING_PLUGIN_DIR',
      'ZING_CONSOLE');
  ?>
  <? foreach ($constants as $c) { ?>
    <tr>
      <th><code><?= $c ?></code></th>
      <td><code><?= var_export(constant($c), true); ?></code></td>
    </tr>
  <? } ?>
</table>