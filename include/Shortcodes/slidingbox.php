<?php
if(!defined('ABSPATH')) {
    exit;
}
class MicrodataSlidingbox {

    public function __construct() {
        add_shortcode(PLUGIN_NAME_PREFIX . 'slidingbox', array($this, 'shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'mdsb_styles'));
    }

    public function shortcode($atts, $content = null) {

        $mdsb = shortcode_atts(array(
                'fa_icon' => 'fa fa-search',
                'bg_img' => plugins_url('/gik25-microdata/assets/images/car1.jpg'),
                'url' => false
            ), $atts);

        if($mdsb['url']) {
            $mdsb_url = $mdsb['url'];
        }
        else {
            $mdsb_url =  '#';
        }

        $mdsb_html = <<<AAA
            <div class="mdsb-wrapper" style="background-image: url({$mdsb['bg_img']});">
                <div class="mdsb">
                    <a href="{$mdsb_url}">
                    </a>
                    <div class="mdsb-inner">
                        <a href="{$mdsb['bg_img']}">
                            <i class="{$mdsb['fa_icon']}"></i>
                        </a>
                    </div>
                </div>
            </div>
AAA;

        return $mdsb_html;
    }

    public function mdsb_styles() {
        wp_register_style('mdsb-styles', plugins_url('/gik25-microdata/assets/css/mdsb.css'), array(), '', 'all');
        wp_register_style('mdsb-fa-styles', plugins_url('/gik25-microdata/assets/css/all.min.css'), array(), '5.13.1', 'all');
        wp_enqueue_style('mdsb-styles');
        wp_enqueue_style('mdsb-fa-styles');
    }

}

$microdata_slidingbox = new MicrodataSlidingbox();