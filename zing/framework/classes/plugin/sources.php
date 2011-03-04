<?php
namespace zing\plugin;

class GitSource
{
    public static function clone_repository($repo, $target) {
        `git clone $repo $target`;
    }
    
    public function fetch_plugin($url, $target_dir) {
        self::clone_repository($url, $target_dir);
    }
}

class GithubSource
{
    public function fetch_plugin($plugin_ref, $target_dir) {
        list($gh_username, $gh_repo) = explode('.', $plugin_ref);
        GitSource::clone_repository("git://github.com/{$gh_username}/{$gh_repo}.git", $target_dir);
    }
}
?>