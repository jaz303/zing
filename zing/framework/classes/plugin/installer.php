<?php
namespace zing\plugin;

class Installer
{
    public function __construct($manager, $reporter) {
        $this->manager  = $manager;
        $this->reporter = $reporter;
    }
    
    private $ok         = false;
    
    private $ref;
    private $file       = null;
    private $dir        = null;
    private $uri        = null;
    private $delete     = array();
    private $stub       = null;
    private $plugin_id  = null;
    private $plugin     = null;
    
    public function success() { return $this->ok; }
    
    public function install($plugin_ref) {
        
        $this->ref = $plugin_ref;
        
        try {
            
            $this->resolve();
            $this->download_if_necessary();
            $this->extract_if_necessary();
            $this->find_plugin_dir();
            $this->create_stub();
            $this->check_install_can_proceed();
            
            $this->copy_files_to_target();
            $this->verify();
            $this->add_plugin_class_path();
            $this->copy_plugin_files_to_app();
            $this->post_install();
            
            $this->ok = true;
            
        } catch (InvalidPluginException $ipe) {
            $this->reporter->fatal("plugin is invalid! ({$ipe->getMessage()})");
        } catch (\Exception $e) {
            $this->reporter->fatal($e->getMessage());
        }
        
        $this->cleanup();
        
        if ($this->ok) {
            $this->reporter->success("Plugin {$this->plugin_id} installed successfully");
        }
        
    }
    
    private function resolve() {
        if (is_dir($this->ref)) {
            $this->dir = $this->ref;
        } elseif (is_file($this->ref)) {
            $this->file = $this->ref;
        } elseif (preg_match('|^https?://|', $this->ref)) {
            $this->uri = $this->ref;
        } elseif (\zing\plugin\Util::is_valid_plugin_id($this->ref)) {
            // TODO: plugin ID - convert to URL
            throw new \Exception("sorry plugin IDs are not yet supported");
        } else {
            $error  = "'{$this->ref}' is not a valid plugin reference.\n";
            $error .= "(was expecting either a file, directory, URL or plugin ID)";
            throw new \Exception($error);
        }
    }
    
    private function download_if_necessary() {
        if ($this->uri) {
            $this->reporter->info('downloading plugin from ' . $this->uri);
            
            $curl = curl_init($this->uri);
            if (is_resource($curl)) {
                
                $plugin_file = tempnam(sys_get_temp_dir(), 'zing-plugin-');
                $fd = fopen($plugin_file, 'w');
                
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION,         true);
                curl_setopt($curl, CURLOPT_FILE,                   $fd);
                
                curl_exec($curl);
                curl_close($curl);
                
                fclose($fd);
                
            } else {
                throw new \Exception("cURL init failed");
            }
            
            $this->file = $plugin_file;
            $this->delete[] = $this->file;
        }
    }
    
    private function extract_if_necessary() {
        if ($this->file) {
            $this->reporter->info('Extracting plugin archive');
            
            $ix = 0;
            do {
                $suffix = substr(sha1($this->file . ($ix++)), 10);
                $this->dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zing-plugin-' . $suffix;
            } while (is_dir($this->dir));
            
            // Assume zip format for now.
            // Obviously we should really be checking file types, checking for driver
            // support, performing content negotiation with the server, etc etc etc.
            $unarchiver = \zing\archive\Support::driver_for_algorithm('zip');
            $unarchiver->extract($this->file, $this->dir);
            
            $this->delete[] = $this->dir;
        }
    }
    
    private function find_plugin_dir() {
        $stack = array($this->dir);
        while (count($stack)) {
            $dir = array_pop($stack);
            if (\zing\plugin\Utils::is_plugin($dir)) {
                $this->dir = $dir;
                return;
            } else {
                $candidates = glob($dir . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);
                foreach ($candidates as $c) {
                    $stack[] = $c;
                }
            }
        }
        throw new \Exception("couldn't find a valid plugin directory in archive, giving up.\n");
    }
    
    private function create_stub() {
        $this->stub = new Stub($this->dir);
        $this->plugin_id = $this->stub->id();
    }
    
    private function check_install_can_proceed() {
        
        $this->reporter->info("Plugin ID: {$this->plugin_id}");
        
        if ($this->manager->is_plugin_installed($this->plugin_id)) {
            throw new \Exception("This plugin is already installed");
        }
        
        $this->reporter->info("Target: {$this->target_dir()}");
        if (is_dir($this->target_dir())) {
            $error  = "Target directory already exists! (is this plugin already installed?)\n";
            $error .= "Delete target directory and retry.";
            throw new \Exception($error);
        }
        
    }
    
    // TODO: this is unix only, plsfix
    private function copy_files_to_target() {
        $this->reporter->info("Copying files to target");
        `cp -R {$this->dir}/ {$this->target_dir()}`;
    }
    
    private function verify() {
        $this->manager->rescan();
        if (!$this->manager->is_plugin_installed($this->plugin_id)) {
            throw new \Exception("Plugin verification failed");
        }
        $this->plugin = $this->manager->plugin($this->plugin_id);
    }
    
    private function add_plugin_class_path() {
        if ($this->plugin->has_classes()) {
            $this->reporter->info("Adding plugin class paths");
            $this->reporter->in();
            foreach ($this->plugin->get_class_paths() as $path) {
                $this->reporter->info('- ' . $path);
                \zing\sys\Config::add_class_path($path);
            }
            $this->reporter->out();
            $this->reporter->warning("Remember to run 'script/phake core:kick' to regenerate autoload map");
        }
    }
    
    private function copy_plugin_files_to_app() {
        if ($this->plugin->has_files()) {
            $this->reporter->info("Copying plugin files to application root");
            $target = ZING_ROOT;
            `cp -R {$this->plugin->get_file_path()}/ $target`;
        }
    }
    
    private function post_install() {
        try {
            $this->reporter->info("Running post-install");
            $this->plugin->post_install();
        } catch (\Exception $e) {
            $error  = "Post-install error: {$e->getMessage()}";
            $this->reporter->warning($error);
        }
    }
    
    private function cleanup() {
        if (count($this->delete)) {
            $this->reporter->info("Cleaning up:");
            $this->reporter->in();
            foreach ($this->delete as $d) {
                $this->reporter->info("- deleting $d ", false);
                if (rm_rf($d)) {
                    $this->reporter->ok();
                } else {
                    $this->reporter->fail();
                }
            }
            $this->reporter->out();
        }
    }
    
    private function target_dir() {
        return ZING_PLUGIN_DIR . DIRECTORY_SEPARATOR . $this->plugin_id;
    }
}
?>