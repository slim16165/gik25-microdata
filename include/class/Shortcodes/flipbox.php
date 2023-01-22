<?php
declare(strict_types=1);
namespace include\class\Shortcodes;

if(!defined('ABSPATH')) {
    exit;
}
class Flipbox extends ShortcodeBase
{

    public function __construct()
    {
        add_shortcode('md_flipbox', [$this, 'shortcode']);

        //Frontend only
        add_action('template_redirect', array($this, 'pluginOptimizedLoad'));

        if (is_admin())
        {
            add_action('admin_enqueue_scripts', [$this, 'mdfb_admin_scripts']);
            add_filter('mce_external_plugins', [$this, 'mdfb_register_plugin']);
            add_filter('mce_buttons', [$this, 'mdfb_register_button']);
        }
    }

    public function pluginOptimizedLoad() : void
    {
        //In alternativa potrei usare !is_admin
        $isFe = is_page() || is_singular() || is_front_page() || is_single();

        if ($isFe && $this->PostContainsShortCode('md_flipbox'))
        {
            add_action('wp_enqueue_scripts',    [$this, 'mdfb_styles']);
        }
    }

    public function shortcode($atts, $content = null): string
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

    public function mdfb_styles() {
        wp_register_style('mdfb-styles', plugins_url('/gik25-microdata/assets/css/mdfb.css'), [], '', 'all');
        wp_enqueue_style('mdfb-styles');
    }

    public function mdfb_admin_scripts() {
        wp_register_style('mdfb-fa-styles', plugins_url('/gik25-microdata/assets/css/fontawesome.min.css'), [], '5.13.1', 'all');
        wp_enqueue_style('mdfb-fa-styles');
    }

    public function mdfb_register_plugin($plugin_array) {
        $plugin_array['md_flipbox'] = plugins_url('/gik25-microdata/assets/js/TinyMCE/flipbox.js');
        return $plugin_array;
    }
    
    public function mdfb_register_button($buttons) {
        array_push($buttons, 'md_flipbox-menu');
        return $buttons;
    }

}

$microdata_flipbox = new Flipbox();