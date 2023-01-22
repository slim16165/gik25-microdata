<?php
namespace include\class\Shortcodes;
if(!defined('ABSPATH')) {
    exit;
}

class Prezzo extends ShortcodeBase
{
    public function __construct()
    {
        $this->shortcode = "md_prezzo";
        parent::__construct();
    }

    public function ShortcodeHandler($atts, $content = null) {
            //Fine offer span
        return <<<EOF
        <span itemprop="offers" itemscope itemtype="http://schema.org/Offer">
            <span itemprop="priceCurrency" content="EUR">€</span>
            <span itemprop="price">
EOF
                .do_shortcode($content)
            ."</span>" //Fine price span
            ."</span>";
    }

    public function register_plugin($plugin_array) {
        $plugins_url = plugins_url('gik25-microdata/assets/js/TinyMCE/prezzo.js');
        $plugin_array['md_prezzo'] = $plugins_url;
        return $plugin_array;
    }

    public function register_button($buttons) {
        array_push($buttons, 'md_prezzo-menu');
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

$prezzo = new Prezzo();