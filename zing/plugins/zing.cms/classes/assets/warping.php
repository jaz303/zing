<?php
namespace zing\cms\assets;



class WarpConfig
{
    public static function load() {
        $config = new self;
        zing_load_config('zing.cms.assets.warp', array('WARP' => $config));
        return $config;
    }
    
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
            $asset = \Asset::find($id);
            return $asset->get_file_blob();
        });
        
        $this->source('thumb', '/^\d+$/', function($id) {
            $asset = \Asset::find($id);
            $blob = $asset->get_thumb_blob();
            if (!$blob && $asset->is_web_safe_image()) {
                $blob = $asset->get_file_blob();
            }
            return $blob;
        });
        
        $this->profile('original', function($blob) {})
             ->title('Original');
        
        $this->image_profile('system-default', function($image) {
            $image->constrain(640, 640);
        })->hide();
        
        $this->image_profile('system-thumbnail', function($image) {
            $image->constrain(50, 50);
        })->hide();
    }
    
    public function source($name, $id_pattern, $fetch) {
        $source = new WarpSource($id_pattern, $fetch);
        $this->sources[$name] = $source;
        return $source;
    }
    
    public function profile($name, $transform) {
        $profile = new WarpProfile($transform);
        $profile->title($name);
        $this->profiles[$name] = $profile;
        return $profile;
    }
    
    public function image_profile($name, $transform) {
        return $this->profile($name, function($blob) use ($transform) {
            if ($blob->is_image()) {
                $image = $blob->to_image();
                $out = $transform($image);
                if (!$out && $out !== false) $out = $image;
                return $out;
            } else {
                
            }
        });
    }
    
    public function profile_index() {
        $out = array();
        foreach ($this->profiles as $name => $profile) {
            if (!$profile->is_hidden()) {
                $out[$name] = $profile->get_title();
            }
        }
        return $out;
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
    private $title;
    private $hidden         = false;
    
    public function __construct($transform) {
        $this->transform = $transform;
    }
    
    public function title($title) { $this->title = $title; return $this; }
    public function get_title() { return $this->title; }
    
    public function hide() { $this->hidden = true; return $this; }
    public function is_hidden() { return $this->hidden; }
    
    public function apply_to($blob) {
        $transform = $this->transform;
        $out = $transform($blob);
        if (!$out && $out !== false ) $out = $blob;
        return $out;
    }
}
?>