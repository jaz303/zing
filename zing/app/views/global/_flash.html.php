<?php
if (isset($locals['flashes'])) {
  $flashes = $locals['flashes'];
}

if (empty($flashes)) return;
?>

<div class='flash-messages'>
  <? foreach ($flashes as $flash) { ?>
    <div class='flash flash-<?= $flash['type'] ?>'><?= $flash['message'] ?></div>
  <? } ?>
</div>
