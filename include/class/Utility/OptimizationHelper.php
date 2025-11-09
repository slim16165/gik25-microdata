<?php
namespace gik25microdata\Utility;
class OptimizationHelper
{
    public function __construct()
    {
        self::ExecuteAfterTemplateRedirect([self::class, "IncludeCssOnPosts"]);
    }

    public static function ExecuteAfterTemplateRedirect($delegate) : void
    {
        // This is the correct way of re-writing the function.
        // This action hook executes just before WordPress determines which template page to load.
        // Source: https://stackoverflow.com/questions/22070223/how-can-i-use-is-page-inside-a-plugin

       add_action( 'template_redirect', $delegate);
    }

    //It's not executed on /wp-admin pages
    //Not executed for backend pages
    public static function IncludeCssOnPosts(): void
    {
        global $post;

        /* Debug
         * QM::info( "is_page " . is_page() ); //check if it's a Page (not post)
         * QM::info( "is_singular post" . is_singular('post') );
         * QM::info( "is_a " . is_a($post, 'WP_Post') ); //returns true everywhere*/

        if ( is_singular('post') && is_a($post, 'WP_Post') && !empty($post->post_content) && is_string($post->post_content))
        {
            //wp_enqueue_style('Footer_Links-style', get_stylesheet_directory_uri() . '/CSS_Components/Footer_Links.css?v=2.5', array( 'parent-style'));
            if (str_contains($post->post_content, '['))
            {
                wp_enqueue_style('css_single_pages', plugins_url() . '/gik25-microdata/assets/css/revious-microdata.css');
            }
        }

        //When the Dashboard or the administration panels are being displayed.
//        if ( is_admin() ||
//            ( !is_front_page() || !is_single()))
    }

    //La funzione è stata lasciata a metà, NON esegue il caricamento selettivo dei plugin
    //Perlomeno va testata
    public static function ConditionalLoadCssJsOnPostsWhichContainAnyEnabledShortcode(): void
    {
        //PC::debug(1, 1);

        if (is_single())
        {
            $enabled_shortcode_found = self::IsAnyShortcodeEnabled();

            if($enabled_shortcode_found)
            {
                // Registra l'hook per caricare CSS e JS durante wp_enqueue_scripts
                add_action('wp_enqueue_scripts', array(__CLASS__, 'load_css_js_on_posts_which_contain_enabled_shortcodes'), 1001);
            }
        }
    }

    /**
     * Carica CSS e JS per i post che contengono shortcode abilitati.
     * Questo metodo viene chiamato durante l'hook wp_enqueue_scripts.
     */
    public static function load_css_js_on_posts_which_contain_enabled_shortcodes(): void
    {
        // Verifica che siamo su un singolo post
        if (!is_single())
        {
            return;
        }

        // Carica il CSS
        wp_enqueue_style(
            'css_for_enabled_shortcodes',
            plugins_url() . '/gik25-microdata/assets/css/css-for-enabled-shortcodes.css',
            array(),
            '1.0.0'
        );

        // Carica il JS
        wp_enqueue_script(
            'css_for_enabled_shortcodes',
            plugins_url() . '/gik25-microdata/assets/js/js-for-enabled-shortcodes.js',
            array(),
            '1.0.0',
            true
        );
    }

    //Inglobare nell'altra e poi cancellare
    public static function load_css_or_js_specific_pages()
    {
        if (is_single())
        {
            $plugin_url = plugin_dir_url(__FILE__);
            wp_enqueue_style('css_single_pages', plugins_url() . '/gik25-microdata/assets/css/revious-microdata.css');
            //If the line over is not working check if the bottom works.. I don't remember which is the fix
	        //wp_enqueue_style('css_single_pages', trailingslashit($plugin_url) . '../../../assets/css/revious-microdata.css', array());


            // Register the style like this for a plugin:
            //wp_register_style('revious-quotes-styles', plugins_url('/revious_microdata.css', __FILE__), array(), '1.7.5', 'all');
            // For either a plugin or a theme, you can then enqueue the style:
            //wp_enqueue_style('revious-quotes-styles');
        }
        //else if(is_category() || is_tag())
    }

    protected static function IsAnyShortcodeEnabled(): bool
    {
        $enabledShortcodes = self::GetListOfEnabledShortcodesFromOptions();
        // Se non ci sono shortcode configurati, assumiamo che possa essere presente uno shortcode (approccio cauto)
        if(!isset($enabledShortcodes) || !is_array($enabledShortcodes))
            return true; //if I can't find any option I should suppose that a shordcode may be enabled (cautelative), instead if the list is simply populatew with few elemetns I have to check

        return self::CheckIfShortcodeIsUsedInThisPost($enabledShortcodes);
    }

    private static function GetListOfEnabledShortcodesFromOptions(): ?array
    {
        //Is the option present?
        $shortcode_names_arr = get_option('revious_microdata_option_name');

        // Verifica che l'opzione esista e sia un array
        if (empty($shortcode_names_arr) || !is_array($shortcode_names_arr))
        {
            return null;
        }

        // Verifica che la chiave 'shortcode_names' esista
        if (!isset($shortcode_names_arr['shortcode_names']) || empty($shortcode_names_arr['shortcode_names']))
        {
            return null;
        }

        $shortcode_names = $shortcode_names_arr['shortcode_names'];

        // Verifica che sia una stringa
        if (!is_string($shortcode_names))
        {
            return null;
        }

        // Estrai gli shortcode separati da virgola e pulisci l'array
        $shortcode_names_arr_2 = explode(',', $shortcode_names);
        
        // Rimuovi spazi bianchi e elementi vuoti
        $shortcode_names_arr_2 = array_map('trim', $shortcode_names_arr_2);
        $shortcode_names_arr_2 = array_filter($shortcode_names_arr_2, function($value) {
            return !empty($value) && is_string($value);
        });

        // Ri-indexa l'array e verifica che non sia vuoto
        $shortcode_names_arr_2 = array_values($shortcode_names_arr_2);
        
        if (empty($shortcode_names_arr_2))
        {
            return null;
        }

        return $shortcode_names_arr_2;
    }

    /**
     * Verifica se uno degli shortcode specificati è presente nel contenuto del post corrente.
     * 
     * @param array $shortcodes Array di nomi di shortcode da verificare
     * @return bool True se almeno uno shortcode è presente nel post, false altrimenti
     */
    protected static function CheckIfShortcodeIsUsedInThisPost(array $shortcodes): bool
    {
        global $post;

        // Verifica che il post globale sia valido
        if (!is_a($post, 'WP_Post'))
        {
            return false;
        }

        // Verifica che il contenuto del post sia disponibile
        if (empty($post->post_content) || !is_string($post->post_content))
        {
            return false;
        }

        // Se l'array è vuoto, non ci sono shortcode da verificare
        if (empty($shortcodes))
        {
            return false;
        }

        // Verifica ogni shortcode
        foreach ($shortcodes as $shortcode_name)
        {
            // Assicuriamoci che $shortcode_name sia una stringa valida
            if (empty($shortcode_name) || !is_string($shortcode_name))
            {
                continue;
            }

            if (has_shortcode($post->post_content, $shortcode_name))
            {
                return true; // $enabled_shortcode_found
            }
        }
        
        return false;
    }

}

new OptimizationHelper();
