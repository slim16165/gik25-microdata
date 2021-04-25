<?php

class OptimizationHelper
{
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
            $enabled_shortcode_found = self::HandleConfiguredShortcodes();

            if($enabled_shortcode_found)
            {
                //TODO: check if the conditional load is working
                //enqueue css, js
                wp_enqueue_style('css_for_enabled_shortcodes',  plugins_url() . '/gik25-microdata/assets/css/css-for-enabled-shortcodes.css');
                wp_enqueue_script('css_for_enabled_shortcodes', plugins_url() . '/gik25-microdata/assets/js/js-for-enabled-shortcodes.js');
            }
        }
    }

    //Inglobare nell'altra e poi cancellare
    public static function load_css_or_js_specific_pages()
    {
        if (is_single())
        {
            $plugin_url = plugin_dir_url(__FILE__);
            wp_enqueue_style('css_single_pages', plugins_url() . '/gik25-microdata/assets/css/revious-microdata.css');
            //If the line over is not working check if the bottom works.. I don't remember which is the fix
	        //wp_enqueue_style('css_single_pages', trailingslashit($plugin_url) . '../../../assets/css/revious-microdata.css', array());


            // Register the style like this for a plugin:
            //wp_register_style('revious-quotes-styles', plugins_url('/revious_microdata.css', __FILE__), array(), '1.7.5', 'all');
            // For either a plugin or a theme, you can then enqueue the style:
            //wp_enqueue_style('revious-quotes-styles');
        }
        //else if(is_category() || is_tag())
    }

    protected static function HandleConfiguredShortcodes()
    {
        $enabled_shortcode_found = true;

        $shortcode_names_arr = get_option('revious_microdata_option_name');

        if (!empty($shortcode_names_arr))
        {
            $shortcode_names = $shortcode_names_arr['shortcode_names'];


            if (!empty($shortcode_names))
            {
                $shortcode_names_arr_2 = explode(',', $shortcode_names);
            }

            global $post;
            if (isset($shortcode_names_arr_2))
            {
                foreach ($shortcode_names_arr_2 as $shortcode_name)
                {
                    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, $shortcode_name))
                    {
                        $enabled_shortcode_found = true;
                    }
                }
            }
        }
        return $enabled_shortcode_found;
    }
}