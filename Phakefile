<?php
define('ZING_VERSION',        '0.0.1');
define('ZING_SOURCE_DIR',     'zing');
define('ZING_TARGET_DIR',     'zing-' . ZING_VERSION);

task("remove_package_dir", function() {
    $remove_cmd = "rm -rf " . ZING_TARGET_DIR;
    `$remove_cmd`;
});

task("create_package_dir", "remove_package_dir", function() {
    $cmd  = 'rsync -a ';
    $cmd .= "--exclude-from=PACKAGE_EXCLUSIONS ";
    $cmd .= ZING_SOURCE_DIR . '/ ' . ZING_TARGET_DIR;
    `$cmd`;
});

task("remove_cms_config", function() {
    require_once dirname(__FILE__) . '/zing/framework/classes/sys/source_block_writer.php';
    \zing\sys\SourceBlockWriter::remove_block_from_file(dirname(__FILE__) . '/' . ZING_TARGET_DIR . '/config/app/main.php', 'zing.cms.asset-path');
});

task("package", "create_package_dir", "remove_cms_config", function() {
	$cmd  = "tar cfz " . ZING_TARGET_DIR . '.tar.gz ' . ZING_TARGET_DIR;
	`$cmd`;
});

task("clean", function() {
    `rm -rf zing-*`;
});

//
// Versioning

task("write_version", function() {
    $boot_file = dirname(__FILE__) . '/zing/framework/boot.php';
    $src = file_get_contents($boot_file);
    $src = preg_replace("/define\('ZING_VERSION',(\s*)'\d+\.\d+\.\d+'\)/", "define('ZING_VERSION',\\1'" . ZING_VERSION . "')", $src);
    file_put_contents($boot_file, $src);
});

task("package_new_version", "write_version", "package");
?>