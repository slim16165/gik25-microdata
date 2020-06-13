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
        $postConTagColori = TagHelper::find_post_id_from_taxonomy("colori", 'post_tag');
        if (in_array($post->ID, $postConTagColori))
            ColorWidget::carousel_js();
    }

    public static function ConditionalLoadCssOnPosts()
    {
        add_action('wp_enqueue_scripts', array(__CLASS__, 'load_css_or_js_specific_pages'), 1001);
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