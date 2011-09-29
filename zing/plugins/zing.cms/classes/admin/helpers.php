<?php
namespace zing\cms\admin\helpers;

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
        $iso = $value->to_date()->iso_date_time();
        
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
    
    public static function collection_select_options($table, $value, $options = array()) {
        if (is_string($options)) {
            $options = array('order' => $options);
        }
        
        $options += array('key' => 'id', 'select' => '*');
        
        $sql = "SELECT {$options['select']} FROM $table";
        if (isset($options['where'])) $sql .= " WHERE {$options['where']}";
        if (isset($options['order'])) $sql .= " ORDER BY {$options['order']}";
        
        $res = \GDB::instance()->q($sql)
                               ->key($options['key']);
                   
        if (is_string($value)) {
            $res->mode('value', $value);
            return $res->stack();
        } else {
            return kmap($res, $value);
        }
    }
    
    private static function prefix_field_name($name, $prefix) {
        if ($prefix) {
            $last_bracket = strrpos($name, '[');
            if ($last_bracket === false) {
                $name = $prefix . $name;
            } else {
                $name = substr($name, 0, $last_bracket + 1) . $prefix . substr($name, $last_bracket + 1);
            }
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
    
    public static function context_menu_item($icon, $caption, $url, $selected = false) {
        $icon = "background-image:url('" . self::icon_path($icon) . "')";
        $class = $selected ? 'selected' : '';
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
        $url = URLHelper::admin_url($url);
        $class = $selected ? 'selected' : '';
        return "<li style=\"$icon\" class=\"$class\"><a href=\"$url\">$caption</a></li>";
    }
    
    //
    // Errors
    
    public static function error_messages($errors = null) {
        return \zing\helpers\HTMLHelper::error_messages($errors, ':admin/errors');
    }
}

class URLHelper
{
    //
    // Core
    
    public static function admin_url($url) {
        if ($url[0] == ':') {
            return "/admin/" . substr($url, 1);
        } else {
            return $url;
        }
    }
  
    public static function admin_base_url() { return '/admin'; }
    public static function admin_dashboard_url() { return '/admin/core/dashboard'; }
    public static function admin_login_url() { return '/admin/sessions/login'; }
    public static function admin_logout_url() { return '/admin/sessions/logout'; }
    
    public static function admin_users_url() { return '/admin/core/users'; }
    public static function admin_new_user_url() { return '/admin/core/users/create'; }
    public static function admin_edit_user_url($user) { return '/admin/core/users/edit/' . object_id($user); }
    public static function admin_delete_user_url($user) { return '/admin/core/users/delete/' . object_id($user); }
    
    public static function admin_system_info_url() { return '/admin/core/system'; }
    public static function admin_database_info_url() { return '/admin/core/database'; }
    
    //
    // CMS
    
    public static function admin_content_url() { return '/admin/cms/content'; }
    
    public static function admin_editable_action_url($type, $action = null, $id = null) {
        $url = "/admin/cms/content/$type";
        if ($action) $url .= "/$action";
        if ($id) $url .= '/' . object_id($id);
        return $url;
    }
    
    public static function admin_editable_url($type) { return self::admin_editable_action_url($type); }
    public static function admin_new_editable_url($type) { return self::admin_editable_action_url($type, 'create'); }
    public static function admin_edit_editable_url($type, $id) { return self::admin_editable_action_url($type, 'update', $id); }
    public static function admin_delete_editable_url($type, $id) { return self::admin_editable_action_url($type, 'delete', $id); }

}
?>