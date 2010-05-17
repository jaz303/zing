<!DOCTYPE html>

<!--[if lt IE 7]><html class="ie ie6 lte9 lte8 lte7"><![endif]-->
<!--[if IE 7]><html class="ie ie7 lte9 lte8 lte7"><![endif]-->
<!--[if IE 8]><html class="ie ie8 lte9 lte8"> <![endif]-->
<!--[if IE 9]><html class="ie ie9 lte9"> <![endif]-->
<!--[if gt IE 9]><html><![endif]-->
<!--[if !IE]><!-->
    
<html>

<!--<![endif]-->

<head>
<title>Zing! Application</title>
  
<?= stylesheet_collection('defaults') ?>

<?= javascript_collection('defaults') ?>
  
</head>

<body>
  
<?= $this->content_for('layout') ?>
  
</body>

</html>