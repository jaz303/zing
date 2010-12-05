<?php
namespace zing\cms\helpers;

class AdminHelper
{
    public static function admin_url($url) {
        if ($url[0] == ':') {
            return "/admin/" . substr($url, 1);
        } else {
            return $url;
        }
    }
    
    public static function icon_path($icon, $set = 'fugue') {
        return "/images/zing.cms/icons/$set/$icon.png";
    }
    
    public static function icon($icon, $set = 'fugue') {
        return "<img src='" . self::icon_path($icon, $set) . "' class='icon' />";
    }
    
    public static function hr() {
        return "<div class='hr'><hr/></div>";
    }
    
    private static $BOOLEAN_ICONS = array(
        'flag'      => array('flag_red', 'flag_green'),
        'tick'      => array('cross', 'tick'),
        'thumb'     => array('thumb_down', 'thumb_up')
    );
    
    public static function boolean_icon($val, $set = 'tick') {
        return self::icon(self::$BOOLEAN_ICONS[$set][$val ? 1 : 0]);
    }
    
    public static function date_select($name, $value = null) {
        
        if ($value === null) $value = new \Date;
        $iso = $value->iso_date();
        
        $name = self::prefix_field_name($name, '@');
        $html  = "<span class='date-picker'>\n";
        $html .= "  <input type='text' value='' />\n";
        $html .= "  <input type='hidden' name='$name' value='$iso' />\n";
        $html .= "  <a href='#'>" . self::icon('calendar') . "</a>\n";
        $html .= "</span>\n";
        
        return $html;
        
    }
    
    public static function date_time_select($name, $value) {
        
        if ($value === null) $value = new \Date_Time;
        $iso = $value->iso_date_time();
        
        $name = self::prefix_field_name($name, '@');
        $html  = "<span class='datetime-picker'>\n";
        $html .= "  <input type='text' value='' />\n";
        $html .= "  <input type='hidden' name='$name' value='$iso' />\n";
        $html .= "  <a href='#'>" . self::icon('calendar') . "</a>\n";
        $html .= "</span>\n";
        
        return $html;
        
    }
    
    public static function rich_textarea($name, $value, $options = array()) {
        $value = htmlentities($value);
        return "<textarea name='$name' class='tinymce'>$value</textarea>";
    }
    
    public static function tabular_form() {
        $form = new AdminTabularForm($action, $method, $attribs);
        if ($block) $block($form);
        return $form;
    }
    
    private static function prefix_field_name($name, $prefix) {
        return $name;
    }
    
    public static function error_message($message) {
        if (strlen($message)) {
            return "<div class='flash error'>$message</div>";
        } else {
            return "";
        }
    }
    
    //
    // Context Menu
    
    public static function start_context_menu() {
        \zing\view\Base::active()->start_capture('context');
    }
    
    public static function end_context_menu() {
        \zing\view\Base::active()->end_capture();
    }
    
    public static function context_menu_item($icon, $caption, $url, $options = array()) {
        $icon = "background-image:url('" . self::icon_path($icon) . "')";
        $class = (isset($options['selected']) && $options['selected']) ? 'selected' : '';
        return "<a style=\"$icon\" class=\"$class\" href=\"$url\"><span class=\"caption\">$caption</span></a>";
    }
    
    //
    // Sidebar
    
    public static function start_sidebar_section($options = array()) {
        $title = isset($options['title']) ? $options['title'] : '';
        if (isset($options['icon'])) $title = self::icon($options['icon']) . ' ' . $title;
        if (strlen($title)) $title = "<h2>$title</h2>";
        $id = isset($options['id']) ? "id='{$options['id']}'" : '';
        $class = isset($options['class']) ? $options['class'] : '';
        
        \zing\view\Base::active()->start_capture('sidebar');
        echo "<div $id class='sidebar-section $class'>\n";
        echo "$title\n";
    }
    
    public static function end_sidebar_section() {
        echo "</div>\n";
        \zing\view\Base::active()->end_capture();
    }
    
    public static function start_sidebar_panel($options = array()) {
        self::start_sidebar_section($options);
        echo "<div class='sidebar-panel>\n";
    }
    
    public static function end_sidebar_panel() {
        echo "</div>\n";
        self::end_sidebar_section();
    }
    
    public static function start_sidebar_menu($options = array()) {
        self::start_sidebar_section($options);
        echo "<ul class='sidebar-menu'>\n";
    }
    
    public static function end_sidebar_menu() {
        echo "</ul>\n";
        self::end_sidebar_section();
    }
    
    public static function sidebar_menu_item($icon, $caption, $url, $selected = false) {
        $icon = "background-image:url('" . self::icon_path($icon) . "')";
        $class = $selected ? 'selected' : '';
        return "<li style=\"$icon\" class=\"$class\"><a href=\"$url\">$caption</a></li>";
    }
}

class AdminFormHeading
{
    public $level;
    public $heading;
    
    public function __construct($level, $heading = null) {
        if ($heading === null) {
            
        } else {
            
        }
    }
}

class AdminFormInput
{
    public $html;
    public $required        = false;
    public $description     = null;
    public $note            = null;
    public $label           = null;
    public $errors          = null;
    
    public function __construct($html) {
        $this->html = $html;
    }
    
    public function required($r = true) { $this->required = $r; return $this; }
    public function description($d) { $this->description = $d; return $this; }
    public function note($n) { $this->note = $n; return $this; }
    public function label($l) { $this->label = $l; return $this; }
    public function errors($e) { $this->errors = (array) $e; return $this; }
}

class AdminForm
{
    /**
     * Map method names on admin form classes to the static helpers that generate
     * the HTML. Plugins can augment and override this array to add input types 
     * without extending any built-in classes
     */
    public static $FIELD_HELPERS = array(
        'text_field'                => '\\zing\\helpers\\FormHelper::text_field',
        'password_field'            => '\\zing\\helpers\\FormHelper::password_field',
        'hidden_field'              => '\\zing\\helpers\\FormHelper::hidden_field',
        'select'                    => '\\zing\\helpers\\FormHelper::select',
        'country_select'            => '\\zing\\helpers\\FormHelper::country_select',
        'textarea'                  => '\\zing\\helpers\\FormHelper::textarea',
        'check_box'                 => '\\zing\\helpers\\FormHelper::check_box',
        'rich_textarea'             => '\\zing\\cms\\helpers\\AdminHelper::rich_textarea',
        'date_select'               => '\\zing\\cms\\helpers\\AdminHelper::date_select',
        'date_time_select'          => '\\zing\\cms\\helpers\\AdminHelper::date_time_select'
    );
    
    
    protected $action;
    protected $method;
    protected $attribs;
    
    protected $items    = array();
    
    public function __construct($action = '', $method = 'post', $attribs = array()) {
        $this->action   = $action;
        $this->method   = $method;
        $this->options  = $options;
    }
    
    public function heading($level, $heading = null) {
        $this->items[] = new AdminFormHeading($level, $heading);
    }
    
    public function __call($method, $args) {
        if (isset(self::$FIELD_HELPERS[$method])) {
            $html = call_user_func_array(self::$FIELD_HELPERS[$method], $args);
            $name = $args[0];
            $item = new AdminFormInput($name, $html);
            $this->items[$name] = $item;
            return $item;
        } else {
            throw new \NoSuchMethodException("no such method: $method");
        }
    }
    
    public function start_capture() {
        
    }
    
    public function end_capture() {
        
    }
    
    protected function start_form_tag() {
        return "";
    }
    
    protected function end_form_tag() {
        return "</form>";
    }
    
    public function __toString() {
        return $this->to_html();
    }
    

}

class AdminTabularForm extends AdminForm
{
    public function __construct($action, $method, $options = array()) {
        
    }
    
    public function row($label, $input) {
        
    }
    
    public function to_html() {
        $html  = $this->start_form_tag();
        foreach ($this->items as $item) {
            if ($item instanceof AdminFormInput) {
                $class = 'item';
                if ($item->required) $class .= ' required';
                if ($item->errors) $class .= ' with-error';
                $html .= "<tr class='$class'>\n";
                $html .= "<td><label>" . htmlentities($item->label) . "</label></td>\n";
                $html .= "<td>" . $item->html;
                if ($item->note) {
                    $html .= "<p class='note'>{$item->note}</p>";
                }
                $html .= "</td>\n";
                
                $html .= "</tr>\n";
            } elseif ($item instanceof AdminFormHeading) {
                
            }
        }
        $html .= $this->end_form_tag();
        return $html;
    }
}
?>