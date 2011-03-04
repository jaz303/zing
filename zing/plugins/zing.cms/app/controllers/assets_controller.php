<?php
// TODO: caching!!!

class AssetsController extends \zing\Controller
{
    protected $config;
    
    protected function init() {
        parent::init();
        $this->auto_session(false);
        $this->config = WarpConfig::load();
    }
    
    public function _show() {
        
        $source = $this->config->get_source($this->param('source'));
        if (!$source) throw new \NotFoundException("unknown asset source - {$this->param('source')}");
        
        $profile = $this->config->get_profile($this->param('profile'));
        if (!$profile) throw new \NotFoundException("unknown asset profile - {$this->param('profile')}");
        
        $blob = $source->fetch($this->param('id'));
        if (!$blob) throw new \NotFoundException("source '{$this->param('source')}' couldn't find asset with ID '{$this->param('id')}'");
        
        $processed_blob = $profile->apply_to($blob);
        
        if (!$processed_blob) {
            throw new \NotFoundException("asset warp failed");
        }
        
        $this->render('blob', $processed_blob);
            
    }
}
?>