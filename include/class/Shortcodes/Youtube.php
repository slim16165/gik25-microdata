<?php
namespace gik25microdata\Shortcodes;

if (!defined('ABSPATH'))
{
    exit;
}

class Youtube extends ShortcodeBase
{

    public function __construct()
    {
        $this->shortcode = 'md_youtube';
        parent::__construct();
        
        // Registra alias dello shortcode dopo parent::__construct()
        add_shortcode('youtube', array($this, 'ShortcodeHandler'));
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
        // Assicurati che $buttons sia un array (potrebbe essere null in alcuni contesti)
        if (!is_array($buttons)) {
            $buttons = array();
        }
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