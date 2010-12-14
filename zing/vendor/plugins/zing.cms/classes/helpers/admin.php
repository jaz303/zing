<?php
namespace zing\cms\helpers\admin;

class BaseHelper
{
    public static function icon_path($icon, $set = 'fugue') {
        return "/images/zing.cms/icons/$set/$icon.png";
    }
    
    public static function icon($icon, $set = null, $title = '') {
        if ($set === null) $set = 'fugue';
        return "<img title='" . htmlspecialchars($title) . "' src='" . self::icon_path($icon, $set) . "' class='icon' />";
    }
    
    public static function hr() {
        return "<div class='hr'><hr/></div>";
    }
    
    private static $BOOLEAN_ICONS = array(
        'flag'      => array('flag_red',    'flag_green'),
        'tick'      => array('cross',       'tick'),
        'thumb'     => array('thumb_down',  'thumb_up')
    );
    
    public static function boolean_icon($val, $set = 'tick') {
        return self::icon(self::$BOOLEAN_ICONS[$set][$val ? 1 : 0]);
    }
    
    public static function date_select($name, $value = null) {
        
        if ($value === null) $value = new \Date;
        $iso = $value->iso_date();
        
        $name = self::prefix_field_name($name, '@');
        $html  = "<span class='date-picker'>\n";
        $html .= "  <input type='text' value='' readonly='readonly' />\n";
        $html .= "  <input type='hidden' name='$name' value='$iso' />\n";
        $html .= "  <a href='#'>" . self::icon('calendar') . "</a>\n";
        $html .= "</span>\n";
        
        return $html;
        
    }
    
    public static function date_time_select($name, $value = null) {
        
        if ($value === null) $value = new \Date_Time;
        $iso = $value->iso_date_time();
        
        $name = self::prefix_field_name($name, '@');
        $html  = "<span class='datetime-picker'>\n";
        $html .= "  <input type='text' value='' readonly='readonly' />\n";
        $html .= "  <input type='hidden' name='$name' value='$iso' />\n";
        $html .= "  <a href='#'>" . self::icon('calendar') . "</a>\n";
        $html .= "</span>\n";
        
        return $html;
        
    }
    
    public static function rich_text_area($name, $value = '', $options = array()) {
        $options += array('sets' => array());
        $class = 'tinymce';
        foreach ($options['sets'] as $option_set) $class .= " tinymce-options-" . $option_set;
        $value = htmlentities($value);
        return "<textarea name='$name' class='$class'>$value</textarea>";
    }
    
    private static function prefix_field_name($name, $prefix) {
        if ($prefix) {
            // foo => {$prefix}foo
            // foo[bar] => foo[{$prefix}bar]
        }
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

class URLHelper
{
    public static function admin_url($url) {
        if ($url[0] == ':') {
            return "/admin/" . substr($url, 1);
        } else {
            return $url;
        }
    }
  
    public static function admin_base_url() { return '/admin'; }
    public static function admin_dashboard_url() { return '/admin'; }
    public static function admin_login_url() { return '/admin/sessions/login'; }
    public static function admin_logout_url() { return '/admin/sessions/logout'; }
}

class AdminFormGroup
{
    public $title   = "";
    public $items   = array();
    
    public function __construct($title = "") {
        $this->title = $title;
    }
}

class AdminFormInput
{
    public $name;                           // Name of this input
    public $method;                         // Helper method that generated the HTML
    public $html;                           // HTML for the input contents
    public $required        = false;        // Indicates that this input is required
    public $description     = null;         // A long description of the form input
    public $note            = null;         // A brief note about the form input
    public $label           = null;         // Label indicating name of form input
    public $errors          = array();      // Array of errors
    public $display_hint    = null;         // Arbitrary data form builder can use to customise display
    
    public function __construct($name, $method, $html) {
        $this->name     = $name;
        $this->method   = $method;
        $this->html     = $html;
    }
    
    public function required($r = true) { $this->required = $r; return $this; }
    public function description($d) { $this->description = $d; return $this; }
    public function note($n) { $this->note = $n; return $this; }
    public function label($l) { $this->label = $l; return $this; }
    public function errors($e) { $this->errors = (array) $e; return $this; }
    public function display_hint($dh) { $this->display_hint = $dh; return $this; }
}

abstract class FormBuilder
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
        'text_area'                 => '\\zing\\helpers\\FormHelper::text_area',
        'check_box'                 => '\\zing\\helpers\\FormHelper::check_box',
        'rich_text_area'            => '\\zing\\cms\\helpers\\AdminHelper::rich_text_area',
        'date_select'               => '\\zing\\cms\\helpers\\AdminHelper::date_select',
        'date_time_select'          => '\\zing\\cms\\helpers\\AdminHelper::date_time_select'
    );
    
    protected $action;      // Form action
    protected $method;      // From method
    protected $attribs;     // Extra HTML attributes
    
    protected $prefix       = null;
    protected $errors       = null;
    
    protected $context      = null;
    protected $context_fn   = null;
    
    protected $items        = array();
    protected $group        = null;
    
    protected $submit_text  = "Submit";
    protected $cancel_url   = null;
    protected $cancel_text  = "cancel";
    
    protected $html         = "";
    
    public function __construct($action = '', $method = 'post', $attribs = array()) {
        $this->action   = $action;
        $this->method   = $method;
        $this->attribs  = $attribs;
    }
    
    public function set_prefix($prefix) { $this->prefix = $prefix; }
    public function set_errors($errors) { $this->errors = $errors; }
    
    /**
     * Set the data context for this form.
     * If set, the form builder will use the context's data to automatically populate
     * form fields wherever possible.
     *
     * @param $context context object (can be anything, array, object)
     * @param $fn a function, taking two parameters (context, field name), that will return
     *        the data associated with a given field in the context object. String shortcuts
     *        exist: 'get' indicates the context is an object and fields should be accessed
     *        by calling "get_{field_name}", and 'array' indicates that the context is an
     *        array and that fields should be accessed by the array lookup operator.
     */
    public function set_context($context, $fn = null) {
        $this->context = $context;
        if ($fn === null) $fn = is_object($context) ? 'get' : 'array';
        if ($fn == 'get') {
            $this->context_fn = function($context, $field) {
                $method = "get_$field";
                return method_exists($context, $method) ? $context->$method() : null;
            };
        } elseif ($fn == 'array') {
            $this->context_fn = function($context, $field) {
                return $context[$field];
            };
        } else {
            $this->context_fn = $fn;
        }
    }
    
    public function submit_text($st) { $this->submit_text = $st; return $this; }
    public function cancel_url($url) { $this->cancel_url = $url; return $this; }
    public function cancel_text($ct) { $this->cancel_text = $ct; return $this; }
    
    public function start_group($title = "") {
        $group = new AdminFormGroup($title);
        $this->items[] = $group;
        $this->group = $group;
        return $this;
    }
    
    public function end_group() {
        $this->group = null;
        return this;
    }
    
    public function __call($method, $args) {
        if (isset(self::$FIELD_HELPERS[$method])) {
            
            $raw_name = array_shift($args);
            
            if ($this->context === null) {
                $value = array_shift($args);
            } else {
                $value = $this->context_fn->__invoke($this->context, $raw_name);
            }
            
            if ($this->prefix) {
                $name = "{$this->prefix}[{$raw_name}]";
            } else {
                $name = $raw_name;
            }
            
            array_unshift($args, $value);
            array_unshift($args, $name);
            
            $method = self::$FIELD_HELPERS[$method];
            $html = call_user_func_array($method, $args);
            
            $input = new AdminFormInput($name, $method, $html);
            
            if ($this->group) {
                $this->group->items[] = $input;
            } else {
                $this->items[] = $input;
            }
            
            return $input;
        
        } else {
            throw new \NoSuchMethodException("no such method: $method");
        }
    }
    
    public function to_html() {
        $attribs = $this->attribs;
        $attribs['method'] = $this->method;
        
        if ($class = $this->get_form_class()) {
            if (!isset($attribs['class'])) {
                $attribs['class'] = $class;
            } else {
                $attribs['class'] .= ' ' . $class;
            }
        }
        
        $this->begin_walk();
        $this->html  = \zing\helpers\FormHelper::start_form($this->action, $attribs);
        $this->in_form();
        $this->walk_nodes($this->items);
        $this->out_form();
        $this->html .= \zing\helpers\FormHelper::end_form();
        $this->end_walk();
        
        return $this->html;
    }
    
    public function displays_errors_inline() { return false; }
    protected function get_form_class() { return ''; }
    
    protected function begin_walk() { }
    protected function end_walk() { }
    
    protected function walk_nodes($nodes) {
        foreach ($nodes as $node) {
            $this->walk_node($node);
        }
    }
    
    protected function walk_node($node) {
        if ($node instanceof AdminFormInput) {
            $this->walk_input($node);
        } elseif ($node instanceof AdminFormGroup) {
            $this->walk_group($node);
        }
    }
    
    protected function in_form() {}
    protected function out_form() {}
    
    protected function walk_group($group) {
        $this->in_group($group);
        $this->walk_nodes($group->items);
        $this->out_group($group);
    }
    
    protected function in_group($group) {}
    protected function out_group($group) {}
    
    protected function walk_input($input) {
        $this->in_input($input);
        $this->out_input($input);
    }
    
    protected function in_input($item) {}
    protected function out_input($item) {}
}

class StandardFormBuilder extends FormBuilder
{
    /**
     * List the names of helpers whose content should automatically
     * be wrapped in <div class='iw'></div>.
     * Plugins can augment this array to wrap additional helpers.
     */
    public static $WRAPPED_HELPERS = array(
        '\\zing\\helpers\\FormHelper::text_field'       => true,
        '\\zing\\helpers\\FormHelper::password_field'   => true,
        '\\zing\\helpers\\FormHelper::select'           => true,
        '\\zing\\helpers\\FormHelper::country_select'   => true,
        '\\zing\\helpers\\FormHelper::text_area'        => true
    );
    
    public function displays_errors_inline() { return true; }
    protected function get_form_class() { return 'standard-form'; }
    
    protected function begin_walk() {
        if (count($this->items)) {
            if (!($this->items[0] instanceof AdminFormGroup)) {
                $group = new AdminFormGroup;
                $group->items = $this->items;
                $this->items = array($group);
            }
        }
    }
    
    protected function in_group($group) {
        $this->html .= "<fieldset>\n";
        if ($group->title) {
            $this->html .= "<legend>" . htmlspecialchars($group->title) . "</legend>\n";
        }
    }
    
    protected function out_group($group) {
        $html->html .= "<div class='c'></div>\n";
        $this->html .= "</fieldset>\n";
    }
    
    protected function in_input($input) {
        
        $class  = 'form-item ';
        
        $dh = $input->display_hint;
        if (is_string($dh)) $dh = array('size' => $dh);
        if (!$dh) $dh = array();
        $dh += array('size' => 'full', 'new_row' => false);
        
        $class .= " {$dh['size']}";
        if ($dh['new_row']) $class .= ' new-row';
        if ($input->errors) $class .= ' with-errors';
        
        $this->html .= "<div class='$class'>\n";
        
        if ($input->label) {
            $label = htmlspecialchars($input->label);
            if ($input->required) {
                $this->html .= "<label class='required'>* {$label}:</label>\n";
            } else {
                $this->html .= "<label>{$label}:</label>\n";
            }
        }
        
        if ($input->description) {
            $this->html .= "<p class='description'>{$input->description}</p>";
        }
        
        if (isset(self::$WRAPPED_HELPERS[$input->method])) {
            $this->html .= "<div class='iw'>{$input->html}</div>";
        } else {
            $this->html .= $input->html;
        }
        
        if ($input->errors) {
            $this->html .= "<p class='errors'>" . implode(', ', $input->errors) . "</p>";
        } elseif ($input->note) {
            $this->html .= "<p class='note'>{$input->note}</p>";
        }
        
        $this->html .= "</div>\n";
        
    }
    
    protected function out_form() {
        $this->html .= "<div class='actions'>\n";
        $this->html .= "<button>{$this->submit_text}</button>\n";
        if ($this->cancel_url) {
            $this->html .= " or <a href='{$this->cancel_url}'>{$this->cancel_text}</a>";
        }
        $this->html .= "</div>\n";
    }
}

class AdminTabularFormBuilder extends StandardFormBuilder
{
    // public function to_html() {
    //     $html  = $this->start_form_tag();
    //     foreach ($this->items as $item) {
    //         if ($item instanceof AdminFormInput) {
    //             $class = 'item';
    //             if ($item->required) $class .= ' required';
    //             if ($item->errors) $class .= ' with-error';
    //             $html .= "<tr class='$class'>\n";
    //             $html .= "<td><label>" . htmlentities($item->label) . "</label></td>\n";
    //             $html .= "<td>" . $item->html;
    //             if ($item->note) {
    //                 $html .= "<p class='note'>{$item->note}</p>";
    //             }
    //             $html .= "</td>\n";
    //             
    //             $html .= "</tr>\n";
    //         } elseif ($item instanceof AdminFormHeading) {
    //             
    //         }
    //     }
    //     $html .= $this->end_form_tag();
    //     return $html;
    // }
}
?>