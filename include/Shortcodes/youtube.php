<?php
if (!defined('ABSPATH'))
{
    exit;
}

class Youtube extends ShortcodeBase
{

    public function __construct()
    {
        add_shortcode('md_youtube', array($this, 'shortcode'));
        add_shortcode('youtube', array($this, 'shortcode'));

        if (is_admin())
        {
            add_filter('mce_external_plugins', array($this, 'mdy_register_plugin'));
            add_filter('mce_buttons', array($this, 'mdy_register_button'));
        }
    }

    public function shortcode($atts, $content = null)
    {
        if (isset($atts["url"]))
        {
            $result = wp_oembed_get($atts["url"]);
            return $result;
        }
    }

    public function mdy_register_plugin($plugin_array)
    {
        $plugin_array['md_youtube'] = plugins_url('/gik25-microdata/assets/js/TinyMCE/youtube.js');
        return $plugin_array;
    }

    public function mdy_register_button($buttons)
    {
        array_push($buttons, 'md_youtube-menu');
        return $buttons;
    }

}

$youtube = new Youtube();