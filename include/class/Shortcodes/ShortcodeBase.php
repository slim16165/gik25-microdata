<?php
namespace gik25microdata\Shortcodes;

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
        // Verifica che shortcode sia impostato dalla classe figlia
        if (empty($this->shortcode)) {
            throw new \RuntimeException('La proprietà $shortcode deve essere impostata nella classe figlia prima di chiamare parent::__construct()');
        }
        
        // Inizializza asset_path se non già definito dalla classe figlia
        if (!isset($this->asset_path)) {
            $this->asset_path = '/gik25-microdata/assets';
        }
        
        // Registra sempre lo shortcode - WordPress ha bisogno che sia registrato per processarlo
        add_shortcode($this->shortcode, array($this, 'ShortcodeHandler'));
        
        // Ottimizzazione: carica CSS/JS solo se lo shortcode è presente nel post
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
        // Rimuovi check conditional tags su template_redirect - verificano global $post invece
        global $post;
        if (!is_a($post, 'WP_Post')) {
            return; // Non siamo in un contesto con post valido
        }

        // Ottimizzazione: carica CSS/JS solo se lo shortcode è presente nel post
        if ($this->PostContainsShortCode($this->shortcode))
        {
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
    protected string $asset_path = '/gik25-microdata/assets';
}


