<?php
namespace include\class\Shortcodes;

if (!defined('ABSPATH'))
{
    exit;
}

class Youtube extends ShortcodeBase
{

    public function __construct()
    {
        $this->shortcode = ['md_youtube', 'youtube'];
        parent::__construct();
    }

    public function ShortcodeHandler($atts, $content = null)
    {
        if (isset($atts["url"]))
        {
            $result = wp_oembed_get($atts["url"]);
            return $result;
        }
    }

    public function register_plugin($plugin_array)
    {
        $plugin_array['md_youtube'] = plugins_url("{$this->asset_path}/js/TinyMCE/youtube.js");
        return $plugin_array;
    }

    public function register_button($buttons)
    {
        array_push($buttons, 'md_youtube-menu');
        return $buttons;
    }

    public function styles()
    {
        // TODO: Implement styles() method.
    }

    public function admin_scripts()
    {
        // TODO: Implement admin_scripts() method.
    }
}

$youtube = new Youtube();