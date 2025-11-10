<?php
namespace gik25microdata\Search;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Advanced Search System
 * 
 * Ricerca avanzata con filtri e suggerimenti
 */
class AdvancedSearch
{
    /**
     * Inizializza advanced search
     */
    public static function init(): void
    {
        add_action('wp_ajax_revious_search_suggestions', [self::class, 'getSuggestions']);
        add_action('wp_ajax_nopriv_revious_search_suggestions', [self::class, 'getSuggestions']);
        add_filter('posts_search', [self::class, 'enhanceSearchQuery'], 10, 2);
    }
    
    /**
     * Ottiene suggerimenti ricerca
     */
    public static function getSuggestions(): void
    {
        $query = sanitize_text_field($_GET['q'] ?? '');
        
        if (strlen($query) < 2) {
            wp_send_json_success([]);
            return;
        }
        
        $suggestions = [];
        
        // Suggerimenti da titoli post
        $posts = get_posts([
            's' => $query,
            'posts_per_page' => 5,
            'post_status' => 'publish',
        ]);
        
        foreach ($posts as $post) {
            $suggestions[] = [
                'type' => 'post',
                'title' => $post->post_title,
                'url' => get_permalink($post->ID),
            ];
        }
        
        // Suggerimenti da categorie
        $categories = get_terms([
            'taxonomy' => 'category',
            'search' => $query,
            'number' => 3,
        ]);
        
        foreach ($categories as $cat) {
            $suggestions[] = [
                'type' => 'category',
                'title' => $cat->name,
                'url' => get_category_link($cat->term_id),
            ];
        }
        
        // Suggerimenti da tag
        $tags = get_terms([
            'taxonomy' => 'post_tag',
            'search' => $query,
            'number' => 3,
        ]);
        
        foreach ($tags as $tag) {
            $suggestions[] = [
                'type' => 'tag',
                'title' => $tag->name,
                'url' => get_tag_link($tag->term_id),
            ];
        }
        
        wp_send_json_success($suggestions);
    }
    
    /**
     * Migliora query di ricerca
     */
    public static function enhanceSearchQuery(string $search, \WP_Query $query): string
    {
        if (!$query->is_search() || empty($query->query_vars['s'])) {
            return $search;
        }
        
        global $wpdb;
        
        $search_term = $query->query_vars['s'];
        $search_term = $wpdb->esc_like($search_term);
        $search_term = '%' . $search_term . '%';
        
        // Cerca anche in excerpt e meta
        $search = $wpdb->prepare(
            " AND (
                ({$wpdb->posts}.post_title LIKE %s) OR
                ({$wpdb->posts}.post_content LIKE %s) OR
                ({$wpdb->posts}.post_excerpt LIKE %s) OR
                EXISTS (
                    SELECT 1 FROM {$wpdb->postmeta}
                    WHERE {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID
                    AND {$wpdb->postmeta}.meta_value LIKE %s
                )
            )",
            $search_term,
            $search_term,
            $search_term,
            $search_term
        );
        
        return $search;
    }
    
    /**
     * Renderizza search form avanzato
     */
    public static function renderSearchForm(array $args = []): string
    {
        $defaults = [
            'placeholder' => 'Cerca...',
            'show_suggestions' => true,
            'ajax_url' => admin_url('admin-ajax.php'),
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $html = '<div class="revious-advanced-search">';
        $html .= '<form role="search" method="get" action="' . esc_url(home_url('/')) . '">';
        $html .= '<input type="search" name="s" class="revious-search-input" placeholder="' . esc_attr($args['placeholder']) . '" autocomplete="off">';
        $html .= '<button type="submit">🔍</button>';
        $html .= '</form>';
        
        if ($args['show_suggestions']) {
            $html .= '<div class="revious-search-suggestions"></div>';
        }
        
        $html .= '</div>';
        
        if ($args['show_suggestions']) {
            $html .= self::getSuggestionsScript($args['ajax_url']);
        }
        
        return $html;
    }
    
    /**
     * Script per suggerimenti
     */
    private static function getSuggestionsScript(string $ajax_url): string
    {
        return '<script>
        (function() {
            const input = document.querySelector(".revious-search-input");
            const suggestions = document.querySelector(".revious-search-suggestions");
            let timeout;
            
            if (!input || !suggestions) return;
            
            input.addEventListener("input", function() {
                clearTimeout(timeout);
                const query = this.value.trim();
                
                if (query.length < 2) {
                    suggestions.innerHTML = "";
                    return;
                }
                
                timeout = setTimeout(function() {
                    fetch("' . esc_url($ajax_url) . '?action=revious_search_suggestions&q=" + encodeURIComponent(query))
                        .then(r => r.json())
                        .then(data => {
                            if (data.success && data.data.length) {
                                suggestions.innerHTML = data.data.map(item => 
                                    `<a href="${item.url}">${item.title}</a>`
                                ).join("");
                            } else {
                                suggestions.innerHTML = "";
                            }
                        });
                }, 300);
            });
        })();
        </script>';
    }
}
