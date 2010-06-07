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
    
    public static function bad_request($message = '') { throw new Exception(400, $message); }
    public static function unauthorized($message = '') { throw new Exception(401, $message); }
    public static function forbidden($message = '') { throw new Exception(403, $message); }
    public static function not_found($message = '') { throw new Exception(404, $message); }
    public static function method_not_allowed($message = '') { throw new Exception(405, $message); }
    public static function not_acceptable($message = '') { throw new Exception(406, $message); }
    public static function proxy_authentication_required($message = '') { throw new Exception(407, $message); }
    public static function request_time_out($message = '') { throw new Exception(408, $message); }
    public static function conflict($message = '') { throw new Exception(409, $message); }
    public static function gone($message = '') { throw new Exception(410, $message); }
    public static function length_required($message = '') { throw new Exception(411, $message); }
    public static function precondition_failed($message = '') { throw new Exception(412, $message); }
    public static function request_entity_too_large($message = '') { throw new Exception(413, $message); }
    public static function request_uri_too_large($message = '') { throw new Exception(414, $message); }
    public static function unsupported_media_type($message = '') { throw new Exception(415, $message); }
    public static function range_not_satisfiable($message = '') { throw new Exception(416, $message); }
    public static function expectation_failed($message = '') { throw new Exception(417, $message); }
    public static function unprocessable_entity($message = '') { throw new Exception(422, $message); }
    public static function locked($message = '') { throw new Exception(423, $message); }
    public static function failed_dependency($message = '') { throw new Exception(424, $message); }
    public static function upgrade_required($message = '') { throw new Exception(426, $message); }
    
    //
    // Server errors
    
    public static function internal_server_error($message = '') { throw new Exception(500, $message); }
    public static function not_implemented($message = '') { throw new Exception(501, $message); }
    public static function bad_gateway($message = '') { throw new Exception(502, $message); }
    public static function service_unavailable($message = '') { throw new Exception(503, $message); }
    public static function gateway_time_out($message = '') { throw new Exception(504, $message); }
    public static function version_not_supported($message = '') { throw new Exception(505, $message); }
    public static function variant_also_varies($message = '') { throw new Exception(506, $message); }
    public static function insufficient_storage($message = '') { throw new Exception(507, $message); }
    public static function not_extended($message = '') { throw new Exception(510, $message); }
    
    //
    //
    
    private $status;
    
    public function __construct($status, $message = '') {
        parent::__construct($message);
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

class Request implements \ArrayAccess, \IteratorAggregate
{
    public static function rewire(&$array) {
        foreach (array_keys($array) as $k) {
            if ($k[0] == '@') {
                $array[substr($k, 1)] = Date::from_request($array[$k]);
                unset($array[$k]);
            } elseif ($k[0] == '$') {
                $array[substr($k, 1)] = Money::from_request($array[$k]);
                unset($array[$k]);
            } elseif (is_array($array[$k])) {
                self::rewire($array[$k]);
            }
        }
    }
    
    public static function build_request_from_input() {
        $r = new self;
        
        if (isset($_SERVER['AUTH_TYPE']))       $r->auth_type = $_SERVER['AUTH_TYPE'];
        if (isset($_SERVER['PHP_AUTH_USER']))   $r->username = $_SERVER['PHP_AUTH_USER'];
        if (isset($_SERVER['PHP_AUTH_PW']))     $r->password = $_SERVER['PHP_AUTH_PW'];
        
        $host = $_SERVER['HTTP_HOST'];
        if ($p = strpos($host, ':')) $host = substr($host, 0, $p);
        
        $path = $_SERVER['REQUEST_URI'];
        if ($p = strpos($path, '?')) $path = substr($path, 0, $p);
        
        $r->host            = $host;
        $r->port            = (int) $_SERVER['SERVER_PORT'];
        $r->path            = $path;
        $r->query           = new Query($_GET);
        $r->query_string    = $_SERVER['QUERY_STRING'];
        $r->request_uri     = $_SERVER['REQUEST_URI'];
        
        $r->method          = strtolower($_SERVER['REQUEST_METHOD']);
        $r->is_secure       = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off';
        
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $r->requested_with = $_SERVER['HTTP_X_REQUESTED_WITH'];
        }
        
        $r->timestamp       = isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time();
        
        $r->client_ip       = $_SERVER['REMOTE_ADDR'];
        $r->client_port     = $_SERVER['REMOTE_PORT'];
        
        $r->params          = $_POST + $_GET; // POST takes precedence
        
        self::rewire($r->params);
        
        return $r;
    }
    
    private $params             = array();
    
    private $auth_type          = null;
    private $username           = '';
    private $password           = '';
    
    private $url                = null;
    
    private $host;
    private $port;
    private $path;
    private $query              = null;
    private $query_string       = null;
    private $request_uri;
    
    private $method;
    private $is_secure;
    private $requested_with     = null;
    
    private $timestamp;
    
    private $client_ip;
    private $client_port;
    
    public function url() {
        if ($this->url === null) {
            $url = 'http';
            if ($this->is_secure) $url .= 's';
            $url .= '://';
            if ($this->username) {
                $url .= $this->username;
                if ($this->password) {
                    $url .= ':' . $this->password;
                }
                $url .= '@';
            }
            $url .= $this->host;
            if ($this->port != 80) $url .= ':' . $this->port;
            $url .= $this->path;
            if (strlen($this->query_string)) {
                $url .= '?' . $this->query_string;
            }
            $this->url = $url;
        }
        return $this->url;
    }
    
    public function auth_type() { return $this->auth_type; }
    public function username() { return $this->username; }
    public function password() { return $this->password; }
    
    public function host() { return $this->host; }
    public function port() { return $this->port; }
    public function path() { return $this->path; }
    public function query() { return $this->query; }
    public function query_string() { return $this->query_string; }
    public function request_uri() { return $this->request_uri; }
    
    public function host_and_port($force80 = false) {
        return $this->host . (($this->port != 80 || $force80) ? (':' . $this->port) : '');
    }
    
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
    
    /**
     * Returns a reference to the request parameters array.
     *
     * @return a reference to the request parameters array.
     */
    public function &params() { return $this->params; }
    
    //
    // A bit hacky - exists so we can merge route parameters
    
    public function merge_params(array $stuff) {
        foreach ($stuff as $k => $v) $this->params[$k] = $v;
    }
    
    //
    // ArrayAccess/IteratorAggregate
    
    public function offsetExists($offset) { return isset($this->params[$offset]); }
    public function offsetGet($offset) { return $this->params[$offset]; }
    public function offsetSet($offset, $value) { $this->params[$offset] = $value; }
    public function offsetUnset($offset) { unset($this->params[$offset]); }
    
    public function getIterator() { return new \ArrayIterator($this->params); }
}

class Query implements \ArrayAccess, \IteratorAggregate
{
    private $params;
    
    public function __construct($query = array()) {
        if (is_array($query)) {
            $this->params = $query;
        } else {
            $this->params = array();
            parse_str($query, $this->params);
        }
    }
    
    public function to_string($with_question_mark = false) {
        $query = http_build_query($this->params);
        if ($with_question_mark && strlen($query)) {
            return '?' . $query;
        } else {
            return $query;
        }
    }
    
    public function to_string_with_trailing_assignment($key, $with_question_mark = false) {
        $query = clone $this;
        unset($query[$key]);
        $query[$key] = '';
        return $query->to_string($with_question_mark);
    }
    
    //
    // ArrayAccess/IteratorAggregate
    
    public function offsetExists($offset) { return isset($this->params[$offset]); }
    public function offsetGet($offset) { return $this->params[$offset]; }
    public function offsetSet($offset, $value) { $this->params[$offset] = $value; }
    public function offsetUnset($offset) { unset($this->params[$offset]); }
    
    public function getIterator() { return new \ArrayIterator($this->params); }
}

class AbstractResponse
{
    protected $status;
    protected $headers;
    
    public function __construct() {
        $this->status   = Constants::OK;
        $this->headers  = new Headers;
    }
    
    public function get_status() { return $this->status; }
    public function set_status($status) { $this->status = $status; }
    
    public function get_content_type() { return $this->headers->get_first('Content-Type'); }
    public function set_content_type($content_type) { $this->headers->set('Content-Type', $content_type); }
    
    public function get_headers() { return $this->headers; }
    public function has_header($header) { return $this->headers->has($header); }
    public function add_header($header, $value) { $this->headers->add($header, $value); }
    public function set_header($header, $value) { $this->headers->set($header, $value); }
    
    protected function send_headers() {
        header(Constants::PROTOCOL_VERSION . " " . $this->status . Constants::text_for_status($this->status));
        foreach ($this->headers as $h) {
            header($h);
        }
    }
}

class Response extends AbstractResponse
{
    public static function redirect($absolute_url, $permanent = false) {
        $response = new Response;
        $response->set_status($permanent ? Constants::MOVED_PERMANENTLY : Constants::MOVED_TEMPORARILY);
        $response->set_header('Location', $absolute_url);
        return $response;
    }
    
    private $body;
    
    public function __construct() {
        parent::__construct();
        $this->body = '';
        $this->headers->set('Content-Type', 'text/html');
    }
    
    public function get_body() { return $this->body; }
    public function set_body($body) { $this->body = $body; }
    public function write($string) { $this->body .= $string; }
    public function write_line($string) { $this->body .= $string . "\n"; }
    
    public function send() {
        
        if (!$this->headers->has("Content-Length")) {
            $this->headers->set("Content-Length", strlen($this->body));
        }
        
        $this->send_headers();
        echo $this->body;
    
    }
}

class FileResponse extends AbstractResponse
{
    private $path;
    
    private $size           = null;
    private $filename       = null;
    private $file_type      = null;
    
    public function __construct($path) {
        parent::__construct();
        $this->path = $path;
    }
    
    public function get_size() {
        return $this->size === null
                ? filesize($this->path)
                : $this->size;
    }
    
    public function get_filename() {
        return $this->filename === null
                ? basename($this->path)
                : $this->filename;
    }
    
    public function get_file_type() {
        return $this->file_type === null
                ? \MIME::for_filename($this->path)
                : $this->file_type;
    }
    
    public function set_size($sz) { $this->size = (int) $sz; }
    public function set_filename($f) { $this->filename = $f; }
    public function set_file_type($t) { $this->file_type = $c; }
    
    public function send() {
        $this->set_content_type($this->get_file_type());
        $this->set_header('Content-Length', $this->get_size());
        
        $this->send_headers();
        
        if (!$fd = fopen($this->path, 'r')) {
            throw new \IOException("couldn't open $this->path for reading");
        }
        
        fpassthru($fd);
        fclose($fd);
    }
}

/**
 * Session handling
 * This will have to do for now, ultimately things will have to change as this
 * thing outputs headers itself.
 *
 * In order to future proof against changes, it's recommended that you don't
 * ever instantiate a Session instance yourself as the constructor parameters
 * may change without warning.
 */
class Session implements \ArrayAccess
{
    private $flash_now;
    
    public function __construct() {
        session_start();
        $this->flash_now = array();
        if (isset($_SESSION['__flash_next'])) {
            $this->flash_now = $_SESSION['__flash_next'];
            $_SESSION['__flash_next'] = array();
        }
    }
    
    public function flash($type, $message) {
        $_SESSION['__flash_next'][] = array('type' => $type, 'message' => $message);
    }
    
    public function current_flash() {
        return $this->flash_now;
    }
    
    public function finalize() {
        session_commit();
    }
    
    public function offsetExists($k) {
        return array_key_exists($k, $_SESSION);
    }
    
    public function offsetGet($k) {
        return $_SESSION[$k];
    }
    
    public function offsetSet($k, $v) {
        $_SESSION[$k] = $v;
    }
    
    public function offsetUnset($k) {
        unset($_SESSION[$k]);
    }
}
?>