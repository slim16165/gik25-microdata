<?php
namespace gik25microdata\Shortcodes;
if(!defined('ABSPATH')) {
    exit;
}
class Progressbar extends ShortcodeBase
{
    public function __construct()
    {
        $this->shortcode = 'md_progressbar';
        parent::__construct();
        
        // Registra alias dello shortcode
        add_shortcode('progressbar', array($this, 'ShortcodeHandler'));
    }

    function styles()
    {
        wp_register_style('styles', plugins_url('gik25-microdata/assets/css/md_progressbar.css'), array(), '', 'all');
        wp_enqueue_style('styles');
        wp_register_script('progressbar_js', plugins_url('gik25-microdata/assets/js/progressbar.js'), array('jquery'), '1.0', true);
        wp_enqueue_script('progressbar_js');
    }

    public function ShortcodeHandler($atts, $content = null)
    {
        // Lo shortcode progressbar non genera HTML direttamente
        // Lo script JavaScript (progressbar.js) gestisce l'inserimento della progress bar nel DOM
        // Questo shortcode serve principalmente per caricare CSS/JS quando presente nel post
        // Restituisce un placeholder invisibile per permettere il rilevamento dello shortcode
        return '<!-- Progress bar loaded via JavaScript -->';
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