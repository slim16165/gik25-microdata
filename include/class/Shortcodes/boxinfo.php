<?php
namespace gik25microdata\Shortcodes;

if (!defined('ABSPATH'))
{
    exit;
}

class Boxinfo extends ShortcodeBase
{
    public function __construct()
    {
        // Imposta shortcode principale PRIMA di chiamare parent::__construct()
        $this->shortcode = 'md_boxinfo';
        parent::__construct();
        
        // Registra alias dello shortcode (dopo parent::__construct() per evitare conflitti)
        add_shortcode('boxinfo',        array($this, 'ShortcodeHandler'));
        add_shortcode('boxinformativo', array($this, 'ShortcodeHandler'));

        if (is_admin())
        {
            //Backend only
            add_filter('mce_external_plugins', array($this, 'add_buttons'));
            add_filter('mce_buttons', array($this, 'register_buttons'));
        }
    }

    public function pluginOptimizedLoad(): void
    {
        // Rimosso check conditional tags - verificano global $post invece
        if ($this->PostContainsShortCode('boxinfo'))
        {
            add_action('wp_enqueue_scripts', array($this, 'styles'));
        }
    }

    public function ShortcodeHandler($atts, $content = null)
    {
        $options = shortcode_atts(array(
            'title' => 'Curiosità', // (Optional)
        ), $atts);

        // Create Header
        if (isset($options['title'])):
            $citeHeader = "<header>{$options['title']}</header>";
        else:
            $citeHeader = null;
        endif;

        $result = "<div class=\"specialnote\">
            <header>$citeHeader</header>
                <p>" . do_shortcode($content) . "</p> 
            </div>";
        return $result;
    }

    public function styles()
    {
        // wp_register_style('styles', plugins_url("{$this->asset_path}/css/gik25-quotes.css"), array(), '1.7.5', 'all');
        // wp_enqueue_style('styles');
        wp_register_style('styles', plugins_url("{$this->asset_path}/css/gik25-quotes.css"), array(), '1.7.5', 'all');
        wp_enqueue_style('styles');
    }

    public function add_buttons($plugin_array)
    {
        // Assicurati che $plugin_array sia un array (potrebbe essere null in alcuni contesti)
        if (!is_array($plugin_array)) {
            $plugin_array = array();
        }
        $plugin_array['Revious_boxinfo'] = plugins_url("{$this->asset_path}/js/TinyMCE/boxinfo.js");
        return $plugin_array;
    }

    public function register_buttons($buttons)
    {
        // Assicurati che $buttons sia un array (potrebbe essere null in alcuni contesti)
        if (!is_array($buttons)) {
            $buttons = array();
        }
        array_push($buttons, 'boxinfo-menu');
        return $buttons;
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

$boxinformativo = new Boxinfo();