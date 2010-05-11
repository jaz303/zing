<?php
namespace zing\http;

class Constants
{
    const PROTOCOL_VERSION      = 'HTTP/1.1';
    
    const OK                    = 200;
    const MOVED_PERMANENTLY     = 301;
    const MOVED_TEMPORARILY     = 302;
    const BAD_REQUEST           = 400;
    const UNAUTHORIZED          = 401;
    const FORBIDDEN             = 403;
    const NOT_FOUND             = 404;
    const ERROR                 = 500;

    public static $STATUS_CODES = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',

        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',

        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Moved Temporarily',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',

        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time Out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',

        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time Out',
        505 => 'Version Not Supported',
        506 => 'Variant Also Varies',
        507 => 'Insufficient Storage',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended',
        530 => 'User access denied'
    );
    
    public static function text_for_status($status) {
        return self::$STATUS_CODES[(int) $status];
    }
}

class Exception extends \Exception
{
    //
    // Client errors
    
    public static function bad_request() { throw new Exception(400); }
    public static function unauthorized() { throw new Exception(401); }
    public static function forbidden() { throw new Exception(403); }
    public static function not_found() { throw new Exception(404); }
    public static function method_not_allowed() { throw new Exception(405); }
    public static function not_acceptable() { throw new Exception(406); }
    public static function proxy_authentication_required() { throw new Exception(407); }
    public static function request_time_out() { throw new Exception(408); }
    public static function conflict() { throw new Exception(409); }
    public static function gone() { throw new Exception(410); }
    public static function length_required() { throw new Exception(411); }
    public static function precondition_failed() { throw new Exception(412); }
    public static function request_entity_too_large() { throw new Exception(413); }
    public static function request_uri_too_large() { throw new Exception(414); }
    public static function unsupported_media_type() { throw new Exception(415); }
    public static function range_not_satisfiable() { throw new Exception(416); }
    public static function expectation_failed() { throw new Exception(417); }
    public static function unprocessable_entity() { throw new Exception(422); }
    public static function locked() { throw new Exception(423); }
    public static function failed_dependency() { throw new Exception(424); }
    public static function upgrade_required() { throw new Exception(426); }
    
    //
    // Server errors
    
    public static function internal_server_error() { throw new Exception(500); }
    public static function not_implemented() { throw new Exception(501); }
    public static function bad_gateway() { throw new Exception(502); }
    public static function service_unavailable() { throw new Exception(503); }
    public static function gateway_time_out() { throw new Exception(504); }
    public static function version_not_supported() { throw new Exception(505); }
    public static function variant_also_varies() { throw new Exception(506); }
    public static function insufficient_storage() { throw new Exception(507); }
    public static function not_extended() { throw new Exception(510); }
    
    //
    //
    
    private $status;
    
    public function __construct($status) {
        $this->status = (int) $status;
    }
    
    public function get_status() {
        return $this->status;
    }
    
    public function get_status_string() {
        return Constants::text_for_status($this->status);
    }    
}

class Headers implements \ArrayAccess, \IteratorAggregate
{
    private $headers = array();

    public function has($key) {
        return isset($this->headers[$key]);
    }
    
    public function get_first($key, $default = null) {
        return isset($this->headers[$key]) ? $this->headers[$key][0] : $default;
    }
    
    public function get_all($key) {
        return isset($this->headers[$key]) ? $this->headers[$key] : array();
    }
    
    public function set($key, $value) {
        $this->remove($key);
        $this->add($key, $value);
    }
    
    public function add($key, $value) {
        $this->headers[$key][] = $value;
    }
    
    public function remove($key) {
        if (isset($this->headers[$key])) unset($this->headers[$key]);
    }
    
    //
    // ArrayAccess
    
    public function offsetExists($key) { return $this->has($key); }
    public function offsetGet($key) { return $this->get_first($key); }
    public function offsetSet($key, $value) { return $this->set($key, $value); }
    public function offsetUnset($key) { return $this->remove($key); }
    
    //
    // IteratorAggregate
    
    public function getIterator() {
        $out = array();
        foreach ($this->headers as $key => $headers) {
            foreach ($headers as $header) {
                $out[] = "$key: $header\n";
            }
        }
        return new \ArrayIterator($out);
    }
}

class Request
{
    public static function build_request_from_input() {
        $r = new self;
        
        $host = $_SERVER['HTTP_HOST'];
        if ($p = strpos($host, ':')) $host = substr($host, 0, $p);
        
        $path = $_SERVER['REQUEST_URI'];
        if ($p = strpos($path, '?')) $path = substr($path, 0, $p);
        
        $r->host        = $host;
        $r->port        = (int) $_SERVER['SERVER_PORT'];
        $r->path        = $path;
        $r->request_uri = $_SERVER['REQUEST_URI'];
        
        $r->method      = strtolower($_SERVER['REQUEST_METHOD']);
        
        $r->is_secure   = isset($_SERVER['HTTPS']);
        
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $r->requested_with = $_SERVER['HTTP_X_REQUESTED_WITH'];
        }
        
        $r->timestamp   = isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time();
        
        $r->client_ip   = $_SERVER['REMOTE_ADDR'];
        $r->client_port = $_SERVER['REMOTE_PORT'];
        
        return $r;
    }
    
    private $host;
    private $port;
    private $path;
    private $request_uri;
    
    private $method;
    private $is_secure;
    private $requested_with     = null;
    
    private $timestamp;
    
    private $client_ip;
    private $client_port;
    
    public function url() {
        $url = 'http';
        if ($this->is_secure) $url .= 's';
        $url .= $this->host;
        if ($this->port != 80) $url .= ':' . $this->port;
        $url .= $this->path;
        return $url;
    }
    
    public function host() { return $this->host; }
    public function port() { return $this->port; }
    public function path() { return $this->path; }
    public function request_uri() { return $this->request_uri; }
    
    public function method() { return $this->method; }
    public function is_secure() { return $this->is_secure; }
    
    public function is_get() { return $this->method == 'get'; }
    public function is_post() { return $this->method == 'post'; }
    public function is_put() { return $this->method == 'put'; }
    public function is_delete() { return $this->method == 'delete'; }
    public function is_head() { return $this->method == 'head'; }
    
    public function is_xhr() { return $this->requested_with == 'xmlhttprequest'; }
    
    public function timestamp() { return $this->timestamp; }
    
    public function client_ip() { return $this->client_ip; }
    public function client_port() { return $this->client_port; }
}

class Response
{
    public static function redirect($absolute_url, $permanent = false) {
        $response = new Response;
        $response->set_status($permanent ? Constants::MOVED_PERMANENTLY : Constants::MOVED_TEMPORARILY);
        $response->set_header('Location', $absolute_url);
        return $response;
    }
    
    private $status;
    private $headers;
    private $body;
    
    public function __construct() {
        $this->status   = Constants::OK;
        $this->headers  = new Headers;
        $this->body     = '';
        
        $this->headers->set('Content-Type', 'text/html');
    }
    
    public function get_status() { return $this->status; }
    public function set_status($status) { $this->status = $status; }
    
    public function get_content_type() { return $this->headers->get_first('Content-Type'); }
    public function set_content_type($content_type) { $this->headers->set('Content-Type', $content_type); }
    
    public function get_headers() { return $this->headers; }
    public function has_header($header) { return $this->headers->has($header); }
    public function add_header($header, $value) { $this->headers->add($header, $value); }
    public function set_header($header, $value) { $this->headers->set($header, $value); }
    
    public function get_body() { return $this->body; }
    public function set_body($body) { $this->body = $body; }
    public function write($string) { $this->body .= $string; }
    public function write_line($string) { $this->body .= $string . "\n"; }
    
    public function send() {
        
        if (!$this->headers->has("Content-Length")) {
            $this->headers->set("Content-Length", strlen($this->body));
        }
        
        header(Constants::PROTOCOL_VERSION . " " . $this->status . Constants::text_for_status($this->status));
        foreach ($this->headers as $h) {
            header($h);
        }
        
        echo $this->body;
    
    }
}
?>