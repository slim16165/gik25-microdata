<?php
if(!defined('ABSPATH')) {
    exit;
}
class MicrodataFlipbox {

    public function __construct() {
        add_shortcode('microdata_flipbox', array($this, 'shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'mdfb_styles'));
    }

    public function shortcode($atts, $content = null) {

        $mdfb = shortcode_atts(array(
                'fa_icon' => 'fas fa-shopping-cart',
                'title' => 'Sample Title',
                'sub_title' => 'Sample Sub Title',
                // 'url' => site_url(),
                'url' => false,
                'text'  => 'Lorem ipsum dolar sit amet lorem ipsum dolar sit amet lorem ipsum dolar sit amet'
            ), $atts);

        if($mdfb['url']) {
            $mdfb_text = '<a href="' . $mdfb['url'] . '">' . $mdfb['text'] . '</a>';
        }
        else {
            $mdfb_text =  $mdfb['text'];
        }

        $mdfb_html = <<<ABC
            <div class="md-flip-box flip-box">
                <div class="flip-box-inner">
                    <div class="flip-box-front">
                        <span class="mdfb-icon-wrap">
                            <i class="{$mdfb['fa_icon']} mdfb-icon"></i>
                        </span>
                        <span class="flip-box-title">{$mdfb['title']}</span>
                        <span class="flip-box-sub-title">{$mdfb['sub_title']}</span>
                    </div>
                    <div class="flip-box-back">
                        <p class="mdfb-text">{$mdfb_text}</p>
                    </div>
                </div>
            </div>
ABC;

        return $mdfb_html;
    }

    public function mdfb_styles() {
        wp_register_style('mdfb-styles', plugins_url('/gik25-microdata/assets/css/mdfb.css'), array(), '', 'all');
        wp_register_style('mdfb-fa-styles', plugins_url('/gik25-microdata/assets/css/all.min.css'), array(), '5.13.1', 'all');
        wp_enqueue_style('mdfb-styles');
        wp_enqueue_style('mdfb-fa-styles');
    }

}

$microdata_flipbox = new MicrodataFlipbox();