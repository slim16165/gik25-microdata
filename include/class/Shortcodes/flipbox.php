<?php
declare(strict_types=1);
namespace gik25microdata\Shortcodes;

if(!defined('ABSPATH')) {
    exit;
}
class Flipbox extends ShortcodeBase
{
    public function __construct()
    {
        $this->shortcode = 'md_flipbox';
        parent::__construct();
        
        // Registra alias dello shortcode
        add_shortcode('flipbox', array($this, 'ShortcodeHandler'));
    }

    public function ShortcodeHandler($atts, $content = null): string
    {
        $mdfb = shortcode_atts([
                'fa_icon' => 'fas fa-shopping-cart',
                'title' => 'Sample Title',
                'sub_title' => 'Sample Sub Title',
                // 'url' => site_url(),
                'url' => false,
                'text'  => 'Lorem ipsum dolar sit amet lorem ipsum dolar sit amet lorem ipsum dolar sit amet'
        ], $atts);

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

    public function styles() {
        wp_register_style('styles', plugins_url("{$this->asset_path}/css/mdfb.css"), [], '', 'all');
        wp_enqueue_style('styles');
    }

    public function admin_scripts() {
        wp_register_style('styles', plugins_url("{$this->asset_path}/css/fontawesome.min.css"), [], '5.13.1', 'all');
        wp_enqueue_style('styles');
    }

    public function register_plugin($plugin_array) {
        $plugin_array['md_flipbox'] = plugins_url("{$this->asset_path}/js/TinyMCE/flipbox.js");
        return $plugin_array;
    }
    
    public function register_button($buttons) {
        array_push($buttons, 'md_flipbox-menu');
        return $buttons;
    }

}

$microdata_flipbox = new Flipbox();