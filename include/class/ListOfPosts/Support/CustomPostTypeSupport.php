<?php
namespace gik25microdata\ListOfPosts\Support;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Supporto per custom post types
 * Estende le funzionalità per supportare CPT personalizzati
 */
class CustomPostTypeSupport
{
    /**
     * Ottiene link da un custom post type
     * 
     * @param string $post_type Tipo di post
     * @param array $args Argomenti per WP_Query
     * @return array Array di link ['target_url' => ..., 'nome' => ...]
     */
    public static function getLinksFromPostType(string $post_type, array $args = []): array
    {
        $defaults = [
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ];
        
        $query_args = array_merge($defaults, $args);
        $query = new \WP_Query($query_args);
        
        $links = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post = get_post();
                
                $links[] = [
                    'target_url' => get_permalink($post->ID),
                    'nome' => get_the_title($post->ID),
                    'commento' => '',
                ];
            }
            wp_reset_postdata();
        }
        
        return $links;
    }
    
    /**
     * Verifica se un post type è supportato
     * 
     * @param string $post_type Tipo di post
     * @return bool True se supportato
     */
    public static function isSupported(string $post_type): bool
    {
        return post_type_exists($post_type);
    }
    
    /**
     * Ottiene tutti i post types supportati
     * 
     * @param bool $public_only Solo post types pubblici
     * @return array Array di post types
     */
    public static function getSupportedPostTypes(bool $public_only = true): array
    {
        $post_types = get_post_types(['public' => $public_only], 'objects');
        $supported = [];
        
        foreach ($post_types as $post_type) {
            $supported[] = [
                'name' => $post_type->name,
                'label' => $post_type->label,
                'public' => $post_type->public,
            ];
        }
        
        return $supported;
    }
    
    /**
     * Crea link da taxonomy terms
     * 
     * @param string $taxonomy Nome della taxonomy
     * @param array $args Argomenti per get_terms
     * @return array Array di link
     */
    public static function getLinksFromTaxonomy(string $taxonomy, array $args = []): array
    {
        $defaults = [
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
        ];
        
        $query_args = array_merge($defaults, $args);
        $terms = get_terms($query_args);
        
        $links = [];
        
        if (!is_wp_error($terms) && !empty($terms)) {
            foreach ($terms as $term) {
                $term_link = get_term_link($term);
                if (!is_wp_error($term_link)) {
                    $links[] = [
                        'target_url' => $term_link,
                        'nome' => $term->name,
                        'commento' => $term->description ?? '',
                    ];
                }
            }
        }
        
        return $links;
    }
}
