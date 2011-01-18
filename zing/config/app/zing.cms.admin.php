<?php
/**
 * Define editable tables here
 */
$GLOBALS['_ZING']['zing.cms.tables'] = array(
    'product' => array('title' => 'Products', 'icon' => 'block'),
);

/**
 * This setting defines all of the asset adapters available via Zing's
 * snazzy asset dialog.
 *
 * By default we just add a single adapter that stores assets in Zing's local
 * file-based asset repository. With a little bit of love and elbow grease
 * it is also possible to integrate the asset dialog seamlessly with web
 * services such as S3 and Panda.
 */
$GLOBALS['_ZING']['zing.cms.assets.dialog.adapters'] = array(
    // Each entry is an array with a 'type' key and containing only objects
    // serializable to JSON
    array('type' => 'zing', 'title' => 'Asset Library')
);
?>