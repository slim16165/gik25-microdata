<?php
if(!defined('ABSPATH')) {
    exit;
}
class Boxinformativo {
    
    public function __construct() {
        add_shortcode(PLUGIN_NAME_PREFIX . 'boxinfo', array($this, 'shortcode'));
        add_shortcode('boxinfo', array($this, 'shortcode'));
        add_shortcode('boxinformativo', array($this, 'shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'boxinformativo_styles'));
        add_filter('mce_external_plugins', array($this, 'boxinformativo_add_buttons'));
        add_filter('mce_buttons', array($this, 'boxinformativo_register_buttons'));
    }

    public function shortcode($atts, $content = null) {
        $options = shortcode_atts(array(
            'title' => 'Curiosità', // (Optional)
        ), $atts);
    
        // Create Header
        if (isset($options['title'])):
            $citeHeader = "<header>{$options['title']}</header>";
        else:
            $citeHeader = null;
        endif;
    
        $result = "<div class=\"specialnote\">
            <header>$citeHeader</header>
                <p>". do_shortcode($content) ."</p> 
            </div>";
        return $result;
    }

    public function boxinformativo_styles(){
        // wp_register_style('boxinformativo-styles', plugins_url('/gik25-microdata/assets/css/gik25-quotes.css'), array(), '1.7.5', 'all');
        // wp_enqueue_style('boxinformativo-styles');
        wp_register_style('gik25-quotes-styles', plugins_url('/gik25-microdata/assets/css/gik25-quotes.css'), array(), '1.7.5', 'all');
        wp_enqueue_style('gik25-quotes-styles');
    }

    public function boxinformativo_add_buttons($plugin_array) {
        $plugin_array['Revious_boxinfo'] = plugins_url('/gik25-microdata/assets/js/Revious_boxinfo.js');
        return $plugin_array;
    }
    
    public function boxinformativo_register_buttons($buttons) {
        array_push($buttons, 'boxinfo-menu');
        return $buttons;
    }

}

$boxinformativo = new Boxinformativo();