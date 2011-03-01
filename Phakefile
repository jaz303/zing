<?php
define('ZING_VERSION',        '0.0.1');
define('ZING_SOURCE_DIR',     'zing');
define('ZING_TARGET_DIR',     'zing-' . ZING_VERSION);

task("create_package_dir", function() {
    
    $cmd  = 'rsync -a ';
    $cmd .= "--exclude-from=PACKAGE_EXCLUSIONS ";
    $cmd .= ZING_SOURCE_DIR . '/ ' . ZING_TARGET_DIR;
    
    `$cmd`;
});

task("package", function() {
	
	$TARGET = "zing-" . ZING_VERSION;
	
	`rm -rf $TARGET`;
	`cp -R zing $TARGET`;
	`cd $TARGET && find . -name ".gitignore" -or -name ".git" -or -name ".gitmodules" -exec rm -rf \{\} \;`;
	`tar cfz $TARGET.tar.tgz $TARGET`;

});

task("clean", function() {
    `rm -rf zing-*`;
});
?>