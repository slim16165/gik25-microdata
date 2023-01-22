<?php
namespace include\class\Shortcodes;

if(!defined('ABSPATH')) {
    exit;
}
class Slidingbox extends ShortcodeBase
{
    public function __construct()
    {
        $this->shortcode = "slidingbox";
        $this->shortcode = 'md_flipbox';
        parent::__construct();
    }

    public function ShortcodeHandler($atts, $content = null) {

        $mdsb = shortcode_atts([
                'fa_icon' => 'fa fa-search',
                'bg_img' => plugins_url("{asset_path}/images/car1.jpg"),
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

    public function styles() {
        wp_register_style('styles', plugins_url("{$this->asset_path}/css/mdsb.css"), array(), '', 'all');
        wp_enqueue_style('styles');
    }

    public function mdsb_admin_scripts() {
        wp_register_style('styles', plugins_url("{$this->asset_path}/css/fontawesome.min.css"), [], '5.13.1', 'all');
        wp_enqueue_style ('styles');
    }

    public function register_plugin($plugin_array) {
        $plugin_array['md_slidingbox'] = plugins_url("{$this->asset_path}/js/TinyMCE/slidingbox.js");
        return $plugin_array;
    }

    public function register_button($buttons) {
        array_push($buttons, 'md_slidingbox-menu');
        return $buttons;
    }

    public function admin_scripts()
    {
        // TODO: Implement admin_scripts() method.
    }
}

$microdata_slidingbox = new Slidingbox();