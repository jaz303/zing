<?php
namespace zing;

class FileUtils
{
    public static function is_ignored_directory($directory) {
        return in_array(basename($directory), array('.svn', '.git', 'CVS'));
    }
}
?>