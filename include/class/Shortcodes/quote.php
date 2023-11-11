<?php
namespace gik25microdata\Shortcodes;
if(!defined('ABSPATH')) {
    exit;
}

class Quote extends ShortcodeBase
{

    public function __construct()
    {
        add_shortcode('md_quote', array($this, 'shortcode'));
        add_shortcode('quote', array($this, 'shortcode'));
        $this->shortcode = 'md_flipbox';
        parent::__construct();
    }

    public function ShortcodeHandler($atts, $content = null)
    {
        $result = "<blockquote>$content</blockquote>";
        return $result;
    }

    public function styles()
    {
        // TODO: Implement styles() method.
    }

    public function admin_scripts()
    {
        // TODO: Implement admin_scripts() method.
    }

    public function register_plugin($plugin_array)
    {
        // TODO: Implement register_plugin() method.
    }

    public function register_button($buttons)
    {
        // TODO: Implement register_button() method.
    }
}

//TODO: manca il pulsante per TinyMCE
$quote = new Quote();