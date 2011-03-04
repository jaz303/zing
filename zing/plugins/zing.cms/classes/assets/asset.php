<?php
namespace zing\cms\assets;

class Asset extends \zing\cms\Model
{
    //
    //
    
    public static function table_name() { return 'asset'; }
    
    public static function path_for_asset_file($id) {
        if ($id instanceof Asset) $id = $id->get_id();
        return $GLOBALS['_ZING']['zing.cms.asset_path'] . '/files/' . $id;
    }
    
    public static function path_for_asset_thumb($id) {
        if ($id instanceof Asset) $id = $id->get_id();
        return $GLOBALS['_ZING']['zing.cms.asset_path'] . '/thumbs/' . $id;
    }
    
    //
    // Attributes
    
    private $folder_id                  = null;
    private $title                      = "";
    private $description                = "";
    private $alt_text                   = "";
    
    private $width                      = null;
    private $height                     = null;
    private $duration                   = null;
    private $fps                        = null;
    private $filename                   = "";
    private $extension                  = "";
    private $filesize                   = 0;
    private $mime_type                  = "";
    
    private $has_thumb                  = false;
    private $thumb_width                = null;
    private $thumb_height               = null;
    private $thumb_filename             = "";
    private $thumb_extension            = "";
    private $thumb_filesize             = 0;
    private $thumb_mime_type            = "";
    
    //
    // Getters
    
    public function get_folder_id() { return $this->folder_id; }
    public function get_title() { return $this->title; }
    public function get_description() { return $this->description; }
    public function get_alt_text() { return $this->alt_text; }
    
    public function get_width() { return $this->width; }
    public function get_height() { return $this->height; }
    public function get_duration() { return $this->duration; }
    public function get_fps() { return $this->fps; }
    public function get_filename() { return $this->filename; }
    public function get_extension() { return $this->extension; }
    public function get_filesize() { return $this->filesize; }
    public function get_mime_type() { return $this->mime_type; }
    
    public function has_thumb() { return $this->has_thumb; }
    public function get_thumb_width() { return $this->thumb_width; }
    public function get_thumb_height() { return $this->thumb_height; }
    public function get_thumb_filename() { return $this->thumb_filename; }
    public function get_thumb_extension() { return $this->thumb_extension; }
    public function get_thumb_filesize() { return $this->thumb_filesize; }
    public function get_thumb_mime_type() { return $this->thumb_mime_type; }
    
    //
    // Queries
    
    public function is_image() { return $this->is_mime_type('image/'); }
    public function is_web_safe_image() { }
    public function is_pdf() { return $this->is_mime_type('application/pdf'); }
    public function is_web_safe_video() { }
    public function is_flash_video() { }
    
    public function is_mime_type() { }
    public function is_extension() { }
    
    //
    // Setters
    
    public function set_folder_id($v) { $this->folder_id = int_or_null($v); }
    public function set_title($v) { $this->title = trim($v); }
    public function set_description($v) { $this->description = $v; }
    public function set_alt_text($v) { $this->alt_text = trim($v); }
    
    protected function set_width($v) { $this->width = int_or_null($v); }
    protected function set_height($v) { $this->height = int_or_null($v); }
    protected function set_duration($v) { $this->duration = int_or_null($v); }
    protected function set_fps($v) { $this->fps = float_or_null($v); }
    public function set_filename($v) { $this->filename = $v; $this->set_extension(\AbstractFile::extension_for($this->filename)) }
    protected function set_extension($v) { $this->extension = $v; }
    protected function set_filesize($v) { $this->filesize = $v; }
    protected function set_mime_type($v) { $this->mime_type = $v; }
    
    protected function set_has_thumb($v) { $this->has_thumb = (bool) $v; }
    protected function set_thumb_width($v) { $this->thumb_width = int_or_null($v); }
    protected function set_thumb_height($v) { $this->thumb_height = int_or_null($v); }
    public function set_thumb_filename($v) { $this->thumb_filename = $v; $this->set_thumb_extension(\AbstractFile::extension_for($this->thumb_filename)) }
    protected function set_thumb_extension($v) { $this->thumb_extension = $v; }
    protected function set_thumb_filesize($v) { $this->thumb_filesize = $v; }
    protected function set_thumb_mime_type($v) { $this->thumb_mime_type = $v; }
    
    //
    // Paths
    
    public function get_file_path() { return self::path_for_asset_file($this); }
    public function get_thumb_path() { return self::path_for_asset_thumb($this); }
    
    //
    // File handling
    
    private $file = null;
    private $thumb = null;
    
    public function get_file() { return $this->file; }
    public function set_file(\AbstractFile $f) { $this->file = $f; }
    
    public function get_thumb() { return $this->thumb; }
    public function set_thumb(\AbstractFile $f) { $this->thumb = $f; }
    
    protected function pre_commit_files() {
        if ($this->file) {
            if ($this->file->is_supported_image()) {
                $image = $this->file->to_image();
                $this->set_width($image->get_width());
                $this->set_height($image->get_height());
            }
            $this->set_duration(null);
            $this->set_fps(null);
            $this->set_filename($this->file->basename());
            $this->set_filesize($this->file->size());
            $this->set_mime_type($this->file->content_type());
        }
        if ($this->thumb) {
            $this->set_has_thumb(true);
            $this->set_thumb_filename($this->thumb->basename());
            $this->set_thumb_filesize($this->thumb->size());
            $this->set_thumb_mime_type($this->thumb->mime_type());
            if ($this->thumb->is_supported_image()) { // kinda redundant
                $image = $this->thumb->to_image();
                $this->set_thumb_width($image->get_width());
                $this->set_thumb_height($image->get_height());
            }
        }
    }
    
    protected function post_commit_files() {
        if ($this->file) {
            $this->file->move($this->get_file_path());
            $this->file = null;
        }
        if ($this->thumb) {
            $this->thumb->move($this->get_thumb_path());
            $this->thumb = null;
        }
    }
    
    protected function delete_files() {
        unlink($this->get_file_path());
        if ($this->has_thumb) {
            unlink($this->get_thumb_path());
        }
    }
    
    //
    // Persistence
    
    protected function before_save() { $this->pre_commit_files(); }
    protected function after_save() { $this->post_commit_files(); }
    protected function after_delete() { $this->delete_files(); }
    
    protected function type_hinted_attributes() {
        return array(
            'i:folder_id'               => $this->folder_id,
            's:title'                   => $this->title,
            's:description'             => $this->description,
            's:alt_text'                => $this->alt_text,
            
            'i:width'                   => $this->width,
            'i:height'                  => $this->height,
            'i:duration'                => $this->duration,
            'f:fps'                     => $this->fps,
            's:filename'                => $this->filename,
            's:extension'               => $this->extension,
            'i:filesize'                => $this->filesize,
            's:mime_type'               => $this->mime_type,
            
            'b:has_thumb'               => $this->has_thumb,
            'i:thumb_width'             => $this->thumb_width,
            'i:thumb_height'            => $this->thumb_height,
            's:thumb_filename'          => $this->thumb_filename,
            's:thumb_extension'         => $this->thumb_extension,
            'i:thumb_filesize'          => $this->thumb_filesize,
            's:thumb_mime_type'         => $this->thumb_mime_type
        );
    }
    
    protected function do_validation() {
        if (is_blank($this->title)) {
            $this->errors->add('title', 'cannot be blank');
        }
        
        if ($this->file === null) {
            if ($this->is_new_record()) {
                $this->errors->add('file', 'cannot be blank');
            }
        } else {
            if (!$this->file->is_readable()) {
                $this->errors->add('file', $this->error_message_for_file($this->file));
            }
        }
        
        if ($this->thumb !== null) {
            if (!$this->thumb->is_readable()) {
                $this->errors->add('thumb', $this->error_message_for_file($this->thumb));
            } else if (!$this->thumb->is_supported_image()) {
                $this->errors->add('thumb', 'is not a supported image format');
            }
        }
        
    }
    
    protected function error_message_for_file($file) {
        if ($file->is_upload()) {
            if (!$file->was_upload_attempted()) {
                return 'cannot be blank';
            } elseif ($file->is_max_size_exceeded()) {
                return 'exceeded the maximum allowed size';
            } elseif ($file->is_partial_upload()) {
                return 'did not upload completely';
            } elseif ($file->is_internal_error()) {
                return '^An internal error occurred';
            } else {
                return '^An unknown error occurred'
            }
        } else {
            return 'is not readable';
        }
    }
}
?>