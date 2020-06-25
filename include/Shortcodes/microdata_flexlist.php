<?php
if(!defined('ABSPATH')) {
    exit;
}

class Flexlist {

    public function __construct() {
        add_shortcode('flexlist', array($this, 'shortcode'));
    }

    public function shortcode($atts, $content = null) {
        $html = $content;
        $dom = new DOMDocument;
        $dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        $nodes = $xpath->query("//ul");
        foreach ($nodes as $node) {
            $node->setAttribute('style', 'display: flex; flex-wrap: wrap;');
        }
    
        $nodes = $xpath->query("//li");
        foreach ($nodes as $node) {
            $node->setAttribute('style', 'margin-right: 5px;');
        }
    
        return $dom->saveHTML();
    }

}

$flexlist = new Flexlist();