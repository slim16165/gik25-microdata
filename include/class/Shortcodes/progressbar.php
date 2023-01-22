<?php
namespace include\class\Shortcodes;
if(!defined('ABSPATH')) {
    exit;
}

class Progressbar extends ShortcodeBase
{

    public function __construct()
    {
        //add_shortcode('md_progressbar', array($this, 'shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'md_progressbar_scripts_styles'));
    }

    function md_progressbar_scripts_styles()
    {
        wp_register_style('md_progressbar-styles', plugins_url('/gik25-microdata/assets/css/md_progressbar.css'), array(), '', 'all');
        wp_enqueue_style('md_progressbar-styles');
        wp_register_script('md_progressbar-script', plugins_url('/gik25-microdata/assets/js/progressbar.js'), array('jquery'));
        wp_enqueue_script('md_progressbar-script');
    }

}

$microdata_progressbar = new Progressbar();