<?php
class WPUsers {
    public function __construct()
    {
        add_shortcode('Users', array($this, 'shortcode'));
    }
     
    public function shortcode()
    {
        $output = '';
        //var_dump(get_users());exit;
        $users = get_users();
        foreach($users as $user) {
            $output .= '<li>' . $user->display_name . '</li>';
        }
        $output = '<ul>' . $output . '</ul>';
        return $output;
    }
}
 
$wp_users = new WPUsers();
?>