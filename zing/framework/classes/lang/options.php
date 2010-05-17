<?php
namespace gdb\lang;

/**
 * OptionParser parses JSON-like strings into PHP arrays. It exists to provide
 * a slightly more bearable syntax for passing options hashes around.
 *
 * The cynics will say this is a half-assed JSON parser. A few subtle differences,
 * however - no outermost braces required, keys need not be quoted, keys can contain
 * dots/dashes, various alternatives for true/false, possibility for adding
 * date/currency syntax in future.
 *
 * Example syntax:
 * "foo: 'bar', bar.bif: {baz: null, bleem: 'foo'}"
 *
 * Obviously this comes with a bunch of runtime parsing overhead so use with care!
 */
class OptionParser
{
    const ID            = 1;
    const COLON         = 2;
    const LBRACKET      = 3;
    const RBRACKET      = 4;
    const LBRACE        = 5;
    const RBRACE        = 6;
    const COMMA         = 7;
    const EOF           = 8;
    
    const STRING        = 100;
    const TRUE          = 101;
    const FALSE         = 102;
    const NULL          = 103;
    const INTEGER       = 104;
    const FLOAT         = 105;
    
    private $string, $pos, $text, $curr;
    
    public function parse($option_string) {
        $this->string = $option_string;
        $this->len = strlen($this->string);
        $this->pos = 0;
        try {
            $this->curr = $this->tok();
            $hash = $this->parse_inner_hash();
            $this->accept(self::EOF);
            return $hash;
        } catch (Exception $e) {
            echo $e->getTraceAsString();
            return null;
        }
    }
    
    private function parse_hash() {
        $this->accept(self::LBRACE);
        $hash = $this->parse_inner_hash();
        $this->accept(self::RBRACE);
        return $hash;
    }
    
    private function parse_inner_hash() {
        $hash = array();
        $key = $this->text;
        $this->accept(self::ID);
        $this->accept(self::COLON);
        $hash[$key] = $this->parse_value();
        while ($this->curr() == self::COMMA) {
            $this->accept_current();
            $key = $this->text;
            $this->accept(self::ID);
            $this->accept(self::COLON);
            $hash[$key] = $this->parse_value();
        }
        return $hash;
    }
    
    private function parse_array() {
        $out = array();
        $this->accept(self::LBRACKET);
        if ($this->curr() != self::RBRACKET) {
            $out[] = $this->parse_value();
            while ($this->curr() == self::COMMA) {
                $this->accept_current();
                $out[] = $this->parse_value();
            }
        }
        $this->accept(self::RBRACKET);
        return $out;
    }

    private function parse_value() {
        $curr = $this->curr();
        if ($curr >= 100) {
            switch ($curr) {
                case self::STRING:
                    $value = $this->text;
                    break;
                case self::TRUE:
                    $value = true;
                    break;
                case self::FALSE:
                    $value = false;
                    break;
                case self::NULL:
                    $value = null;
                    break;
                case self::INTEGER:
                    $value = (int) $this->text;
                    break;
                case self::FLOAT:
                    $value = (float) $this->text;
                    break;
            }
            $this->accept_current();
            return $value;
        } elseif ($curr == self::LBRACE) {
            return $this->parse_hash();
        } elseif ($curr == self::LBRACKET) {
            return $this->parse_array();
        } else {
            $this->parse_error("value expected");
        }
    }
    
    private function tok() {
        while ($this->pos < $this->len) {
            $c = $this->string[$this->pos++];
            $this->text = null;
            if (ctype_space($c)) { continue; }
            if (ctype_alpha($c) || $c == '_') {
                $this->text = $c;
                while ($this->pos < $this->len) {
                    $c = $this->string[$this->pos++];
                    if (ctype_alpha($c) || $c == '_' || $c == '-' || $c == '.') {
                        $this->text .= $c;
                    } else {
                        $this->pos--;
                        break;
                    }
                }
                switch ($this->text) {
                    case 'true': case 'yes':
                        return self::TRUE;
                    case 'false': case 'no':
                        return self::FALSE;
                    case 'null':
                        return self::NULL;
                    default:
                        return self::ID;
                }
            } elseif ($c == '"' || $c == '\'') {
                $q = $c;
                $s = false;
                $this->text = '';
                while ($this->pos < $this->len) {
                    $c = $this->string[$this->pos++];
                    if ($s) {
                        $this->text .= $c;
                        $s = false;
                    } else {
                        if ($c == $q) {
                            return self::STRING;
                        } elseif ($c == '\\') {
                            $s = true;
                        } else {
                            $this->text .= $c;
                        }
                    }
                }
                $this->parse_error();
            } elseif (ctype_digit($c) || $c == '-' || $c == '+') {
                $this->text = $c;
                while ($this->pos < $this->len) {
                    $c = $this->string[$this->pos++];
                    if (ctype_digit($c)) {
                        $this->text .= $c;
                    } elseif ($c == '.') {
                        $this->text .= '.';
                        // buggy; will accept "1." as valid float
                        while ($this->pos < $this->len) {
                            $c = $this->string[$this->pos++];
                            if (ctype_digit($c)) {
                                $this->text .= $c;
                            } else {
                                $this->pos--;
                                return self::FLOAT;
                            }
                        }
                        return self::FLOAT;
                    } else {
                        $this->pos--;
                        return self::INTEGER;
                    }
                }
                return self::INTEGER;
            } elseif ($c == '[') {
                return self::LBRACKET;
            } elseif ($c == ']') {
                return self::RBRACKET;
            } elseif ($c == '{') {
                return self::LBRACE;
            } elseif ($c == '}') {
                return self::RBRACE;
            } elseif ($c == ':') {
                return self::COLON;
            } elseif ($c == ',') {
                return self::COMMA;
            } else {
                $this->parse_error();
            }
        }
        return self::EOF;
    }
    
    private function curr() {
        return $this->curr;
    }
    
    private function accept($token) {
        if ($this->curr != $token) $this->parse_error();
        $this->curr = $this->tok();
    }
    
    private function accept_current() {
        $this->curr = $this->tok();
    }
    
    private function parse_error($msg = '') {
        throw new Exception($msg);
    }
}
?>