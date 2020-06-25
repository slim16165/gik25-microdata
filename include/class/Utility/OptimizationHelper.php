<?php

class OptimizationHelper
{
    public static function ConditionalLoadJsCss_Colori()
    {
        add_action('wp_head', array(__CLASS__, '_conditionalLoadJsCss_Colori'));
    }

    static function _conditionalLoadJsCss_Colori()
    {
        global $post;
        $postConTagColori = TagHelper::find_post_id_from_taxonomy("colori", 'post_tag');//args: term_name "colori", taxonomy_type 'post_tag'
        if (in_array($post->ID, $postConTagColori))
            ColorWidget::carousel_js();
    }

    public static function ConditionalLoadCssOnPosts()
    {
        add_action('wp_enqueue_scripts', array(__CLASS__, 'load_css_or_js_specific_pages'), 1001);
    }

    public static function ConditionalLoadCssJsOnPostsWhichContainEnabledShortcodes() //configurable in plugin settings.
    {
        add_action('wp_enqueue_scripts', array(__CLASS__, 'load_css_js_on_posts_which_contain_enabled_shortcodes'), 1001);
    }

    public static function load_css_js_on_posts_which_contain_enabled_shortcodes()
    {
        if (is_single())
        {
            $enabled_shortcode_found = false;

            $shortcode_names_arr = get_option('revious_microdata_option_name');
            $shortcode_names = $shortcode_names_arr['shortcode_names'];

            if(!empty($shortcode_names)) {
                $shortcode_names_arr_2 = explode(',', $shortcode_names);
            }

            global $post;
            foreach($shortcode_names_arr_2 as $shortcode_name) {
                if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, $shortcode_name) ) {
                    $enabled_shortcode_found = true;
                }
            }

            if($enabled_shortcode_found) {
                //enqueue css, js
                wp_enqueue_style('css_for_enabled_shortcodes',  plugins_url() . '/gik25-microdata/assets/css/css-for-enabled-shortcodes.css');
                wp_enqueue_script('css_for_enabled_shortcodes', plugins_url() . '/gik25-microdata/assets/js/js-for-enabled-shortcodes.js');
            }
        }
        
    }

    public static function load_css_or_js_specific_pages()
    {
        if (is_single())
        {
            $plugin_url = plugin_dir_url(__FILE__);
            wp_enqueue_style('css_single_pages', trailingslashit($plugin_url) . 'assets/css/revious-microdata.css', array());

            // Register the style like this for a plugin:
            //wp_register_style('revious-quotes-styles', plugins_url('/revious_microdata.css', __FILE__), array(), '1.7.5', 'all');
            // For either a plugin or a theme, you can then enqueue the style:
            //wp_enqueue_style('revious-quotes-styles');
        }
        //else if(is_category() || is_tag())
    }
}