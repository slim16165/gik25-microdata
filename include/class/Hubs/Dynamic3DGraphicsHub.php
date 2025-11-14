<?php
namespace gik25microdata\Hubs;

use gik25microdata\ListOfPosts\LinkBuilder;
use gik25microdata\Widgets\ColorWidget;
use gik25microdata\Utility\TagHelper;
use WP_Post;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Hub Programmi 3D Dinamico
 * 
 * Genera hub programmi 3D completo con query dinamica WordPress invece di link hardcoded.
 * Sostituisce il vecchio grafica3d_handler con versione completamente dinamica.
 * 
 * @since 1.0.0
 */
class Dynamic3DGraphicsHub
{
    /**
     * Tag WordPress per identificare post su grafica 3D
     */
    private const GRAFICA_3D_TAG = 'grafica-3d';
    
    /**
     * Tag WordPress per identificare post su programmi CAD
     */
    private const CAD_TAG = 'cad';
    
    /**
     * Tag WordPress per identificare post su rendering
     */
    private const RENDERING_TAG = 'rendering';
    
    /**
     * Numero massimo di programmi da mostrare
     */
    private const MAX_PROGRAMMI = 20;
    
    /**
     * Lista di programmi 3D comuni per fallback
     */
    private const PROGRAMMI_3D_KEYWORDS = [
        'freecad', 'homestyler', 'autodesk revit', 'archicad', 'maya 3d',
        'blender 3d', 'librecad', 'draftsight', 'lumion', 'rhinoceros',
        'sketchup', 'programmi gratuiti progettazione 3d'
    ];
    
    /**
     * Inizializza lo shortcode
     */
    public static function init(): void
    {
        add_shortcode('hub_grafica3d', [self::class, 'render']);
        add_shortcode('hub_grafica3d_dinamico', [self::class, 'render']); // Alias per compatibilità
    }
    
    /**
     * Renderizza l'hub programmi 3D completo
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
        $output .= "<h3>Programmi di Grafica 3D</h3>\n";
        $output .= "<div class='row'>\n";
        $output .= "<div class='row__inner'>\n";
        
        // Query dinamica per post su programmi 3D
        $posts = self::get_3d_graphics_posts();
        
        // Genera i link carosello
        foreach ($posts as $post) {
            if (!$post instanceof WP_Post) {
                continue;
            }
            
            $url = get_permalink($post->ID);
            $title = get_the_title($post->ID);
            
            // Estrai il nome del programma dal titolo
            $nome = self::extract_program_name($title);
            
            $output .= $builder->buildCarouselLink($url, $nome);
        }
        
        $output .= "</div>\n</div>\n</div>\n";
        
        return $output;
    }
    
    /**
     * Ottiene i post sui programmi 3D
     * 
     * @return array<WP_Post> Array di post
     */
    private static function get_3d_graphics_posts(): array
    {
        $all_post_ids = [];
        
        // Prova con tag "grafica-3d"
        $post_ids = TagHelper::find_post_id_from_taxonomy(self::GRAFICA_3D_TAG, 'post_tag');
        if (!empty($post_ids)) {
            $all_post_ids = array_merge($all_post_ids, $post_ids);
        }
        
        // Prova con tag "cad"
        $post_ids = TagHelper::find_post_id_from_taxonomy(self::CAD_TAG, 'post_tag');
        if (!empty($post_ids)) {
            $all_post_ids = array_merge($all_post_ids, $post_ids);
        }
        
        // Prova con tag "rendering"
        $post_ids = TagHelper::find_post_id_from_taxonomy(self::RENDERING_TAG, 'post_tag');
        if (!empty($post_ids)) {
            $all_post_ids = array_merge($all_post_ids, $post_ids);
        }
        
        // Rimuovi duplicati
        $all_post_ids = array_unique($all_post_ids);
        
        if (!empty($all_post_ids)) {
            $all_post_ids = array_slice($all_post_ids, 0, self::MAX_PROGRAMMI);
            $posts = array_map('get_post', $all_post_ids);
            $posts = array_filter($posts, function($post) {
                return $post instanceof WP_Post && $post->post_status === 'publish';
            });
        } else {
            // Fallback: cerca post con keywords specifiche
            $query = new \WP_Query([
                'post_type' => 'post',
                'posts_per_page' => self::MAX_PROGRAMMI,
                'orderby' => 'title',
                'order' => 'ASC',
                's' => implode(' ', self::PROGRAMMI_3D_KEYWORDS),
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
        return array_slice($posts, 0, self::MAX_PROGRAMMI);
    }
    
    /**
     * Estrae il nome del programma dal titolo del post
     * 
     * @param string $title Titolo del post
     * @return string Nome del programma pulito
     */
    private static function extract_program_name(string $title): string
    {
        // Rimuovi prefissi comuni
        $title = preg_replace('/^(programma|software|migliori|guida|tutorial)\s+/i', '', $title);
        
        // Rimuovi suffissi comuni
        $title = preg_replace('/\s+(3d|grafica|cad|rendering|tutorial|guida|review)$/i', '', $title);
        
        // Capitalizza la prima lettera
        $title = ucfirst(trim($title));
        
        return $title;
    }
}

