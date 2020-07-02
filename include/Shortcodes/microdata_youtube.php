<?php
if(!defined('ABSPATH')) {
    exit;
}

class Youtube {

    public function __construct() {
        add_shortcode(PLUGIN_NAME_PREFIX . 'youtube', array($this, 'shortcode'));
    }

    public function shortcode($atts, $content = null) {
        if(isset($atts["url"])) {
            $result = wp_oembed_get($atts["url"]);
            return $result;
        }
    }

}

$youtube = new Youtube();