<?php
if(!defined('ABSPATH')) {
    exit;
}

class Prezzo {

    public function __construct() {
        add_shortcode(PLUGIN_NAME_PREFIX . 'prezzo', array($this, 'shortcode'));
        add_filter('mce_external_plugins', array($this, 'revious_microdata_add_tinymce_plugins'));
        add_filter('mce_buttons', array($this, 'revious_microdata_register_prezzo_buttons'));
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

    public function revious_microdata_add_tinymce_plugins($plugin_array) {
        $plugins_url = plugins_url('gik25-microdata/assets/js/revious-microdata.js', 'revious-microdata');
        $plugin_array['revious_microdata'] = $plugins_url;
        return $plugin_array;
    }

    public function revious_microdata_register_prezzo_buttons($buttons) {
        array_push($buttons, 'md_prezzo_btn');
        return $buttons;
    }

}

$prezzo = new Prezzo();