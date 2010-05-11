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
        100 => 'CONTINUE',
        101 => 'SWITCHING_PROTOCOLS',
        102 => 'PROCESSING',

        200 => 'OK',
        201 => 'CREATED',
        202 => 'ACCEPTED',
        203 => 'NON_AUTHORITATIVE',
        204 => 'NO_CONTENT',
        205 => 'RESET_CONTENT',
        206 => 'PARTIAL_CONTENT',
        207 => 'MULTI_STATUS',

        300 => 'MULTIPLE_CHOICES',
        301 => 'MOVED_PERMANENTLY',
        302 => 'MOVED_TEMPORARILY',
        303 => 'SEE_OTHER',
        304 => 'NOT_MODIFIED',
        305 => 'USE_PROXY',
        307 => 'TEMPORARY_REDIRECT',

        400 => 'BAD_REQUEST',
        401 => 'UNAUTHORIZED',
        402 => 'PAYMENT_REQUIRED',
        403 => 'FORBIDDEN',
        404 => 'NOT_FOUND',
        405 => 'METHOD_NOT_ALLOWED',
        406 => 'NOT_ACCEPTABLE',
        407 => 'PROXY_AUTHENTICATION_REQUIRED',
        408 => 'REQUEST_TIME_OUT',
        409 => 'CONFLICT',
        410 => 'GONE',
        411 => 'LENGTH_REQUIRED',
        412 => 'PRECONDITION_FAILED',
        413 => 'REQUEST_ENTITY_TOO_LARGE',
        414 => 'REQUEST_URI_TOO_LARGE',
        415 => 'UNSUPPORTED_MEDIA_TYPE',
        416 => 'RANGE_NOT_SATISFIABLE',
        417 => 'EXPECTATION_FAILED',
        422 => 'UNPROCESSABLE_ENTITY',
        423 => 'LOCKED',
        424 => 'FAILED_DEPENDENCY',
        426 => 'UPGRADE_REQUIRED',

        500 => 'INTERNAL_SERVER_ERROR',
        501 => 'NOT_IMPLEMENTED',
        502 => 'BAD_GATEWAY',
        503 => 'SERVICE_UNAVAILABLE',
        504 => 'GATEWAY_TIME_OUT',
        505 => 'VERSION_NOT_SUPPORTED',
        506 => 'VARIANT_ALSO_VARIES',
        507 => 'INSUFFICIENT_STORAGE',
        510 => 'NOT_EXTENDED'
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
    public static function factory() {
        
    }
    
    private $path;
    private $method;
    
    public function path() { return $this->path; }
    public function method() { return $this->method; }
}

class Response
{
    public static function redirect($absolute_url, $permanent = false) {
        $response = new Response;
        $response->set_status($permanent ? 301 : 302);
        $response->set_header('Location', $absolute_url);
        return $response;
    }
    
    private $status;
    private $headers;
    private $body;
    
    public function __construct() {
        $this->status   = 200;
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