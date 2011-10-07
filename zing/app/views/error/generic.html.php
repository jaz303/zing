<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title><?= $status ?> <?= ($string = \zing\http\Constants::text_for_status($status)) ?></title>
</head><body>
<h1><?= $string ?></h1>
<?php if (isset($message)) { ?><p><?= $message; ?></p><?php } ?>
<hr>
<address><?= ZING_SIGNATURE ?></address>
</body></html>