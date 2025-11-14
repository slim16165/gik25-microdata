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
 * Hub Colori Dinamico
 * 
 * Genera hub colori completo con query dinamica WordPress invece di link hardcoded.
 * Sostituisce il vecchio link_colori_handler con versione completamente dinamica.
 * 
 * @since 1.0.0
 */
class DynamicColorHub
{
    /**
     * Tag WordPress per identificare post sui colori
     */
    private const COLOR_TAG = 'colori';
    
    /**
     * Tag WordPress per identificare post Pantone
     */
    private const PANTONE_TAG = 'pantone';
    
    /**
     * Tag WordPress per identificare post su abbinamenti colori
     */
    private const ABBINAMENTI_TAG = 'abbinamento-colori';
    
    /**
     * Tag WordPress per identificare post su palette colori
     */
    private const PALETTE_TAG = 'palette';
    
    /**
     * Numero massimo di colori da mostrare nella sezione principale
     */
    private const MAX_COLORI_PRINCIPALI = 50;
    
    /**
     * Numero massimo di post Pantone da mostrare
     */
    private const MAX_PANTONE = 10;
    
    /**
     * Numero massimo di post abbinamenti da mostrare
     */
    private const MAX_ABBINAMENTI = 12;
    
    /**
     * Numero massimo di post articoli vari da mostrare
     */
    private const MAX_ARTICOLI_VARI = 12;
    
    /**
     * Inizializza lo shortcode
     */
    public static function init(): void
    {
        add_shortcode('hub_colori', [self::class, 'render']);
        add_shortcode('hub_colori_dinamico', [self::class, 'render']); // Alias per compatibilità
    }
    
    /**
     * Renderizza l'hub colori completo
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
        $output .= "<h3>Articoli sui colori</h3>\n";
        
        // Sezione: Colori Specifici
        $output .= self::render_colori_specifici($builder);
        
        // Sezione: Pantone
        $output .= self::render_pantone($builder);
        
        // Sezione: Articoli Vari (abbinamenti, palette, guide)
        $output .= self::render_articoli_vari($builder);
        
        $output .= "</div>\n";
        
        return $output;
    }
    
    /**
     * Renderizza la sezione "Colori Specifici"
     * 
     * @param LinkBuilder $builder Builder per creare i link
     * @return string HTML della sezione
     */
    private static function render_colori_specifici(LinkBuilder $builder): string
    {
        $output = "<p>Colori Specifici</p>\n";
        $output .= "<div class='row'>\n";
        $output .= "<div class='row__inner'>\n";
        
        // Query dinamica per post con tag "colori"
        $post_ids = TagHelper::find_post_id_from_taxonomy(self::COLOR_TAG, 'post_tag');
        
        if (empty($post_ids)) {
            // Fallback: cerca post con "colore" nel titolo o contenuto
            $query = new \WP_Query([
                'post_type' => 'post',
                'posts_per_page' => self::MAX_COLORI_PRINCIPALI,
                'orderby' => 'title',
                'order' => 'ASC',
                's' => 'colore',
                'post_status' => 'publish',
            ]);
            
            $posts = $query->posts;
            wp_reset_postdata();
        } else {
            // Limita il numero di post
            $post_ids = array_slice($post_ids, 0, self::MAX_COLORI_PRINCIPALI);
            $posts = array_map('get_post', $post_ids);
            $posts = array_filter($posts, function($post) {
                return $post instanceof WP_Post && $post->post_status === 'publish';
            });
            
            // Ordina per titolo
            usort($posts, function($a, $b) {
                return strcmp($a->post_title, $b->post_title);
            });
        }
        
        // Genera i link carosello
        foreach ($posts as $post) {
            if (!$post instanceof WP_Post) {
                continue;
            }
            
            $url = get_permalink($post->ID);
            $title = get_the_title($post->ID);
            
            // Estrai il nome del colore dal titolo (es. "Colore Rosso" -> "Colore Rosso")
            $nome = self::extract_color_name($title);
            
            $output .= $builder->buildCarouselLink($url, $nome);
        }
        
        $output .= "</div>\n</div>\n";
        
        return $output;
    }
    
    /**
     * Renderizza la sezione "Pantone"
     * 
     * @param LinkBuilder $builder Builder per creare i link
     * @return string HTML della sezione
     */
    private static function render_pantone(LinkBuilder $builder): string
    {
        $output = "<p>Colori Pantone</p>\n";
        $output .= "<div class='row'>\n";
        $output .= "<div class='row__inner'>\n";
        
        // Query dinamica per post con tag "pantone"
        $post_ids = TagHelper::find_post_id_from_taxonomy(self::PANTONE_TAG, 'post_tag');
        
        if (empty($post_ids)) {
            // Fallback: cerca post con "pantone" nel titolo
            $query = new \WP_Query([
                'post_type' => 'post',
                'posts_per_page' => self::MAX_PANTONE,
                'orderby' => 'date',
                'order' => 'DESC',
                's' => 'pantone',
                'post_status' => 'publish',
            ]);
            
            $posts = $query->posts;
            wp_reset_postdata();
        } else {
            $post_ids = array_slice($post_ids, 0, self::MAX_PANTONE);
            $posts = array_map('get_post', $post_ids);
            $posts = array_filter($posts, function($post) {
                return $post instanceof WP_Post && $post->post_status === 'publish';
            });
            
            // Ordina per data (più recenti prima)
            usort($posts, function($a, $b) {
                return strtotime($b->post_date) - strtotime($a->post_date);
            });
        }
        
        // Genera i link carosello
        foreach ($posts as $post) {
            if (!$post instanceof WP_Post) {
                continue;
            }
            
            $url = get_permalink($post->ID);
            $title = get_the_title($post->ID);
            
            $output .= $builder->buildCarouselLink($url, $title);
        }
        
        $output .= "</div>\n</div>\n<br /><br />\n";
        
        return $output;
    }
    
    /**
     * Renderizza la sezione "Articoli Vari" (abbinamenti, palette, guide)
     * 
     * @param LinkBuilder $builder Builder per creare i link
     * @return string HTML della sezione
     */
    private static function render_articoli_vari(LinkBuilder $builder): string
    {
        $output = "<p>Articoli Vari</p>\n";
        $output .= "<div class='row'>\n";
        $output .= "<div class='row__inner'>\n";
        
        // Raccogli post da diversi tag
        $all_post_ids = [];
        
        // Post con tag abbinamento-colori
        $abbinamenti_ids = TagHelper::find_post_id_from_taxonomy(self::ABBINAMENTI_TAG, 'post_tag');
        if (!empty($abbinamenti_ids)) {
            $all_post_ids = array_merge($all_post_ids, $abbinamenti_ids);
        }
        
        // Post con tag palette
        $palette_ids = TagHelper::find_post_id_from_taxonomy(self::PALETTE_TAG, 'post_tag');
        if (!empty($palette_ids)) {
            $all_post_ids = array_merge($all_post_ids, $palette_ids);
        }
        
        // Rimuovi duplicati
        $all_post_ids = array_unique($all_post_ids);
        
        if (empty($all_post_ids)) {
            // Fallback: cerca post con keywords specifiche
            $query = new \WP_Query([
                'post_type' => 'post',
                'posts_per_page' => self::MAX_ARTICOLI_VARI,
                'orderby' => 'date',
                'order' => 'DESC',
                's' => 'abbinamento colori palette complementari',
                'post_status' => 'publish',
            ]);
            
            $posts = $query->posts;
            wp_reset_postdata();
        } else {
            $all_post_ids = array_slice($all_post_ids, 0, self::MAX_ARTICOLI_VARI);
            $posts = array_map('get_post', $all_post_ids);
            $posts = array_filter($posts, function($post) {
                return $post instanceof WP_Post && $post->post_status === 'publish';
            });
        }
        
        // Genera i link carosello
        foreach ($posts as $post) {
            if (!$post instanceof WP_Post) {
                continue;
            }
            
            $url = get_permalink($post->ID);
            $title = get_the_title($post->ID);
            
            $output .= $builder->buildCarouselLink($url, $title);
        }
        
        $output .= "</div>\n</div>\n";
        
        return $output;
    }
    
    /**
     * Estrae il nome del colore dal titolo del post
     * 
     * @param string $title Titolo del post
     * @return string Nome del colore pulito
     */
    private static function extract_color_name(string $title): string
    {
        // Rimuovi prefissi comuni
        $title = preg_replace('/^(colore|color|colori)\s+/i', '', $title);
        
        // Rimuovi suffissi comuni
        $title = preg_replace('/\s+(arredamento|pareti|parete|interni|interno)$/i', '', $title);
        
        // Capitalizza la prima lettera
        $title = ucfirst(trim($title));
        
        return $title;
    }
}

