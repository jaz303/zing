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
        file_put_contents($this->config_path(), $this->generate_config_file());
        
        $exit_status;
        system("{$this->path_lighttpd} -D -f {$this->config_path()}", $exit_status);
        
        if ($exit_status != 0) {
            exit(1);
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