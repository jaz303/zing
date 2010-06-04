<?php
namespace zing\mail;

/**
 *
 * @package zing
 *
 * @guide zing.mail
 * @config zing.mail.charset Default character set for new messages
 * @config zing.mail.transport Class name of default transport
 */

/**
 * Thrown when an error occurs sending mail.
 */
class MailSendException extends \Exception {}

class Message
{
    public static function build() {
		return new self(null, Transport::default_transport());
	}

    private $transport		    = null;
    
    private $to                 = array();
    private $cc                 = array();
    private $bcc                = array();

    private $from_address       = null;
    private $from_name          = null;
    
    private $reply_to_address   = null;
    private $reply_to_name      = null;
    
    private $charset            = null;
    
    private $subject		    = '';
	private $text			    = null;
	private $html			    = null;
	
	public function __construct($charset = null, $transport = null) {
	    if ($charset === null) {
	        global $_ZING;
	        if (isset($_ZING['zing.mail.charset'])) {
	            $charset = $_ZING['zing.mail.charset'];
	        } else {
	            $charset = 'iso-8859-1';
	        }
	    }
	    $this->charset   = $charset;
	    $this->transport = $transport;
	}
	
	public function send($transport = null) {
		if ($transport === null) {
			$transport = $this->transport;
		}
		if ($transport === null) {
			throw new \InvalidArgumentException("can't send message - no transport");
		}
		$transport->send_message($this);
	}
	
	//
	// Getters
	
	public function get_to() { return $this->to; }
	public function get_cc() { return $this->cc; }
	public function get_bcc() { return $this->bcc; }
	
	public function has_cc() { return count($this->cc) > 0; }
	public function has_bcc() { return count($this->bcc) > 0; }
	
	public function get_from_address() { return $this->from_address; }
	public function get_from_name() { return $this->from_name; }
	
	public function has_from_address() { return $this->from_address !== null; }
	
	public function get_reply_to_address() { return $this->reply_to_address; }
	public function get_reply_to_name() { return $this->reply_to_name; }
	
	public function has_reply_to_address() { return $this->reply_to_address !== null; }
	
	public function get_charset() { return $this->charset; }
	
	public function get_subject() { return $this->subject; }
	public function get_text_content() { return $this->text; }
	public function get_html_content() { return $this->html; }
	
	public function has_content() { return $this->text !== null || $this->html !== null; }
	public function has_mixed_content() { return $this->text !== null && $this->html !== null; }
	public function has_text_content() { return $this->text !== null; }
	public function has_html_content() { return $this->html !== null; }
	
	//
	// Setters
	
	public function to($address, $name = null) {
	    $this->to[] = array($address, $name);
	    return $this;
	}
	
	public function cc($address, $name = null) {
	    $this->cc[] = array($address, $name);
	    return $this;
	}
	
	public function bcc($address, $name = null) {
	    $this->bcc[] = array($address, $name);
	    return $this;
	}
	
	public function from($address, $name = null) {
	    $this->from_address = $address;
	    $this->from_name    = $name;
	    return $this;
	}
	
	public function reply_to($address, $name = null) {
	    $this->reply_to_address = $address;
	    $this->reply_to_name    = $name;
	    return $this;
	}
	
	public function set_charset($c) { $this->charset = $c; }
	
	public function subject($s) { $this->subject = $s; return $this; }
	public function text_content($t) { $this->text = $t; return $this; }
	public function html_content($h) { $this->html = $h; return $this; }
}

abstract class Transport
{
    protected static $default_transport = null;
    
    public static function default_transport() {
		if (self::$default_transport === null) {
			global $_ZING;
			if (isset($_ZING['zing.mail.transport'])) {
				$class = $_ZING['zing.mail.transport']['class'];
				$config = $_ZING['zing.mail.transport'];
			} else {
				$class = '\\zing\\mail\\MailTransport';
				$config = array();
			}
			self::$default_transport = new $class($config);
		}
		return self::$default_transport;
	}
	
	public function __construct(array $config = array()) {}
	
	protected function format_address($address, $name = null) {
	    if (is_array($address)) {
	        $name = $address[1];
	        $address = $address[0];
	    }
	    if ($name !== null) {
	        return "$name <$address>";
	    } else {
	        return $address;
	    }
	}
	
	protected function format_address_list($list) {
	    return implode(', ', array_map(array($this, 'format_address'), $list));
	}
	
	protected function format_header($header, $value = null) {
	    if (is_array($header)) {
	        return "{$header[0]}: {$header[1]}";
	    } else {
	        return "$header: $value";
	    }
	}
	
	protected function format_headers($headers, $separator = "\r\n") {
        return implode($separator, array_map(array($this, 'format_header'), $headers));
	}
}

class MailTransport extends Transport
{
    public function send_message($message) {
		if (!mail($this->format_address_list($message->get_to()),
		          $message->get_subject(),
		          $this->render_body($message),
		          $this->render_headers($message))) {
			throw new MailSendException;
		}
	}
	
	private function render_body($message) {
	    if ($message->has_mixed_content()) {
	        
	    } elseif ($message->has_text_content()) {
	        return $message->get_text_content();
	    } elseif ($message->has_html_content()) {
	        return $message->get_html_content();
	    } else {
	        return '';
	    }
	}
	
	private function render_headers($message) {
	    
	    $headers = array();
	    
	    if ($message->has_from_address()) {
	        $headers[] = array(
	            'From',
	            $this->format_address(
	                $message->get_from_address(),
	                $message->get_from_name()
	            )
	        );
	    }
	    
	    if ($message->has_reply_to_address()) {
	        $headers[] = array(
	            'Reply-To',
	            $this->format_address(
	                $message->get_reply_to_address(),
	                $message->get_reply_to_name()
	            )
	        );
	    }
	    
	    if ($message->has_cc()) {
	        $headers[] = array(
	            'Cc',
	            $this->format_address_list($message->get_cc())
	        );
	    }
	    
	    if ($message->has_bcc()) {
	        $headers[] = array(
	            'Bcc',
	            $this->format_address_list($message->get_bcc())
	        );
	    }
	    
	    if ($message->has_mixed_content()) {
	        
	    } elseif ($message->has_html_content()) {
	        $headers[] = array('MIME-Version', '1.0');
	        $headers[] = array('Content-type', 'text/html; charset=' . $message->get_charset());
	    } else {
	        $headers[] = array('Content-type', 'text/plain; charset=' . $message->get_charset());
	    }
	    
	    return $this->format_headers($headers);
	    
	}
}
?>