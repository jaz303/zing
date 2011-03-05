<?php
class TestController extends ApplicationController
{
    protected static $filters = array(
        'before' => array(
            'before' => array('except' => 'foo')
        )
    );
    
    protected function before() {
        \zing_load_config('zing.cms.admin');
        $this->layout('application');
        $this->title = "Test Title";
        $this->subtitle = "Test Subtitle";
        $this->admin_structure = \zing\cms\admin\Structure::instance();
    }
        
    public function _index() {
        $this->errors = new \Errors;
        $this->errors->add('foo', 'is not a bar');
        $this->errors->add_to_base('blah blah blah');
    }
    
    public function _set() {
        foreach ($_GET as $k => $v) {
            $this->request->cookies()->set($k, $v);
        }
        $this->render('text', 'OK');
    }
    
    public function _session() {
        $this->session['foo'] += 1;
        $this->render('text', $this->session['foo']);
    }
    
    public function _clear() {
        foreach ($_GET as $k => $v) {
            $this->request->cookies()->remove($k);
        }
        $this->render('text', 'OK');
    }
    
    public function _dump() {
        $cookies = $this->request->cookies();
        $out = '';
        foreach ($cookies as $k => $v) {
            $out .= "$k = $v\n";
        }
        $this->render('text', $out);
    }
}
?>
