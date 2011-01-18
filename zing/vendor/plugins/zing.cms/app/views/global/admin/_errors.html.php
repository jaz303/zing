<?php
if (isset($locals['errors'])) {
  $errors = $locals['errors'];
}

if (!isset($errors)) {
  return;
} elseif ($errors instanceof \Errors) {
  if ($errors->ok()) {
    return;
  }
  $errors = $errors->full_messages();
} elseif (count($errors) == 0) {
  return;
}
?>

<div class='errors'>
  <h2>The following errors occurred, please correct them:</h2>
  <ul>
    <? foreach ($errors as $e) { ?>
      <li><?= htmlspecialchars($e) ?></li>
    <? } ?>
  </ul>
</div>