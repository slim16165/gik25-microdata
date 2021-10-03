<?php
if(!defined('ABSPATH')) {
    exit;
}
class Slidingbox extends ShortcodeBase
{

    public function __construct()
    {
        add_shortcode('md_slidingbox', array($this, 'shortcode'));

        //Frontend only
        add_action('template_redirect', array($this, 'pluginOptimizedLoad'));

        if (is_admin())
        {
            add_action('admin_enqueue_scripts', array($this, 'mdsb_admin_scripts'));
            add_filter('mce_external_plugins', array($this, 'mdsb_register_plugin'));
            add_filter('mce_buttons', array($this, 'mdsb_register_button'));
        }
    }

    public function pluginOptimizedLoad() : void
    {
        //In alternativa potrei usare !is_admin
        $isFe = is_page() || is_singular() || is_front_page() || is_single();

        if ($isFe && $this->PostContainsShortCode('md_slidingbox'))
        {
            add_action('wp_enqueue_scripts',    array($this, 'mdsb_styles'));
        }
    }

    public function shortcode($atts, $content = null) {

        $mdsb = shortcode_atts([
                'fa_icon' => 'fa fa-search',
                'bg_img' => plugins_url('/gik25-microdata/assets/images/car1.jpg'),
                'url' => false
        ], $atts);

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
        wp_enqueue_style('mdsb-styles');
    }

    public function mdsb_admin_scripts() {
        wp_register_style('mdbb-fa-styles', plugins_url('/gik25-microdata/assets/css/fontawesome.min.css'), [], '5.13.1', 'all');
        wp_enqueue_style ('mdbb-fa-styles');
    }

    public function mdsb_register_plugin($plugin_array) {
        $plugin_array['md_slidingbox'] = plugins_url('/gik25-microdata/assets/js/TinyMCE/slidingbox.js');
        return $plugin_array;
    }

    public function mdsb_register_button($buttons) {
        array_push($buttons, 'md_slidingbox-menu');
        return $buttons;
    }

}

$microdata_slidingbox = new Slidingbox();