<!doctype html>

<!--
  Zing's default application layout is a slightly modified version of HTML5 Boilerplate
  http://html5boilerplate.com/
  (c) Paul Irish & contributors: https://github.com/paulirish/html5-boilerplate/contributors
-->

<!--[if lt IE 7 ]> <html class="no-js ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]>    <html class="no-js ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]>    <html class="no-js ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

  <title>Zing! Application</title>
  
  <meta name="description" content="">
  <meta name="author" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="shortcut icon" href="/favicon.ico">
  <link rel="apple-touch-icon" href="/apple-touch-icon.png">
  
  <?= stylesheet_collection('defaults') ?>
  <?= javascript_include_tag('modernizer-1.7.min.js') ?>
</head>

<body>

  <div id="container">
    <header>

    </header>
    <div id="main" role="main">
      <?= $this->content_for('layout') ?>
    </div>
    <footer>

    </footer>
  </div>

  <?= javascript_collection('defaults') ?>

  <!--[if lt IE 7 ]>
    <?= javascript_include_tag('js/libs/dd_belatedpng.js') ?>
    <script>DD_belatedPNG.fix('img, .png_bg');</script>
  <![endif]-->

</body>
</html>
