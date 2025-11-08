<?php
namespace gik25microdata\REST;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * REST API per MCP Server
 * Espone dati WordPress in formato JSON per interrogazione via MCP
 */
class MCPApi
{
    private const NAMESPACE = 'td-mcp/v1';
    private const CACHE_GROUP = 'td_mcp';
    private const CACHE_EXPIRATION = 3600; // 1 ora

    public static function init(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    /**
     * Registra le route REST API
     */
    public static function register_routes(): void
    {
        // Lista categorie
        register_rest_route(self::NAMESPACE, '/categories', [
            'methods' => 'GET',
            'callback' => [self::class, 'get_categories'],
            'permission_callback' => '__return_true',
        ]);

        // Post per categoria
        register_rest_route(self::NAMESPACE, '/posts/category/(?P<slug>[a-zA-Z0-9-]+)', [
            'methods' => 'GET',
            'callback' => [self::class, 'get_posts_by_category'],
            'permission_callback' => '__return_true',
            'args' => [
                'slug' => [
                    'required' => true,
                    'type' => 'string',
                ],
                'limit' => [
                    'default' => 10,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ],
                'offset' => [
                    'default' => 0,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);

        // Ricerca post
        register_rest_route(self::NAMESPACE, '/posts/search', [
            'methods' => 'GET',
            'callback' => [self::class, 'search_posts'],
            'permission_callback' => '__return_true',
            'args' => [
                'q' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'limit' => [
                    'default' => 20,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);

        // Post per colore
        register_rest_route(self::NAMESPACE, '/posts/color/(?P<color>[a-zA-Z0-9-]+)', [
            'methods' => 'GET',
            'callback' => [self::class, 'get_posts_by_color'],
            'permission_callback' => '__return_true',
            'args' => [
                'color' => [
                    'required' => true,
                    'type' => 'string',
                ],
                'limit' => [
                    'default' => 15,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);

        // Post per linea IKEA
        register_rest_route(self::NAMESPACE, '/posts/ikea/(?P<line>[a-zA-Z0-9-]+)', [
            'methods' => 'GET',
            'callback' => [self::class, 'get_posts_by_ikea_line'],
            'permission_callback' => '__return_true',
            'args' => [
                'line' => [
                    'required' => true,
                    'type' => 'string',
                ],
                'limit' => [
                    'default' => 15,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);

        // Post per stanza
        register_rest_route(self::NAMESPACE, '/posts/room/(?P<room>[a-zA-Z0-9-]+)', [
            'methods' => 'GET',
            'callback' => [self::class, 'get_posts_by_room'],
            'permission_callback' => '__return_true',
            'args' => [
                'room' => [
                    'required' => true,
                    'type' => 'string',
                ],
                'limit' => [
                    'default' => 15,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);

        // Post Pantone
        register_rest_route(self::NAMESPACE, '/posts/pantone', [
            'methods' => 'GET',
            'callback' => [self::class, 'get_pantone_posts'],
            'permission_callback' => '__return_true',
            'args' => [
                'limit' => [
                    'default' => 20,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);

        // Post popolari
        register_rest_route(self::NAMESPACE, '/posts/popular', [
            'methods' => 'GET',
            'callback' => [self::class, 'get_popular_posts'],
            'permission_callback' => '__return_true',
            'args' => [
                'limit' => [
                    'default' => 20,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);

        // Post recenti
        register_rest_route(self::NAMESPACE, '/posts/recent', [
            'methods' => 'GET',
            'callback' => [self::class, 'get_recent_posts'],
            'permission_callback' => '__return_true',
            'args' => [
                'limit' => [
                    'default' => 20,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);

        // Ottieni post completo (con contenuto)
        register_rest_route(self::NAMESPACE, '/posts/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [self::class, 'get_post_full'],
            'permission_callback' => '__return_true',
            'args' => [
                'id' => [
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);

        // Analizza contenuti e suggerisci widget
        register_rest_route(self::NAMESPACE, '/analyze/widget-suggestions', [
            'methods' => 'POST',
            'callback' => [self::class, 'analyze_widget_suggestions'],
            'permission_callback' => '__return_true',
            'args' => [
                'post_ids' => [
                    'required' => false,
                    'type' => 'array',
                    'items' => ['type' => 'integer'],
                ],
                'category_slug' => [
                    'required' => false,
                    'type' => 'string',
                ],
                'limit' => [
                    'default' => 50,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);

        // Analizza pattern in contenuti
        register_rest_route(self::NAMESPACE, '/analyze/patterns', [
            'methods' => 'POST',
            'callback' => [self::class, 'analyze_patterns'],
            'permission_callback' => '__return_true',
            'args' => [
                'category_slug' => [
                    'required' => false,
                    'type' => 'string',
                ],
                'limit' => [
                    'default' => 100,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);

        // Lista tag
        register_rest_route(self::NAMESPACE, '/tags', [
            'methods' => 'GET',
            'callback' => [self::class, 'get_tags'],
            'permission_callback' => '__return_true',
            'args' => [
                'search' => [
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'limit' => [
                    'default' => 100,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);

        // Ottieni tag di un post
        register_rest_route(self::NAMESPACE, '/posts/(?P<id>\d+)/tags', [
            'methods' => 'GET',
            'callback' => [self::class, 'get_post_tags'],
            'permission_callback' => '__return_true',
            'args' => [
                'id' => [
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);
    }

    /**
     * Lista tutte le categorie
     */
    public static function get_categories(\WP_REST_Request $request): \WP_REST_Response
    {
        $cache_key = 'categories_list';
        $cached = wp_cache_get($cache_key, self::CACHE_GROUP);
        
        if ($cached !== false) {
            return new \WP_REST_Response($cached, 200);
        }

        $categories = get_categories([
            'hide_empty' => true,
            'orderby' => 'count',
            'order' => 'DESC',
        ]);

        $result = array_map(function ($cat) {
            return [
                'id' => $cat->term_id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'count' => $cat->count,
                'url' => get_category_link($cat->term_id),
            ];
        }, $categories);

        wp_cache_set($cache_key, $result, self::CACHE_GROUP, self::CACHE_EXPIRATION);

        return new \WP_REST_Response($result, 200);
    }

    /**
     * Post per categoria
     */
    public static function get_posts_by_category(\WP_REST_Request $request): \WP_REST_Response
    {
        $slug = $request->get_param('slug');
        $limit = $request->get_param('limit');
        $offset = $request->get_param('offset');

        $cache_key = "posts_category_{$slug}_{$limit}_{$offset}";
        $cached = wp_cache_get($cache_key, self::CACHE_GROUP);
        
        if ($cached !== false) {
            return new \WP_REST_Response($cached, 200);
        }

        $query = new \WP_Query([
            'category_name' => $slug,
            'posts_per_page' => $limit,
            'offset' => $offset,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        $result = self::format_posts($query->posts);

        wp_cache_set($cache_key, $result, self::CACHE_GROUP, self::CACHE_EXPIRATION);

        return new \WP_REST_Response($result, 200);
    }

    /**
     * Ricerca post
     */
    public static function search_posts(\WP_REST_Request $request): \WP_REST_Response
    {
        $query = $request->get_param('q');
        $limit = $request->get_param('limit');

        $cache_key = "search_{$query}_{$limit}";
        $cached = wp_cache_get($cache_key, self::CACHE_GROUP);
        
        if ($cached !== false) {
            return new \WP_REST_Response($cached, 200);
        }

        $wp_query = new \WP_Query([
            's' => $query,
            'posts_per_page' => $limit,
            'post_status' => 'publish',
            'orderby' => 'relevance',
        ]);

        $result = self::format_posts($wp_query->posts);

        wp_cache_set($cache_key, $result, self::CACHE_GROUP, self::CACHE_EXPIRATION);

        return new \WP_REST_Response($result, 200);
    }

    /**
     * Post per colore
     */
    public static function get_posts_by_color(\WP_REST_Request $request): \WP_REST_Response
    {
        $color = $request->get_param('color');
        $limit = $request->get_param('limit');

        $cache_key = "posts_color_{$color}_{$limit}";
        $cached = wp_cache_get($cache_key, self::CACHE_GROUP);
        
        if ($cached !== false) {
            return new \WP_REST_Response($cached, 200);
        }

        // Cerca per slug o titolo contenente il colore
        $wp_query = new \WP_Query([
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_query' => [
                'relation' => 'OR',
            ],
            'tax_query' => [],
        ]);

        // Filtra manualmente per slug/titolo contenente il colore
        $all_posts = get_posts([
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        $filtered = array_filter($all_posts, function ($post) use ($color) {
            $color_lower = strtolower($color);
            $slug = strtolower($post->post_name);
            $title = strtolower($post->post_title);
            return strpos($slug, $color_lower) !== false || strpos($title, $color_lower) !== false;
        });

        $result = self::format_posts(array_slice($filtered, 0, $limit));

        wp_cache_set($cache_key, $result, self::CACHE_GROUP, self::CACHE_EXPIRATION);

        return new \WP_REST_Response($result, 200);
    }

    /**
     * Post per linea IKEA
     */
    public static function get_posts_by_ikea_line(\WP_REST_Request $request): \WP_REST_Response
    {
        $line = $request->get_param('line');
        $limit = $request->get_param('limit');

        $cache_key = "posts_ikea_{$line}_{$limit}";
        $cached = wp_cache_get($cache_key, self::CACHE_GROUP);
        
        if ($cached !== false) {
            return new \WP_REST_Response($cached, 200);
        }

        $wp_query = new \WP_Query([
            's' => "ikea {$line}",
            'posts_per_page' => $limit,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        $result = self::format_posts($wp_query->posts);

        wp_cache_set($cache_key, $result, self::CACHE_GROUP, self::CACHE_EXPIRATION);

        return new \WP_REST_Response($result, 200);
    }

    /**
     * Post per stanza
     */
    public static function get_posts_by_room(\WP_REST_Request $request): \WP_REST_Response
    {
        $room = $request->get_param('room');
        $limit = $request->get_param('limit');

        $cache_key = "posts_room_{$room}_{$limit}";
        $cached = wp_cache_get($cache_key, self::CACHE_GROUP);
        
        if ($cached !== false) {
            return new \WP_REST_Response($cached, 200);
        }

        $wp_query = new \WP_Query([
            's' => $room,
            'posts_per_page' => $limit,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        $result = self::format_posts($wp_query->posts);

        wp_cache_set($cache_key, $result, self::CACHE_GROUP, self::CACHE_EXPIRATION);

        return new \WP_REST_Response($result, 200);
    }

    /**
     * Post Pantone
     */
    public static function get_pantone_posts(\WP_REST_Request $request): \WP_REST_Response
    {
        $limit = $request->get_param('limit');

        $cache_key = "posts_pantone_{$limit}";
        $cached = wp_cache_get($cache_key, self::CACHE_GROUP);
        
        if ($cached !== false) {
            return new \WP_REST_Response($cached, 200);
        }

        $wp_query = new \WP_Query([
            's' => 'pantone',
            'posts_per_page' => $limit,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        $result = self::format_posts($wp_query->posts);

        wp_cache_set($cache_key, $result, self::CACHE_GROUP, self::CACHE_EXPIRATION);

        return new \WP_REST_Response($result, 200);
    }

    /**
     * Post popolari (per commenti)
     */
    public static function get_popular_posts(\WP_REST_Request $request): \WP_REST_Response
    {
        $limit = $request->get_param('limit');

        $cache_key = "posts_popular_{$limit}";
        $cached = wp_cache_get($cache_key, self::CACHE_GROUP);
        
        if ($cached !== false) {
            return new \WP_REST_Response($cached, 200);
        }

        $wp_query = new \WP_Query([
            'posts_per_page' => $limit,
            'post_status' => 'publish',
            'orderby' => 'comment_count',
            'order' => 'DESC',
            'date_query' => [
                [
                    'after' => '1 year ago',
                ],
            ],
        ]);

        $result = self::format_posts($wp_query->posts);

        wp_cache_set($cache_key, $result, self::CACHE_GROUP, self::CACHE_EXPIRATION);

        return new \WP_REST_Response($result, 200);
    }

    /**
     * Post recenti
     */
    public static function get_recent_posts(\WP_REST_Request $request): \WP_REST_Response
    {
        $limit = $request->get_param('limit');

        $cache_key = "posts_recent_{$limit}";
        $cached = wp_cache_get($cache_key, self::CACHE_GROUP);
        
        if ($cached !== false) {
            return new \WP_REST_Response($cached, 200);
        }

        $wp_query = new \WP_Query([
            'posts_per_page' => $limit,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        $result = self::format_posts($wp_query->posts);

        wp_cache_set($cache_key, $result, self::CACHE_GROUP, self::CACHE_EXPIRATION);

        return new \WP_REST_Response($result, 200);
    }

    /**
     * Ottieni post completo con contenuto
     */
    public static function get_post_full(\WP_REST_Request $request): \WP_REST_Response
    {
        $post_id = $request->get_param('id');
        
        $cache_key = "post_full_{$post_id}";
        $cached = wp_cache_get($cache_key, self::CACHE_GROUP);
        
        if ($cached !== false) {
            return new \WP_REST_Response($cached, 200);
        }

        $post = get_post($post_id);
        if (!$post || $post->post_status !== 'publish') {
            return new \WP_REST_Response(['error' => 'Post not found'], 404);
        }

        $thumbnail_id = get_post_thumbnail_id($post->ID);
        $thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'large') : null;
        $categories = wp_get_post_categories($post->ID, ['fields' => 'all']);
        $tags = wp_get_post_tags($post->ID, ['fields' => 'all']);

        $result = [
            'id' => $post->ID,
            'title' => $post->post_title,
            'slug' => $post->post_name,
            'url' => get_permalink($post->ID),
            'content' => $post->post_content,
            'excerpt' => $post->post_excerpt ?: wp_trim_words($post->post_content, 55),
            'date' => $post->post_date,
            'date_formatted' => get_the_date('', $post->ID),
            'modified' => $post->post_modified,
            'thumbnail' => $thumbnail_url,
            'comment_count' => (int) $post->comment_count,
            'categories' => array_map(function ($cat) {
                return [
                    'id' => $cat->term_id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                ];
            }, $categories),
            'tags' => array_map(function ($tag) {
                return [
                    'id' => $tag->term_id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                ];
            }, $tags),
            'word_count' => str_word_count(strip_tags($post->post_content)),
        ];

        wp_cache_set($cache_key, $result, self::CACHE_GROUP, self::CACHE_EXPIRATION);
        
        return new \WP_REST_Response($result, 200);
    }

    /**
     * Analizza contenuti e suggerisci widget
     */
    public static function analyze_widget_suggestions(\WP_REST_Request $request): \WP_REST_Response
    {
        $post_ids = $request->get_param('post_ids');
        $category_slug = $request->get_param('category_slug');
        $limit = $request->get_param('limit');

        // Ottieni post da analizzare
        $posts = [];
        if ($post_ids && is_array($post_ids)) {
            $posts = get_posts([
                'post__in' => $post_ids,
                'post_status' => 'publish',
                'posts_per_page' => -1,
            ]);
        } elseif ($category_slug) {
            $posts = get_posts([
                'category_name' => $category_slug,
                'post_status' => 'publish',
                'posts_per_page' => $limit,
                'orderby' => 'date',
                'order' => 'DESC',
            ]);
        } else {
            $posts = get_posts([
                'post_status' => 'publish',
                'posts_per_page' => $limit,
                'orderby' => 'date',
                'order' => 'DESC',
            ]);
        }

        // Pattern di widget
        $widget_patterns = [
            'kitchen_finder' => [
                'keywords' => ['cucina', 'cucine', 'ikea cucina', 'metod', 'enhet', 'sunnersta', 'knoxhult', 'cucina ikea'],
                'widget' => 'kitchen_finder',
                'description' => 'Widget per trovare la cucina perfetta',
                'shortcode' => '[kitchen_finder title="Trova la cucina perfetta per te" show_progress="true"]',
            ],
            'palette_correlate' => [
                'keywords' => ['colori', 'colore', 'palette', 'abbinamenti', 'pantone', 'tinte', 'tinture'],
                'widget' => 'palette_correlate',
                'description' => 'Widget per palette di colori correlate',
                'shortcode' => '[td_palette_correlate]',
            ],
            'ikea_line' => [
                'keywords' => ['ikea', 'metod', 'enhet', 'billy', 'pax', 'kallax', 'hemnes'],
                'widget' => 'ikea_line',
                'description' => 'Widget per linee IKEA',
                'shortcode' => '[td_ikea_line line="metod"]',
            ],
            'room_related' => [
                'keywords' => ['cucina', 'soggiorno', 'camera', 'bagno', 'camera da letto', 'salotto'],
                'widget' => 'room_related',
                'description' => 'Widget per articoli correlati per stanza',
                'shortcode' => '[td_room_related room="cucina"]',
            ],
        ];

        // Analizza contenuti
        $analysis = [
            'total_posts' => count($posts),
            'widget_suggestions' => [],
            'pattern_counts' => [],
        ];

        foreach ($widget_patterns as $widget_key => $pattern) {
            $matches = 0;
            $matched_posts = [];

            foreach ($posts as $post) {
                $content = strtolower($post->post_title . ' ' . wp_strip_all_tags($post->post_content));
                $matched_keywords = [];

                foreach ($pattern['keywords'] as $keyword) {
                    if (str_contains($content, strtolower($keyword))) {
                        $matched_keywords[] = $keyword;
                    }
                }

                if (!empty($matched_keywords)) {
                    $matches++;
                    $matched_posts[] = [
                        'id' => $post->ID,
                        'title' => $post->post_title,
                        'url' => get_permalink($post->ID),
                        'matched_keywords' => $matched_keywords,
                    ];
                }
            }

            if ($matches > 0) {
                $percentage = round(($matches / count($posts)) * 100, 1);
                $analysis['widget_suggestions'][] = [
                    'widget' => $pattern['widget'],
                    'description' => $pattern['description'],
                    'shortcode' => $pattern['shortcode'],
                    'matches' => $matches,
                    'percentage' => $percentage,
                    'matched_posts' => array_slice($matched_posts, 0, 10), // Prime 10
                ];
                $analysis['pattern_counts'][$widget_key] = $matches;
            }
        }

        // Ordina per percentuale di match
        usort($analysis['widget_suggestions'], function ($a, $b) {
            return $b['percentage'] <=> $a['percentage'];
        });

        return new \WP_REST_Response($analysis, 200);
    }

    /**
     * Analizza pattern in contenuti
     */
    public static function analyze_patterns(\WP_REST_Request $request): \WP_REST_Response
    {
        $category_slug = $request->get_param('category_slug');
        $limit = $request->get_param('limit');

        $query_args = [
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'orderby' => 'date',
            'order' => 'DESC',
        ];

        if ($category_slug) {
            $query_args['category_name'] = $category_slug;
        }

        $posts = get_posts($query_args);

        // Pattern comuni
        $patterns = [
            'kitchen_keywords' => ['cucina', 'cucine', 'fornelli', 'lavello', 'piano cottura'],
            'color_keywords' => ['colore', 'colori', 'tinta', 'tinte', 'pantone', 'palette'],
            'ikea_keywords' => ['ikea', 'metod', 'enhet', 'billy', 'pax', 'kallax'],
            'room_keywords' => ['cucina', 'soggiorno', 'camera', 'bagno', 'salotto', 'camino'],
        ];

        $analysis = [
            'total_posts' => count($posts),
            'patterns' => [],
        ];

        foreach ($patterns as $pattern_key => $keywords) {
            $matches = 0;
            foreach ($posts as $post) {
                $content = strtolower($post->post_title . ' ' . wp_strip_all_tags($post->post_content));
                foreach ($keywords as $keyword) {
                    if (str_contains($content, $keyword)) {
                        $matches++;
                        break;
                    }
                }
            }
            $analysis['patterns'][$pattern_key] = [
                'matches' => $matches,
                'percentage' => count($posts) > 0 ? round(($matches / count($posts)) * 100, 1) : 0,
                'keywords' => $keywords,
            ];
        }

        return new \WP_REST_Response($analysis, 200);
    }

    /**
     * Lista tag
     */
    public static function get_tags(\WP_REST_Request $request): \WP_REST_Response
    {
        $search = $request->get_param('search');
        $limit = $request->get_param('limit');

        $cache_key = "tags_list_{$search}_{$limit}";
        $cached = wp_cache_get($cache_key, self::CACHE_GROUP);
        
        if ($cached !== false) {
            return new \WP_REST_Response($cached, 200);
        }

        $args = [
            'hide_empty' => false,
            'number' => $limit,
            'orderby' => 'count',
            'order' => 'DESC',
        ];

        if ($search) {
            $args['search'] = $search;
        }

        $tags = get_terms([
            'taxonomy' => 'post_tag',
            'hide_empty' => false,
            'number' => $limit,
            'orderby' => 'count',
            'order' => 'DESC',
            'search' => $search ?: '',
        ]);

        if (is_wp_error($tags)) {
            return new \WP_REST_Response(['error' => $tags->get_error_message()], 400);
        }

        $result = array_map(function ($tag) {
            return [
                'id' => $tag->term_id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'count' => $tag->count,
                'url' => get_tag_link($tag->term_id),
            ];
        }, $tags);

        wp_cache_set($cache_key, $result, self::CACHE_GROUP, self::CACHE_EXPIRATION);

        return new \WP_REST_Response($result, 200);
    }

    /**
     * Ottieni tag di un post
     */
    public static function get_post_tags(\WP_REST_Request $request): \WP_REST_Response
    {
        $post_id = $request->get_param('id');

        $cache_key = "post_tags_{$post_id}";
        $cached = wp_cache_get($cache_key, self::CACHE_GROUP);
        
        if ($cached !== false) {
            return new \WP_REST_Response($cached, 200);
        }

        $post = get_post($post_id);
        if (!$post) {
            return new \WP_REST_Response(['error' => 'Post not found'], 404);
        }

        $tags = wp_get_post_tags($post_id, ['fields' => 'all']);

        $result = array_map(function ($tag) {
            return [
                'id' => $tag->term_id,
                'name' => $tag->name,
                'slug' => $tag->slug,
            ];
        }, $tags);

        wp_cache_set($cache_key, $result, self::CACHE_GROUP, self::CACHE_EXPIRATION);

        return new \WP_REST_Response($result, 200);
    }

    /**
     * Formatta array di post in formato standardizzato
     */
    private static function format_posts(array $posts): array
    {
        return array_map(function ($post) {
            $thumbnail_id = get_post_thumbnail_id($post->ID);
            $thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'medium') : null;

            return [
                'id' => $post->ID,
                'title' => $post->post_title,
                'slug' => $post->post_name,
                'url' => get_permalink($post->ID),
                'excerpt' => wp_trim_words($post->post_excerpt ?: $post->post_content, 30),
                'date' => $post->post_date,
                'date_formatted' => get_the_date('', $post->ID),
                'thumbnail' => $thumbnail_url,
                'comment_count' => (int) $post->comment_count,
            ];
        }, $posts);
    }
}

