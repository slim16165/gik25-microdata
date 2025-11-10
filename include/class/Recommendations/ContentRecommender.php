<?php
namespace gik25microdata\Recommendations;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Content Recommendations Engine
 * 
 * Sistema raccomandazioni basato su ML e analisi contenuto
 */
class ContentRecommender
{
    /**
     * Ottiene post correlati intelligenti
     */
    public static function getRelatedPosts(int $post_id, int $limit = 5): array
    {
        $post = get_post($post_id);
        if (!$post) {
            return [];
        }
        
        // Strategia multi-fattore
        $scores = [];
        
        // 1. Stesso autore
        $author_posts = get_posts([
            'author' => $post->post_author,
            'post__not_in' => [$post_id],
            'posts_per_page' => 20,
            'fields' => 'ids',
        ]);
        
        foreach ($author_posts as $related_id) {
            $scores[$related_id] = ($scores[$related_id] ?? 0) + 3;
        }
        
        // 2. Stesse categorie
        $categories = wp_get_post_categories($post_id);
        if (!empty($categories)) {
            $category_posts = get_posts([
                'category__in' => $categories,
                'post__not_in' => [$post_id],
                'posts_per_page' => 20,
                'fields' => 'ids',
            ]);
            
            foreach ($category_posts as $related_id) {
                $scores[$related_id] = ($scores[$related_id] ?? 0) + 5;
            }
        }
        
        // 3. Stessi tag
        $tags = wp_get_post_tags($post_id, ['fields' => 'ids']);
        if (!empty($tags)) {
            $tag_posts = get_posts([
                'tag__in' => $tags,
                'post__not_in' => [$post_id],
                'posts_per_page' => 20,
                'fields' => 'ids',
            ]);
            
            foreach ($tag_posts as $related_id) {
                $scores[$related_id] = ($scores[$related_id] ?? 0) + 4;
            }
        }
        
        // 4. Similarità titolo (parole chiave comuni)
        $title_words = array_filter(explode(' ', strtolower($post->post_title)));
        $all_posts = get_posts([
            'post__not_in' => [$post_id],
            'posts_per_page' => 50,
            'fields' => 'ids',
        ]);
        
        foreach ($all_posts as $related_id) {
            $related_post = get_post($related_id);
            $related_words = array_filter(explode(' ', strtolower($related_post->post_title)));
            $common_words = count(array_intersect($title_words, $related_words));
            
            if ($common_words > 0) {
                $scores[$related_id] = ($scores[$related_id] ?? 0) + ($common_words * 2);
            }
        }
        
        // 5. Post recenti (bonus)
        $recent_posts = get_posts([
            'post__not_in' => [$post_id],
            'posts_per_page' => 10,
            'orderby' => 'date',
            'order' => 'DESC',
            'fields' => 'ids',
        ]);
        
        foreach ($recent_posts as $related_id) {
            $scores[$related_id] = ($scores[$related_id] ?? 0) + 1;
        }
        
        // Ordina per score
        arsort($scores);
        
        // Prendi top N
        $top_ids = array_slice(array_keys($scores), 0, $limit);
        
        return array_map('get_post', $top_ids);
    }
    
    /**
     * Ottiene trending content
     */
    public static function getTrendingPosts(int $days = 7, int $limit = 10): array
    {
        global $wpdb;
        
        // Usa analytics se disponibile
        $analytics_table = $wpdb->prefix . 'revious_analytics_events';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '{$analytics_table}'") === $analytics_table) {
            $trending = $wpdb->get_results($wpdb->prepare(
                "SELECT post_id, COUNT(*) as views
                FROM {$analytics_table}
                WHERE event_type = 'page' AND event_name = 'view'
                AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
                AND post_id IS NOT NULL
                GROUP BY post_id
                ORDER BY views DESC
                LIMIT %d",
                $days,
                $limit
            ), ARRAY_A);
            
            if (!empty($trending)) {
                $post_ids = array_column($trending, 'post_id');
                return array_map('get_post', $post_ids);
            }
        }
        
        // Fallback: post più commentati
        return get_posts([
            'posts_per_page' => $limit,
            'orderby' => 'comment_count',
            'order' => 'DESC',
            'date_query' => [
                [
                    'after' => "{$days} days ago",
                ],
            ],
        ]);
    }
    
    /**
     * Calcola content performance score
     */
    public static function getPerformanceScore(int $post_id): float
    {
        $post = get_post($post_id);
        if (!$post) {
            return 0.0;
        }
        
        $score = 0.0;
        
        // Views (se analytics disponibile)
        global $wpdb;
        $analytics_table = $wpdb->prefix . 'revious_analytics_events';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '{$analytics_table}'") === $analytics_table) {
            $views = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$analytics_table}
                WHERE post_id = %d AND event_type = 'page'",
                $post_id
            ));
            $score += min($views / 100, 30); // Max 30 punti
        }
        
        // Comments
        $comments = get_comments_number($post_id);
        $score += min($comments / 10, 20); // Max 20 punti
        
        // Social shares (se disponibile)
        $shares = get_post_meta($post_id, '_revious_social_shares', true) ?: 0;
        $score += min($shares / 50, 25); // Max 25 punti
        
        // Time on page (se analytics disponibile)
        if ($wpdb->get_var("SHOW TABLES LIKE '{$analytics_table}'") === $analytics_table) {
            $avg_time = $wpdb->get_var($wpdb->prepare(
                "SELECT AVG(CAST(JSON_EXTRACT(metadata, '$.seconds') AS UNSIGNED))
                FROM {$analytics_table}
                WHERE post_id = %d AND event_name = 'time_on_page'",
                $post_id
            ));
            if ($avg_time) {
                $score += min($avg_time / 60, 25); // Max 25 punti (1 min = 1 punto)
            }
        }
        
        return round($score, 2);
    }
}
