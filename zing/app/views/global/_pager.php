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

$base  = preg_replace('/(\?|&)page=\w+(&|$)/', '$1', $request->request_uri());
$base .= ((strpos($base, '?') === false) ? '?' : '&') . 'page=';
?>

<div class='pager'>
  <ul>
    <? for ($i = 1; $i <= $page_count; $i++) { ?>
      <li><a class='page<?= $i == $page ? ' active' : '' ?>' href='<?= $base ?><?= $i ?>'><?= $i ?></a></li>
    <? } ?>
  </ul>
</div>
