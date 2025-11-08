<?php
namespace gik25microdata\Shortcodes;

if (!defined('ABSPATH'))
{
    exit;
}

class BlinkingButton extends ShortcodeBase
{
    public function __construct()
    {
        $this->shortcode = 'md_blinkingbutton';
        parent::__construct();
        
        // Registra alias dello shortcode
        add_shortcode('blinkingbutton', array($this, 'ShortcodeHandler'));
    }

    public function ShortcodeHandler($atts, $content = null): string
    {
        $mdbb = shortcode_atts(array(
            'fa_icon' => 'fa fa-bars',
            'url' => false,
            'text' => 'Button'
        ), $atts);

        if ($mdbb['url'])
        {
            $mdbb_text = '<a href="' . $mdbb['url'] . '">' . $mdbb['text'] . '</a>';
        } else
        {
            $mdbb_text = $mdbb['text'];
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

    public function styles()
{
    wp_register_style('styles', plugins_url("{$this->asset_path}/css/mdbb.css"), array(), '', 'all');
    wp_enqueue_style('styles');
}

    public function admin_scripts()
    {
        wp_register_style('styles', plugins_url("{$this->asset_path}/css/fontawesome.min.css"), array(), '5.13.1', 'all');
        wp_enqueue_style('styles');
    }

    public function scripts()
    {
        wp_register_script('mdbb-script', plugins_url("{$this->asset_path}/js/mdbb.js"), array('jquery'), '', true);
        wp_enqueue_script('mdbb-script');
    }

    public function register_plugin($plugin_array)
    {
        $plugin_array['md_blinkingbutton'] = plugins_url("{$this->asset_path}/js/TinyMCE/blinkingbutton.js");
        return $plugin_array;
    }

    public function register_button($buttons)
    {
        array_push($buttons, 'md_blinkingbutton-menu');
        return $buttons;
    }

}

$microdata_blinkingbutton = new BlinkingButton();