<?php
namespace zing\console;

class Table
{
    private $headers        = array();
    private $content        = array();
    
    private $column_widths  = array();
    private $total_width;
    
    public function add_header($h) { $this->headers[] = $h; }
    public function add_row($r) { $this->content[] = $r; }
    
    public function render() {
        $this->preflight();
        
        $txt  = $this->render_separator();
        $txt .= $this->render_row($this->headers);
        $txt .= $this->render_separator();
        
        foreach ($this->content as $row) {
            $txt .= $this->render_row($row);
        }
        
        $txt .= $this->render_separator();
        
        return $txt;
    }
    
    private function render_separator() {
        $sep = '';
        for ($i = 0, $c = count($this->headers); $i < $c; $i++) {
            $sep .= '+' . str_repeat('-', $this->column_widths[$i] + 2);
        }
        return $sep . "+\n";
    }
    
    private function render_row($row) {
        $out = '';
        for ($i = 0, $c = count($row); $i < $c; $i++) {
            $out .= '| ' . str_pad($row[$i], $this->column_widths[$i]) . ' ';
        }
        return $out . "|\n";
    }
    
    public function toString() {
        return $this->render();
    }
    
    private function preflight() {
        foreach ($this->headers as $h) {
            $this->column_widths[] = $this->strlen($h);
        }
        foreach ($this->content as $row) {
            foreach ($row as $ix => $field) {
                $len = $this->strlen($field);
                if ($len > $this->column_widths[$ix]) {
                    $this->column_widths[$ix] = $len;
                }
            }
        }
        $this->total_width = array_sum($this->column_widths);
    }
    
    private function strlen($txt) {
        return strlen($txt);
    }
}
?>