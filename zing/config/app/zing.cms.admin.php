<?php
\zing\view\Base::$view_paths[] = ZING_ROOT . '/vendor/plugins/zing.cms/app/views';

\zing\view\Base::$stylesheet_collections['zing.cms.admin'] = array(
    'zing.cms/admin/reset.css',
    'zing.cms/admin/typography.css',
    'zing.cms/admin/flash.css',
    'zing.cms/admin/helpers.css',
    'zing.cms/admin/layout.css',
    'zing.cms/admin/widgets.css',
    'zing.cms/admin/main.css',
    'zing.cms/admin/asset-dialog.css',
    'zing.cms/jscalendar-1.0/calendar-win2k-1.css'
);

\zing\view\Base::$javascript_collections['zing.cms.admin'] = array(
    // 'zing.cms/jscalendar-1.0/calendar_stripped.js',
    // 'zing.cms/jscalendar-1.0/lang/calendar-en.js',
    'zing.cms/admin/jquery.min.js',
    'zing.cms/admin/zing.js',
    // 'zing.cms/admin/jquery.drag-queen.js',
    // 'zing.cms/tiny_mce/jquery.tinymce.js',
    // 'zing.cms/admin/jquery.rebind.js',
    // 'zing.cms/admin/admin.js'
);

/**
 * Define editable tables here
 */
$GLOBALS['_ZING']['zing.cms.tables'] = array(
    'product' => array('title' => 'Products', 'icon' => 'block'),
);
?>