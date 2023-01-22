<?php
namespace include\class\Shortcodes;

abstract class ShortcodeBase
{
//    public function PostContainsShortCode($shortcode): bool
//    {
//        global $post;
//        if (str_contains($post->post_content, $shortcode))
//            return true;
//        else return false;
//    }

    public function __construct()
    {
        add_action('template_redirect', array($this, 'pluginOptimizedLoad'));

        if (is_admin())
        {
            add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
            add_filter('mce_external_plugins',  array($this, 'register_plugin'));
            add_filter('mce_buttons',           array($this, 'register_button'));
        }
    }

    public function pluginOptimizedLoad(): void
    {
        //In alternativa potrei usare !is_admin
        $isFe = is_page() || is_singular() || is_front_page() || is_single();

        if ($isFe && $this->PostContainsShortCode($this->shortcode))
        {
//            add_shortcode('md_boxinfo',     array(__CLASS__, 'shortcode'));
//            add_shortcode('boxinfo',        array(__CLASS__, 'shortcode'));
//            add_shortcode('boxinformativo', array(__CLASS__, 'shortcode'));
            add_shortcode($this->shortcode,  array($this, 'ShortcodeHandler'));
            add_action('wp_enqueue_scripts', array($this, 'styles'));
            add_action('wp_enqueue_scripts', array($this, 'scripts'));
        }
    }

    protected function PostContainsShortCode($shortcode): bool
    {
        global $post;
        return is_a($post, 'WP_Post') && has_shortcode($post->post_content, $shortcode);
    }

    public abstract function ShortcodeHandler($atts, $content = null);

    public abstract function styles();

    public abstract function admin_scripts();

    public abstract function register_plugin($plugin_array);

    public abstract function register_button($buttons);

    protected string $shortcode;
}


