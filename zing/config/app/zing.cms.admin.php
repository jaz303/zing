<?php
/**
 * Define editables here
 */
$GLOBALS['_ZING']['zing.cms.editables'] = array(
    'articles'          => array('title' => 'Articles', 'icon' => 'newspaper'),
    'commissioners'     => array('title' => 'Commissioners', 'icon' => 'user_black'),
    'artists'           => array('title' => 'Artists', 'icon' => 'user_green')
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