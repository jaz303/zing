<?php
namespace zing\sys;

/**
 * SourceBlockWriter writes and removes marked, delimited blocks of code to and
 * from source-files. Zing plugins can use this feature to insert code into the
 * likes of global Javascript and stylesheet resources, as well as Zing's own
 * configuration files.
 *
 * Currently supports PHP, Javascript and CSS.
 *
 * @package zing
 * @author Jason Frame
 */
class SourceBlockWriter
{
	public static $EXTENSION_MAP = array(
		'php'			=> 'php',
		'js'			=> 'javascript',
		'css'			=> 'css'
	);

	public static $LANGUAGE_CONFIG = array(
		'php'			=> array('<?php',	'?>',	'//',	null),
		'javascript'	=> array(null,		null,	'//',	null),
		'css'			=> array(null,		null,	'/*',	'*/')
	);

	public static function append_block_to_file($file, $name, $source, $comment = null) {
		return self::writer_for_file($file)->append_block($name, $source, $comment);
	}

	public static function remove_block_from_file($file, $name) {
		return self::writer_for_file($file)->remove_block($name, $source);
	}

	public static function writer_for_file($file) {
		$extension = array_pop(explode('.', basename($file)));
		if (!isset(self::$EXTENSION_MAP[$extension])) {
			throw new \Exception("Unknown extension: $extension");
		}
		return self::writer_for_language(self::$EXTENSION_MAP[$extension], $file);
	}

	public static function writer_for_language($language, $file) {
		if (!isset(self::$LANGUAGE_CONFIG[$language])) {
			throw new \Exception("Unknown language: $language");
		}
		$config = self::$LANGUAGE_CONFIG[$language];
		return new self($file, $config[0], $config[1], $config[2], $config[3]);
	}

	private $filename;
	private $source_start, $source_end;
	private $comment_start, $comment_end;

	public function __construct($filename, $source_start, $source_end, $comment_start, $comment_end) {
		$this->filename 		= $filename;
		$this->source_start		= $source_start;
		$this->source_end 		= $source_end;
		$this->comment_start	= $comment_start;
		$this->comment_end		= $comment_end;
	}

	public function append_block($name, $source, $comment = null) {
	    
	    $code = array("\n");
	    if ($name) $code[] = $this->commented_line('{begin:' . $name . '}');
	    if ($comment) $code[] = $this->commented_line($comment);
		$code[] = $source . "\n";
		if ($name) $code[] = $this->commented_line('{end:' . $name . '}');
	    
	    $source = $this->read_source();
	    $done = false;
	    
        if ($this->source_start) {
            $source_matcher = '/^\s*' . preg_quote($this->source_end) . '/';
            for ($i = count($source) - 1; $i >= 0; $i--) {
                if (preg_match($source_matcher, $source[$i])) {
                    array_splice($source, $i, 0, $code);
                    $done = true;
                    break;
                }
            }
        }
	    
	    if (!$done) {
	        foreach ($code as $block_line) {
                $source[] = $block_line;
            }
	    }
	    
	    $this->write_source($source);
        
	}

	public function remove_block($name) {
        
        $state = 'out';
        $source = $this->read_source();
        $out = array();
        
        $match_start = '|^\s*';
        if ($this->comment_start) $match_start .= preg_quote($this->comment_start) . '\s*';
        $match_start .= preg_quote('{begin:' . $name . '}');
        if ($this->comment_end) $match_start .= '\s*' . preg_quote($this->comment_end);
        $match_start .= '|';
        
        $match_end = '|^\s*';
        if ($this->comment_start) $match_end .= preg_quote($this->comment_start) . '\s*';
        $match_end .= preg_quote('{end:' . $name . '}');
        if ($this->comment_end) $match_end .= '\s*' . preg_quote($this->comment_end);
        $match_end .= '|';
        
        foreach ($source as $line) {
            switch ($state) {
                case 'out':
                    if (preg_match($match_start, $line)) {
                        $state = 'in';
                    } else {
                        $out[] = $line;
                    }
                    break;
                case 'in':
                    if (preg_match($match_end, $line)) {
                        $state = 'out';
                    }
                    break;
            }
        }
        
        $this->write_source($out);
        
	}

	private function commented_line($comment) {
		$code = $this->comment_start . ' ' . $comment;
		if ($this->comment_end) {
			$code .=  ' ' . $this->comment_end;
		}
		$code .= "\n";
		return $code;
	}
	
	private function read_source() {
	    if (($src = file($this->filename)) === false) {
	        throw new \IOException("couldn't read {$this->filename}");
	    }
	    return $src;
	}
	
	private function write_source($source) {
	    if (!file_put_contents($this->filename, $source)) {
	        throw new \IOException("couldn't write to {$this->filename}");
	    }
	}
}
?>