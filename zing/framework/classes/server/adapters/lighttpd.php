<?php
namespace zing\server\adapters;

use zing\sys\OS;

class LighttpdAdapter extends AbstractAdapter
{
    const BASENAME_LIGHTTPD                 = 'lighttpd';
    const BASENAME_PHP_CGI                  = 'php-cgi';
    
    private $path_lighttpd                  = null;
    private $path_php_cgi                   = null;
    private $running                        = true;
    
    public function __construct() {
        $this->locate_binaries();
    }
    
    public function get_server_string() {
        return "lighttpd ({$this->path_lighttpd})";
    }
    
    public function start() {
        
        if (!extension_loaded('pcntl')) {
            $prefix = (PHP_SHLIB_SUFFIX === 'dll') ? 'php_' : '';
            if (!dl($prefix . 'pcntl.' . PHP_SHLIB_SUFFIX)) {
                throw new \Exception("lighttpd adapter requires the pcntl extension\n");
            }
        }
        
        if (file_exists($this->config_path())) {
            throw new \Exception("Temporary config file {$this->config_path()} exists\nIs there already a server on this port?");
        }
        
        file_put_contents($this->config_path(), $this->generate_config_file());
        
        //
        // This is all completely dodgy and I'm not sure I've done it right
        
        $pid = pcntl_fork();
        if ($pid == -1) {
            throw new \Exception("couldn't fork!");
        } elseif ($pid) {
            pcntl_signal(SIGINT, array($this, 'handle_sigint'));
            while ($this->running) {
                pcntl_signal_dispatch();
                sleep(0.2);
            }
            $status = null;
            pcntl_waitpid($pid, &$status);
        } else {
            `{$this->path_lighttpd} -D -f {$this->config_path()}`;
        }
        
        $this->cleanup();
        
    }
    
    public function handle_sigint() {
        $this->running = false;
        $this->cleanup();
    }
    
    private function cleanup() {
        if (file_exists($this->config_path())) {
            unlink($this->config_path());
        }
    }
    
    private function locate_binaries() {
        
        $this->path_lighttpd = OS::find_executable_anywhere(self::BASENAME_LIGHTTPD);
        if (!$this->path_lighttpd) {
            throw new \Exception("Could not locate lighttpd binary - try setting manually");
        }
        
        $this->path_php_cgi = OS::find_executable_anywhere(self::BASENAME_PHP_CGI);
        if (!$this->path_php_cgi) {
            throw new \Exception("Could not locate PHP CGI binary - try setting manually");
        }
    }
    
    private function generate_config_file() {
        $DOCUMENT_ROOT      = ZING_PUBLIC_DIR;
        $PORT               = $this->get_port();
        $PHP_CGI_PATH       = $this->path_php_cgi;
        $PHP_SOCKET_PATH    = $this->socket_path();
        
        ob_start();
        require dirname(__FILE__) . '/lighttpd_template_config.php';
        return ob_get_clean();
    }
    
    private function config_path() {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zing-server.' . $this->get_port() . '.conf';
    }
    
    private function socket_path() {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zing-server.' . $this->get_port() . '.socket';
    }
}
?>