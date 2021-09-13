<?php
if(!defined('ABSPATH')) {
    exit;
}
class Boxinfo {
    
    public function __construct()
    {
        //Frontend only
        add_action( 'template_redirect', array($this, 'pluginOptimizedLoad') );

        //Backend only
        add_filter('mce_external_plugins', array($this, 'boxinformativo_add_buttons'));
        add_filter('mce_buttons', array($this, 'boxinformativo_register_buttons'));
    }

    public function pluginOptimizedLoad() : void
    {
        //In alternativa potrei usare !is_admin
        $isFe = is_page() || is_singular() || is_front_page() || is_single();

        if ($isFe && $this->PostContainsShortCode('boxinfo'))
        {
            add_shortcode(PLUGIN_NAME_PREFIX . 'boxinfo', array('Boxinfo', 'shortcode'));
            add_shortcode('boxinfo', array('Boxinfo', 'shortcode'));
            add_shortcode('boxinformativo', array('Boxinfo', 'shortcode'));
            add_action('wp_enqueue_scripts', array($this, 'boxinformativo_styles'));
        }
    }

    public function PostContainsShortCode($shortcode) : bool
    {
        global $post;
        if (strpos($post->post_content, $shortcode) !== false)
            return true;
        else return false;
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
        $plugin_array['Revious_boxinfo'] = plugins_url('/gik25-microdata/assets/js/TinyMCE/boxinfo.js');
        return $plugin_array;
    }
    
    public function boxinformativo_register_buttons($buttons) {
        array_push($buttons, 'boxinfo-menu');
        return $buttons;
    }

}

$boxinformativo = new Boxinfo();