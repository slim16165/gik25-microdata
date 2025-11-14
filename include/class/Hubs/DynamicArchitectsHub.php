<?php
namespace gik25microdata\Hubs;

use gik25microdata\ListOfPosts\LinkBuilder;
use gik25microdata\Widgets\ColorWidget;
use WP_Post;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Hub Architetti Dinamico
 * 
 * Genera hub architetti completo con query dinamica WordPress invece di link hardcoded.
 * Sostituisce il vecchio archistars_handler con versione completamente dinamica.
 * 
 * @since 1.0.0
 */
class DynamicArchitectsHub
{
    /**
     * Slug categoria WordPress per architetti
     */
    private const ARCHITECTS_CATEGORY = 'archistar';
    
    /**
     * Tag WordPress per identificare post su architetti
     */
    private const ARCHITECTS_TAG = 'architetti';
    
    /**
     * Numero massimo di architetti da mostrare
     */
    private const MAX_ARCHITECTS = 50;
    
    /**
     * Inizializza lo shortcode
     */
    public static function init(): void
    {
        add_shortcode('hub_architetti', [self::class, 'render']);
        add_shortcode('hub_architetti_dinamico', [self::class, 'render']); // Alias per compatibilità
    }
    
    /**
     * Renderizza l'hub architetti completo
     * 
     * @param array $atts Attributi dello shortcode
     * @return string HTML dell'hub
     */
    public static function render(array $atts = []): string
    {
        $css = ColorWidget::get_carousel_css();
        $builder = LinkBuilder::create('carousel');
        
        $output = "<style>{$css}</style>\n";
        $output .= "<div class='contain'>\n";
        $output .= "<h3>Architetti</h3>\n";
        $output .= "<div class='row'>\n";
        $output .= "<div class='row__inner'>\n";
        
        // Query dinamica per post categoria "archistar" o tag "architetti"
        $posts = self::get_architects_posts();
        
        // Genera i link carosello
        foreach ($posts as $post) {
            if (!$post instanceof WP_Post) {
                continue;
            }
            
            $url = get_permalink($post->ID);
            $title = get_the_title($post->ID);
            
            // Estrai il nome dell'architetto dal titolo (es. "Renzo Piano" -> "Renzo Piano")
            $nome = self::extract_architect_name($title);
            
            $output .= $builder->buildCarouselLink($url, $nome);
        }
        
        $output .= "</div>\n</div>\n</div>\n";
        
        return $output;
    }
    
    /**
     * Ottiene i post sugli architetti
     * 
     * @return array<WP_Post> Array di post
     */
    private static function get_architects_posts(): array
    {
        $posts = [];
        
        // Prova prima con la categoria
        $category = get_category_by_slug(self::ARCHITECTS_CATEGORY);
        if ($category) {
            $query = new \WP_Query([
                'post_type' => 'post',
                'posts_per_page' => self::MAX_ARCHITECTS,
                'orderby' => 'title',
                'order' => 'ASC',
                'category_name' => self::ARCHITECTS_CATEGORY,
                'post_status' => 'publish',
            ]);
            
            $posts = $query->posts;
            wp_reset_postdata();
        }
        
        // Se non ci sono post dalla categoria, prova con il tag
        if (empty($posts)) {
            $tag = get_term_by('slug', self::ARCHITECTS_TAG, 'post_tag');
            if ($tag) {
                $query = new \WP_Query([
                    'post_type' => 'post',
                    'posts_per_page' => self::MAX_ARCHITECTS,
                    'orderby' => 'title',
                    'order' => 'ASC',
                    'tag' => self::ARCHITECTS_TAG,
                    'post_status' => 'publish',
                ]);
                
                $posts = $query->posts;
                wp_reset_postdata();
            }
        }
        
        // Fallback: cerca post con "architetto" o nomi di architetti famosi nel titolo
        if (empty($posts)) {
            $famous_architects = [
                'renzo piano', 'zaha hadid', 'stefano boeri', 'fucksas', 'frank gehry',
                'norman foster', 'rem koolhaas', 'mario botta', 'jean nouvel',
                'santiago calatrava', 'mario cucinella', 'mvrdv', 'herzog de meuron',
                'david chipperfield', 'kengo kuma', 'matteo thun', 'sanaa',
                'daniel libeskind', 'steven holl', 'richard meier', 'som',
                'snohetta', 'toyo ito', 'archea', 'diller scofidio', 'gensler',
                'peter zumthor', 'unstudio', 'coop himmelblau', 'grafton architects',
                'bjarke ingels', 'heatherwick', 'nemesi'
            ];
            
            $query = new \WP_Query([
                'post_type' => 'post',
                'posts_per_page' => self::MAX_ARCHITECTS,
                'orderby' => 'title',
                'order' => 'ASC',
                's' => implode(' ', $famous_architects),
                'post_status' => 'publish',
            ]);
            
            $posts = $query->posts;
            wp_reset_postdata();
        }
        
        // Filtra e ordina i post
        $posts = array_filter($posts, function($post) {
            return $post instanceof WP_Post && $post->post_status === 'publish';
        });
        
        // Ordina per titolo
        usort($posts, function($a, $b) {
            return strcmp($a->post_title, $b->post_title);
        });
        
        // Limita il numero di post
        return array_slice($posts, 0, self::MAX_ARCHITECTS);
    }
    
    /**
     * Estrae il nome dell'architetto dal titolo del post
     * 
     * @param string $title Titolo del post
     * @return string Nome dell'architetto pulito
     */
    private static function extract_architect_name(string $title): string
    {
        // Rimuovi prefissi comuni
        $title = preg_replace('/^(architetto|archistar|studio)\s+/i', '', $title);
        
        // Rimuovi suffissi comuni
        $title = preg_replace('/\s+(architetto|archistar|studio|biografia|opere)$/i', '', $title);
        
        // Capitalizza la prima lettera
        $title = ucfirst(trim($title));
        
        return $title;
    }
}

