<?php
namespace include\class\Shortcodes;
if(!defined('ABSPATH')) {
    exit;
}
class Progressbar extends ShortcodeBase
{
    public function __construct()
    {
        $this->shortcode = 'md_flipbox';
        parent::__construct();
        //add_shortcode('md_progressbar', array($this, 'shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'styles'));
    }

    function styles()
    {
        wp_register_style('styles', plugins_url("{asset_path}/css/md_progressbar.css"), array(), '', 'all');
        wp_enqueue_style('styles');
        $asset_path = "gik25-microdata/asset";
        script('script', plugins_url("{$this->$asset_path}/js/progressbar.js"), array('jquery'));
        script('script');
    }

    public function ShortcodeHandler($atts, $content = null)
    {
        // TODO: Implement ShortcodeHandler() method.
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

$microdata_progressbar = new Progressbar();