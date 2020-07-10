<?php
if(!defined('ABSPATH')) {
    exit;
}
class MicrodataBlinkingButton {

    public function __construct() {
        add_shortcode(PLUGIN_NAME_PREFIX . 'blinkingbutton', array($this, 'shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'mdbb_styles'));
        add_action('wp_enqueue_scripts', array($this, 'mdbb_scripts'));
    }

    public function shortcode($atts, $content = null) {

        $mdbb = shortcode_atts(array(
                'fa_icon' => 'fa fa-bars',
                'url' => false,
                'text'  => 'Button'
            ), $atts);

        if($mdbb['url']) {
            $mdbb_text = '<a href="' . $mdbb['url'] . '">' . $mdbb['text'] . '</a>';
        }
        else {
            $mdbb_text =  $mdbb['text'];
        }

        $mdbb_html = <<<ABC
            <div class="mdbb-wrapper">
                <span class="mdbb-icon-wrap">
                    <i class="{$mdbb['fa_icon']} mdbb-icon"></i>
                </span>
                <br>
                {$mdbb_text}
                <div class="mdbb"></div>
            </div>
ABC;

        return $mdbb_html;
    }

    public function mdbb_styles() {
        wp_register_style('mdbb-styles', plugins_url('/gik25-microdata/assets/css/mdbb.css'), array(), '', 'all');
        wp_register_style('mdbb-fa-styles', plugins_url('/gik25-microdata/assets/css/all.min.css'), array(), '5.13.1', 'all');
        wp_enqueue_style('mdbb-styles');
        wp_enqueue_style('mdbb-fa-styles');
    }

    public function mdbb_scripts() {
        wp_register_script('mdbb-script', plugins_url('/gik25-microdata/assets/js/mdbb.js'), array('jquery'));
        wp_enqueue_script('mdbb-script');
    }

}

$microdata_blinkingbutton= new MicrodataBlinkingButton();