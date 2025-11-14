<?php
namespace gik25microdata\Hubs;

use gik25microdata\ListOfPosts\LinkBuilder;
use gik25microdata\Utility\TagHelper;
use WP_Post;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Cross-Linker Avanzato
 * 
 * Genera link incrociati intelligenti basati su keywords articolo.
 * Supporta combinazioni: Colore + Stanza + IKEA
 * 
 * @since 1.0.0
 */
class AdvancedCrossLinker
{
    /**
     * Numero massimo di link da generare per ogni tipo
     */
    private const MAX_LINKS_PER_TYPE = 6;
    
    /**
     * Inizializza il widget
     */
    public static function init(): void
    {
        // Hook per aggiungere cross-link automatici ai post
        add_filter('the_content', [self::class, 'add_cross_links_to_content'], 20);
    }
    
    /**
     * Aggiunge cross-link al contenuto del post
     * 
     * @param string $content Contenuto del post
     * @return string Contenuto con cross-link aggiunti
     */
    public static function add_cross_links_to_content(string $content): string
    {
        global $post;
        
        if (!is_singular('post') || !$post instanceof WP_Post) {
            return $content;
        }
        
        // Estrai keywords dal post
        $keywords = self::extract_keywords($post);
        
        if (empty($keywords)) {
            return $content;
        }
        
        // Genera cross-link
        $cross_links = self::generate_cross_links($keywords, $post);
        
        if (empty($cross_links)) {
            return $content;
        }
        
        // Aggiungi i link prima della fine del contenuto
        $output = $content;
        $output .= "\n<div class='td-cross-links'>\n";
        $output .= "<h4>Articoli Correlati</h4>\n";
        $output .= "<div class='td-cross-links-grid'>\n";
        $output .= $cross_links;
        $output .= "</div>\n</div>\n";
        
        return $output;
    }
    
    /**
     * Estrae keywords dal post (colore, stanza, IKEA)
     * 
     * @param WP_Post $post Post da analizzare
     * @return array<string, string> Array associativo con keywords estratte
     */
    private static function extract_keywords(WP_Post $post): array
    {
        $keywords = [
            'color' => null,
            'room' => null,
            'ikea' => null,
        ];
        
        // Estrai da tag
        $tags = get_the_tags($post->ID);
        if ($tags) {
            foreach ($tags as $tag) {
                $tag_slug = strtolower($tag->slug);
                
                // Colori comuni
                $colors = ['bianco', 'nero', 'grigio', 'rosso', 'verde', 'blu', 'giallo', 
                          'rosa', 'tortora', 'beige', 'tortora', 'salvia', 'petrolio'];
                foreach ($colors as $color) {
                    if (strpos($tag_slug, $color) !== false) {
                        $keywords['color'] = $color;
                        break 2;
                    }
                }
                
                // Stanze comuni
                $rooms = ['cucina', 'soggiorno', 'camera', 'bagno', 'studio', 'ingresso'];
                foreach ($rooms as $room) {
                    if (strpos($tag_slug, $room) !== false) {
                        $keywords['room'] = $room;
                        break 2;
                    }
                }
                
                // IKEA
                if (strpos($tag_slug, 'ikea') !== false || 
                    in_array($tag_slug, ['billy', 'kallax', 'besta', 'pax', 'metod', 'enhet'])) {
                    $keywords['ikea'] = $tag_slug;
                }
            }
        }
        
        // Estrai da titolo e contenuto se non trovato nei tag
        $title_lower = strtolower($post->post_title);
        $content_lower = strtolower($post->post_content);
        $combined = $title_lower . ' ' . $content_lower;
        
        if (!$keywords['color']) {
            foreach (['verde salvia', 'verde-salvia', 'tortora', 'bianco', 'nero', 'grigio'] as $color) {
                if (strpos($combined, $color) !== false) {
                    $keywords['color'] = $color;
                    break;
                }
            }
        }
        
        if (!$keywords['room']) {
            foreach (['cucina', 'soggiorno', 'camera', 'bagno'] as $room) {
                if (strpos($combined, $room) !== false) {
                    $keywords['room'] = $room;
                    break;
                }
            }
        }
        
        if (!$keywords['ikea']) {
            foreach (['ikea', 'billy', 'kallax', 'besta', 'pax', 'metod'] as $ikea) {
                if (strpos($combined, $ikea) !== false) {
                    $keywords['ikea'] = $ikea;
                    break;
                }
            }
        }
        
        return array_filter($keywords);
    }
    
    /**
     * Genera cross-link basati su keywords
     * 
     * @param array<string, string> $keywords Keywords estratte
     * @param WP_Post $current_post Post corrente
     * @return string HTML dei cross-link
     */
    private static function generate_cross_links(array $keywords, WP_Post $current_post): string
    {
        $builder = LinkBuilder::create('carousel');
        $output = '';
        
        // Query combinata: Colore + Stanza + IKEA
        if (!empty($keywords['color']) && !empty($keywords['room']) && !empty($keywords['ikea'])) {
            $posts = self::query_combined($keywords, $current_post->ID);
            if (!empty($posts)) {
                foreach (array_slice($posts, 0, self::MAX_LINKS_PER_TYPE) as $post) {
                    $url = get_permalink($post->ID);
                    $title = get_the_title($post->ID);
                    $output .= $builder->buildCarouselLink($url, $title);
                }
                return $output;
            }
        }
        
        // Query parziale: Colore + Stanza
        if (!empty($keywords['color']) && !empty($keywords['room'])) {
            $posts = self::query_color_room($keywords, $current_post->ID);
            if (!empty($posts)) {
                foreach (array_slice($posts, 0, self::MAX_LINKS_PER_TYPE) as $post) {
                    $url = get_permalink($post->ID);
                    $title = get_the_title($post->ID);
                    $output .= $builder->buildCarouselLink($url, $title);
                }
                return $output;
            }
        }
        
        // Query parziale: IKEA + Stanza
        if (!empty($keywords['ikea']) && !empty($keywords['room'])) {
            $posts = self::query_ikea_room($keywords, $current_post->ID);
            if (!empty($posts)) {
                foreach (array_slice($posts, 0, self::MAX_LINKS_PER_TYPE) as $post) {
                    $url = get_permalink($post->ID);
                    $title = get_the_title($post->ID);
                    $output .= $builder->buildCarouselLink($url, $title);
                }
                return $output;
            }
        }
        
        // Query singola: Colore
        if (!empty($keywords['color'])) {
            $posts = self::query_color($keywords['color'], $current_post->ID);
            if (!empty($posts)) {
                foreach (array_slice($posts, 0, self::MAX_LINKS_PER_TYPE) as $post) {
                    $url = get_permalink($post->ID);
                    $title = get_the_title($post->ID);
                    $output .= $builder->buildCarouselLink($url, $title);
                }
            }
        }
        
        return $output;
    }
    
    /**
     * Query combinata: Colore + Stanza + IKEA
     * 
     * @param array<string, string> $keywords Keywords
     * @param int $exclude_post_id ID del post da escludere
     * @return array<WP_Post> Array di post
     */
    private static function query_combined(array $keywords, int $exclude_post_id): array
    {
        $search_terms = array_filter([
            $keywords['color'] ?? null,
            $keywords['room'] ?? null,
            $keywords['ikea'] ?? null,
        ]);
        
        $query = new \WP_Query([
            'post_type' => 'post',
            'posts_per_page' => self::MAX_LINKS_PER_TYPE * 2,
            'orderby' => 'relevance',
            's' => implode(' ', $search_terms),
            'post__not_in' => [$exclude_post_id],
            'post_status' => 'publish',
        ]);
        
        $posts = $query->posts;
        wp_reset_postdata();
        
        return array_filter($posts, function($post) {
            return $post instanceof WP_Post;
        });
    }
    
    /**
     * Query: Colore + Stanza
     * 
     * @param array<string, string> $keywords Keywords
     * @param int $exclude_post_id ID del post da escludere
     * @return array<WP_Post> Array di post
     */
    private static function query_color_room(array $keywords, int $exclude_post_id): array
    {
        $query = new \WP_Query([
            'post_type' => 'post',
            'posts_per_page' => self::MAX_LINKS_PER_TYPE * 2,
            'orderby' => 'relevance',
            's' => ($keywords['color'] ?? '') . ' ' . ($keywords['room'] ?? ''),
            'post__not_in' => [$exclude_post_id],
            'post_status' => 'publish',
        ]);
        
        $posts = $query->posts;
        wp_reset_postdata();
        
        return array_filter($posts, function($post) {
            return $post instanceof WP_Post;
        });
    }
    
    /**
     * Query: IKEA + Stanza
     * 
     * @param array<string, string> $keywords Keywords
     * @param int $exclude_post_id ID del post da escludere
     * @return array<WP_Post> Array di post
     */
    private static function query_ikea_room(array $keywords, int $exclude_post_id): array
    {
        $query = new \WP_Query([
            'post_type' => 'post',
            'posts_per_page' => self::MAX_LINKS_PER_TYPE * 2,
            'orderby' => 'relevance',
            's' => 'ikea ' . ($keywords['room'] ?? ''),
            'post__not_in' => [$exclude_post_id],
            'post_status' => 'publish',
        ]);
        
        $posts = $query->posts;
        wp_reset_postdata();
        
        return array_filter($posts, function($post) {
            return $post instanceof WP_Post;
        });
    }
    
    /**
     * Query: Colore
     * 
     * @param string $color Colore
     * @param int $exclude_post_id ID del post da escludere
     * @return array<WP_Post> Array di post
     */
    private static function query_color(string $color, int $exclude_post_id): array
    {
        $post_ids = TagHelper::find_post_id_from_taxonomy($color, 'post_tag');
        
        if (empty($post_ids)) {
            $query = new \WP_Query([
                'post_type' => 'post',
                'posts_per_page' => self::MAX_LINKS_PER_TYPE * 2,
                'orderby' => 'date',
                'order' => 'DESC',
                's' => $color,
                'post__not_in' => [$exclude_post_id],
                'post_status' => 'publish',
            ]);
            
            $posts = $query->posts;
            wp_reset_postdata();
        } else {
            $post_ids = array_diff($post_ids, [$exclude_post_id]);
            $post_ids = array_slice($post_ids, 0, self::MAX_LINKS_PER_TYPE * 2);
            $posts = array_map('get_post', $post_ids);
        }
        
        return array_filter($posts, function($post) {
            return $post instanceof WP_Post && $post->post_status === 'publish';
        });
    }
}

