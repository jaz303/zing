<!DOCTYPE html>

<?php
$show_sidebar = true; // TODO: dynamic
$section_path = 'core.home.dashboard';
?>

<html>
  <head>
    <title><?= \zing\cms\Attribution::name() ?></title>
    
    <?= stylesheet_collection('zing.cms.admin') ?>
    <?= javascript_collection('zing.cms.admin') ?>
    
    <link rel="shortcut icon" href="<?= image_path('zing.cms/admin/favicon.png') ?>" />
		
    <script type='text/javascript'>
		  $(function() {
        var dialog = new AssetDialog.Dialog();
        dialog.setRoot($('.asset-dialog-wrapper')[0]);
        dialog.init();
      });
		</script>
  </head>
  <body class='<?= $show_sidebar ? 'with-sidebar' : '' ?>'>
		<div id='page-wrapper'>
			<div id='non-footer'>
				<div id='header'>
					<h1 id='page-title'>
						Edit Page
						<span class='subtitle'>This is the subtitle</span>
					</h1>
					
					<div id='info-bar'>
						Logged in as jaz303 |
						<a href='#'>Logout</a> |
						Module: 
            <select class='x-link-select'>
              <? foreach ($admin_structure->available_modules($section_path) as $module) { ?>
                <option value='<?= admin_url($module['url']) ?>'><?= $module['name'] ?></option>
              <? } ?>
            </select>
		      </div>
					
					<ul id='sections'>
					  <? foreach ($admin_structure->available_sections($section_path) as $section) { ?>
              <li><a href='<?= $section['url'] ?>'><?= icon($section['icon']) ?> <?= $section['title'] ?></a></li>
            <? } ?>
					</ul>
				</div>
				
				<? if ($show_sidebar) { ?>
				
					<div id='sidebar'>
					  <? if (false) { ?>
  					  <? $section = $admin_structure->section_for($section_path); ?>
  					  <? if (!empty($section['children'])) { ?>
  					    <? start_sidebar_menu(array('title' => $section['title'])) ?>
  					      <? foreach ($section['children'] as $child) { ?>
  					        <?= sidebar_menu_item($child['icon'],
  					                              $child['title'],
  					                              admin_url($child['url']),
  					                              false) ?>
  					      <? } ?>
  					    <? end_sidebar_menu() ?>
  					  <? } ?>
					  <? } ?>
		        
		        <?= $this->content_for('sidebar') ?>
		      </div>
					
				<? } ?>
				
				<div id='main'>
					<div id='context-bar'>
						<?= $this->content_for('context') ?>
						<div class='clear'></div>
					</div>
					<div id='content'>
					  <?= $this->content_for('layout') ?>
					</div>
				</div>
				
				<div class='clear'></div>
			
			</div>
		</div>
		
		<div id='notifications'>
      <? foreach ($session->current_flash() as $flash) { ?>
        <div class='flash <?= $flash['type'] ?>'><?= $flash['message'] ?></div>
      <? } ?>
    </div>
    
    <div id='activity-indicator'>
      <?= i('zing.cms/admin/global-indicator.gif') ?>
    </div
	  
	  <div id='footer'>
    	<div class='align-right'>
    		<span class='freefall'><?= \zing\cms\Attribution::name() ?></span>
        <?= \zing\cms\Attribution::copyright() ?>
    	  <?= i('zing.cms/admin/freefall-icon.png', array('class' => 'icon')) ?>
    	</div>
    </div>
		
  </body>
</html>