<?php
define('ZING_VERSION', '0.0.1');

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