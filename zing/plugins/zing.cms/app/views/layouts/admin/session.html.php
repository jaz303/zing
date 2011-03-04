<html>
  <head>
    <title>Zing! CMS</title>
    <?= stylesheet_collection('zing.cms.admin-session') ?>
  </head>
  <body>
		<div id='page-wrapper'>
			<div id='non-footer'>
        <?= $this->content_for('layout') ?>
			</div>
			
  		<div id='notifications'>
        <? foreach ($session->current_flash() as $flash) { ?>
          <div class='flash <?= $flash['type'] ?>'><?= $flash['message'] ?></div>
        <? } ?>
      </div>
		
		</div>
    <div id='footer'>
    	<div class='align-right'>
    		<span class='zing'><?= \zing\cms\Attribution::name() ?></span>
        <?= \zing\cms\Attribution::copyright() ?>
    	  <?= i('zing.cms/admin/freefall-icon.png', array('class' => 'icon')) ?>
    	</div>
    </div>
  </body>
</html>
