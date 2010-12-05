<?php
namespace zing\cms\assets;

// TODO: caching!!!

class WarpConfig
{
    private $sources = array();
    private $profiles = array();
    
    public function get_source($name) {
        return isset($this->sources[$name]) ? $this->sources[$name] : null;
    }
    
    public function get_profile($name) {
        return isset($this->profiles[$name]) ? $this->profiles[$name] : null;
    }
    
    /**
     * Configure asset warping for a default Zing! CMS installation.
     * This sets up:
     *  * sources for CMS uploaded assets and thumbs
     *  * an 'original' profile that leaves files unaltered
     *  * a 'system-default' profile that constrains images to 640x640
     *  * a 'system-thumbnail' profile that constrains images to 50x50
     */
    public function defaults() {
        $this->source('asset', '/^\d+$/', function($id) {
            // find asset by ID
            // return blob
        });
        
        $this->source('thumb', '/^\d+$/', function($id) {
            // find asset by ID
            // return blob
        });
        
        $this->profile('original', function($blob) {});
        
        $this->image_profile('system-default', function($image) {
            $image->constrain(640, 640);
        });
        
        $this->image_profile('system-thumbnail', function($image) {
            $image->constrain(50, 50);
        });
    }
    
    public function source($name, $id_pattern, $fetch) {
        $this->sources[$name] = new WarpSource($id_pattern, $fetch);
    }
    
    public function profile($name, $transform) {
        $this->profiles[$name] = new WarpProfile($transform);
    }
    
    public function image_profile($name, $transform) {
        $this->profiles[$name] = new WarpProfile(function($blob) use ($transform) {
            if ($blob->is_image()) {
                $image = $blob->to_image();
                $out = $transform($image);
                if (!$out && $out !== false) $out = $image;
                return $out;
            } else {
                
            }
        });
    }
}

class WarpSource
{
    private $valid_id;
    private $fetch;
    
    public function __construct($valid_id, $fetch) {
        $this->valid_id = $valid_id;
        $this->fetch = $fetch;
    }
    
    public function fetch($id) {
        if ($this->valid_id && !preg_match($this->valid_id, $id)) {
            return null;
        }
        $fetch = $this->fetch;
        $blob = $fetch($id);
        if (is_string($blob)) {
            if (preg_match('|^https?://|', $blob)) { // $blob is URL of content
                $ch = curl_init($blob);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $data = curl_exec($ch);
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if ($code == 200) {
                    $info = curl_getinfo($ch);
                    $blob = new \MemoryBlob($data, $info['content_type']);
                } else {
                    $blob = null;
                }
                curl_close($ch);
            } else { // $blob is path to file...
                $blob = new \FileBlob($blob);
            }
        }
        return $blob;
    }
}

class WarpProfile
{
    private $transform;
    
    public function __construct($transform) {
        $this->transform = $transform;
    }
    
    public function apply_to($blob) {
        $transform = $this->transform;
        $out = $transform($blob);
        if (!$out && $out !== false ) $out = $blob;
        return $out;
    }
}

class AssetsController extends \zing\Controller
{
    protected $config;
    
    protected function init() {
        parent::init();
        $this->config = new WarpConfig;
        zing_load_config('zing.cms.assets', array('WARP' => $this->config));
    }
    
    public function _show() {
        
        $source = $this->config->get_source($this->param('source'));
        if (!$source) throw new \NotFoundException("unknown asset source - {$this->param('source')}");
        
        $profile = $this->config->get_profile($this->param('profile'));
        if (!$profile) throw new \NotFoundException("unknown asset profile - {$this->param('profile')}");
        
        $blob = $source->fetch($this->param('id'));
        if (!$blob) throw new \NotFoundException("source '{$this->param('source')}' couldn't find asset with ID '{$this->param('id')}'");
        
        $processed_blob = $profile->apply_to($blob);
        
        // TODO: it might be an idea to give controller a send_blob() method.
        
        if (!$processed_blob) {
            throw new \NotFoundException("asset warp failed");
        } else {
            // by sheer luck both Blob and Image have methods with the same names
            // duck typing ftw.
            $this->response->set_content_type($processed_blob->mime_type());
            $this->response->set_body($processed_blob->data());
        }
        
        $this->set_performed(true);
        
    }
}
?>