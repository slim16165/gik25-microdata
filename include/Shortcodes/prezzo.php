<?php
if(!defined('ABSPATH')) {
    exit;
}

class Prezzo {

    public function __construct() {
        add_shortcode(PLUGIN_NAME_PREFIX . 'prezzo', array($this, 'shortcode'));
        add_filter('mce_external_plugins', array($this, 'mdp_register_plugin'));
        add_filter('mce_buttons', array($this, 'mdp_register_button'));
    }

    public function shortcode($atts, $content = null) {
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

    public function mdp_register_plugin($plugin_array) {
        $plugins_url = plugins_url('gik25-microdata/assets/js/prezzo.js');
        $plugin_array['md_prezzo'] = $plugins_url;
        return $plugin_array;
    }

    public function mdp_register_button($buttons) {
        array_push($buttons, 'md_prezzo-menu');
        return $buttons;
    }

}

$prezzo = new Prezzo();