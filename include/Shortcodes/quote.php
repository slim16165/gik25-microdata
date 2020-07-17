<?php
if(!defined('ABSPATH')) {
    exit;
}

class Quote {

    public function __construct() {
        add_shortcode(PLUGIN_NAME_PREFIX . 'quote', array($this, 'shortcode'));
    }

    public function shortcode($atts, $content = null) {
        $result = "<blockquote>$content</blockquote>";
        return $result;
    }

}

$quote = new Quote();