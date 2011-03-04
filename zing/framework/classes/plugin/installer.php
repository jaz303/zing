<?php
namespace zing\plugin;

class Installer
{
    public static $SOURCES = array(
        'git'           => 'zing\plugin\GitSource',
        'github'        => 'zing\plugin\GithubSource'
    );
    
    public static function source_ids() { return array_keys(self::$SOURCES); }
    
    public function __construct($manager, $reporter) {
        $this->manager  = $manager;
        $this->reporter = $reporter;
    }
    
    private $ok             = false;        // overall exit status
    private $source_ref;                    // source ref (e.g. 'git', 'github')
    private $source         = null;         // source instance
    private $plugin_ref;                    // plugin ref (e.g. URL, GitHub repo)
    private $tmp_dir        = null;         // temporary directory for extraction
    private $plugin_dir;                    // actual directory of fetched plugin
    private $plugin_id;                     // plugin ID, as read from plugin stub
    private $stub;
    private $plugin;
    
    public function success() { return $this->ok; }
    
    public function install($source_ref, $plugin_ref) {
        
        $this->source_ref = $source_ref;
        $this->plugin_ref = $plugin_ref;
        
        try {
            
            // Instantiate plugin source (e.g. git, github)
            $this->get_source();
            $this->reporter->info("Installing {$this->plugin_ref} (source={$this->source_ref})");          
            
            $this->create_temporary_directory();
            
            // Fetch plugin from external source
            // After this step the plugin will be unpacked into a temporary directory
            $this->fetch_plugin();
            
            // Plugin is unpacked; let's find the actual plugin dir.
            $this->find_plugin_dir();
            
            $this->create_stub();
            $this->check_install_can_proceed();
            $this->copy_files_to_target();
            $this->verify();
            $this->copy_plugin_files_to_app();
            $this->post_install();
            $this->regenerate_autoload_map();
            
            $this->ok = true;
            
        } catch (\Exception $e) {
            $this->reporter->fatal($e->getMessage());
        }
        
        $this->cleanup();
        
        if ($this->ok) {
            $this->reporter->success("Plugin {$this->plugin_id} installed successfully");
        }
        
    }
    
    private function get_source() {
        if (!isset(self::$SOURCES[$this->source_ref])) {
            throw new \Exception("Unknown plugin source - {$this->source_ref}");
        }
        $source_class = self::$SOURCES[$this->source_ref];
        $this->source = new $source_class;
    }
    
    private function create_temporary_directory() {
        $file = tempnam(sys_get_temp_dir(), 'zing-plugin-');
        if ($file === false) {
            throw new \Exception("couldn't create temporary file");
        }
        if (!unlink($file)) {
            throw new \Exception("couldn't delete temporary file");
        }
        if (!mkdir($file)) {
            throw new \Exception("couldn't create temporary directory");
        }
        $this->tmp_dir = $file;
        $this->reporter->info("Temporary directory created");
    }
    
    private function fetch_plugin() {
        $this->reporter->info("Fetching plugin {$this->plugin_ref}");
        $this->source->fetch_plugin($this->plugin_ref, $this->tmp_dir);
    }
    
    private function find_plugin_dir() {
        $stack = array($this->tmp_dir);
        while (count($stack)) {
            $dir = array_pop($stack);
            if (\zing\plugin\Utils::is_plugin($dir)) {
                $this->plugin_dir = $dir;
                $this->reporter->success("Plugin directory located");
                return;
            } else {
                $candidates = glob($dir . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);
                foreach ($candidates as $c) {
                    $stack[] = $c;
                }
            }
        }
        throw new \Exception("couldn't find a valid plugin directory, giving up.\n");
    }
    
    private function create_stub() {
        $this->stub = new Stub($this->plugin_dir);
        $this->plugin_id = $this->stub->id();
        $this->reporter->success("Stub created, plugin ID is {$this->plugin_id}");
    }
    
    private function check_install_can_proceed() {
        if ($this->manager->is_plugin_installed($this->plugin_id)) {
            throw new \Exception("This plugin is already installed");
        }
        
        if (is_dir($this->target_dir())) {
            $error  = "Target directory already exists! (is this plugin already installed?)\n";
            $error .= "Delete target directory and retry.";
            throw new \Exception($error);
        }
    }
    
    // FIXME: 'nix-specific
    private function copy_files_to_target() {
        $this->reporter->info("Copying files to target");
        `cp -R {$this->plugin_dir}/ {$this->target_dir()}`;
    }
    
    private function verify() {
        $this->manager->rescan();
        if (!$this->manager->is_plugin_installed($this->plugin_id)) {
            throw new \Exception("Plugin verification failed");
        } else {
            $this->plugin = $this->manager->plugin($this->plugin_id);
            $this->reporter->success("Plugin verified OK");
        }
    }
    
    // FIXME: 'nix-specific
    private function copy_plugin_files_to_app() {
        if ($this->plugin->has_files()) {
            $this->reporter->info("Copying plugin files to application root");
            $target = ZING_ROOT;
            `cp -R {$this->plugin->file_path()}/ $target`;
        } else {
            $this->reporter->info("This plugin has no files; nothing to do");
        }
    }
    
    private function post_install() {
        try {
            $this->reporter->info("Running post-install...");
            $this->plugin->post_install();
        } catch (\Exception $e) {
            $error  = "Post-install error: {$e->getMessage()}";
            $this->reporter->warning($error);
        }
    }
    
    private function regenerate_autoload_map() {
        \zing\sys\Utils::regenerate_autoload_map();
        $this->reporter->success('Autoload map regenerated');
    }
    
    private function cleanup() {
        if ($this->tmp_dir) {
            `rm -rf {$this->tmp_dr}`;
//            rm_rf($this->tmp_dir);
        }
    }
    
    //
    //
    
    private function target_dir() {
        return ZING_PLUGIN_DIR . DIRECTORY_SEPARATOR . $this->plugin_id;
    }
}
?>