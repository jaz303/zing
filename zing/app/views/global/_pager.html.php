<?php
// A bit of juggling

if (isset($locals['collection'])) {
    $collection = $locals['collection'];
}

if (isset($collection)) {
  if ($collection instanceof \GDBResult) {
    $page_count     = $collection->page_count();
    $page           = $collection->page();
  } else {
    $page_count     = $collection['page_count'];
    $page           = $collection['page'];
  }
}

if (!isset($page_count) || $page_count < 2) return;
if (!isset($page)) $page = 1;

$base  = $request->path() .
         $request->query()->to_string_with_trailing_assignment('page', true);
$base  = htmlspecialchars($base);
?>

<div class='pager'>
  <ol>
    <? if ($page > 1) { ?>
      <li><a class='prev' href='<?= $base ?><?= $page - 1 ?>'>&laquo; Prev</a></li>
    <? } ?>
    <? for ($i = 1; $i <= $page_count; $i++) { ?>
      <li><a class='page<?= $i == $page ? ' active' : '' ?>' href='<?= $base ?><?= $i ?>'><?= $i ?></a></li>
    <? } ?>
    <? if ($page < $page_count) { ?>
      <li><a class='next' href='<?= $base ?><?= $page + 1 ?>'>Next &raquo;</a></li>
    <? } ?>
  </ol>
</div>
