<?php
namespace gik25microdata\Admin;

use gik25microdata\Shortcodes\ShortcodeRegistry;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Pagina admin per visualizzare l'utilizzo di tutti gli shortcode
 * Scansiona tutti i contenuti e mostra una griglia con gli shortcode utilizzati
 */
class ShortcodesUsagePage
{
    private const CAPABILITY = 'manage_options';
    private const CACHE_GROUP = 'gik25_shortcode_usage';
    private const CACHE_EXPIRATION = 3600; // 1 ora

    /**
     * Renderizza la pagina
     */
    public static function renderPage(): void
    {
        if (!current_user_can(self::CAPABILITY)) {
            wp_die(__('Non hai i permessi per visualizzare questa pagina.', 'gik25-microdata'));
        }

        // Gestisci refresh cache
        $refresh_cache = isset($_GET['refresh_cache']) && $_GET['refresh_cache'] === '1';
        if ($refresh_cache && check_admin_referer('refresh_shortcode_usage_cache')) {
            self::clear_cache();
        }

        // Ottieni dati dalla cache o scansiona
        $usage_data = self::get_usage_data($refresh_cache);
        
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Utilizzo Shortcode', 'gik25-microdata'); ?></h1>
            
            <div class="shortcode-usage-header" style="margin: 20px 0; padding: 15px; background: #fff; border: 1px solid #c3c4c7; border-radius: 4px;">
                <p><?php esc_html_e('Panoramica completa degli shortcode utilizzati nel sito. I dati vengono scansionati automaticamente e memorizzati in cache per migliorare le performance.', 'gik25-microdata'); ?></p>
                <p>
                    <a href="<?php echo esc_url(wp_nonce_url(add_query_arg(['refresh_cache' => '1']), 'refresh_shortcode_usage_cache')); ?>" class="button">
                        <?php esc_html_e('🔄 Aggiorna Scansione', 'gik25-microdata'); ?>
                    </a>
                    <span class="description" style="margin-left: 10px;">
                        <?php esc_html_e('Ultima scansione:', 'gik25-microdata'); ?>
                        <strong><?php echo esc_html($usage_data['scan_time'] ?? 'Mai'); ?></strong>
                    </span>
                </p>
            </div>

            <?php if (empty($usage_data['shortcodes'])) : ?>
                <div class="notice notice-info">
                    <p><?php esc_html_e('Nessuno shortcode trovato nei contenuti.', 'gik25-microdata'); ?></p>
                </div>
            <?php else : ?>
                <!-- Statistiche generali -->
                <div class="shortcode-usage-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0;">
                    <div class="stat-card" style="padding: 15px; background: #fff; border: 1px solid #c3c4c7; border-radius: 4px;">
                        <div style="font-size: 24px; font-weight: bold; color: #2271b1;"><?php echo count($usage_data['shortcodes']); ?></div>
                        <div style="color: #646970; font-size: 13px;">Shortcode Unici</div>
                    </div>
                    <div class="stat-card" style="padding: 15px; background: #fff; border: 1px solid #c3c4c7; border-radius: 4px;">
                        <div style="font-size: 24px; font-weight: bold; color: #2271b1;"><?php echo $usage_data['total_posts'] ?? 0; ?></div>
                        <div style="color: #646970; font-size: 13px;">Contenuti Totali</div>
                    </div>
                    <div class="stat-card" style="padding: 15px; background: #fff; border: 1px solid #c3c4c7; border-radius: 4px;">
                        <div style="font-size: 24px; font-weight: bold; color: #2271b1;"><?php echo $usage_data['total_occurrences'] ?? 0; ?></div>
                        <div style="color: #646970; font-size: 13px;">Occorrenze Totali</div>
                    </div>
                </div>

                <!-- Griglia shortcode -->
                <div class="shortcode-usage-grid" id="shortcode-usage-grid">
                    <?php foreach ($usage_data['shortcodes'] as $shortcode => $data) : ?>
                        <?php self::render_shortcode_card($shortcode, $data); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php
        self::render_styles();
        self::render_scripts();
    }

    /**
     * Renderizza una card shortcode
     */
    private static function render_shortcode_card(string $shortcode, array $data): void
    {
        $label = ShortcodeRegistry::getLabel($shortcode) ?? $shortcode;
        $posts_count = count($data['posts']);
        $total_occurrences = $data['total_occurrences'];
        $category = self::get_shortcode_category($shortcode);
        
        ?>
        <div class="shortcode-usage-card" data-category="<?php echo esc_attr($category); ?>" data-shortcode="<?php echo esc_attr($shortcode); ?>">
            <div class="shortcode-card-header">
                <h3 class="shortcode-card-title">
                    <code><?php echo esc_html($shortcode); ?></code>
                    <span class="shortcode-card-label"><?php echo esc_html($label); ?></span>
                </h3>
                <span class="shortcode-card-category"><?php echo esc_html(self::get_category_label($category)); ?></span>
            </div>
            
            <div class="shortcode-card-stats">
                <div class="stat-item">
                    <span class="stat-value"><?php echo esc_html($posts_count); ?></span>
                    <span class="stat-label">Contenuti</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?php echo esc_html($total_occurrences); ?></span>
                    <span class="stat-label">Occorrenze</span>
                </div>
            </div>
            
            <div class="shortcode-card-posts">
                <strong>Utilizzato in:</strong>
                <ul class="post-list">
                    <?php 
                    // Mostra max 5 post, poi link "vedi tutti"
                    $posts_to_show = array_slice($data['posts'], 0, 5);
                    foreach ($posts_to_show as $post) : 
                    ?>
                        <li>
                            <a href="<?php echo esc_url(get_edit_post_link($post['ID'])); ?>" target="_blank">
                                <?php echo esc_html($post['post_title']); ?>
                            </a>
                            <span class="post-type-badge"><?php echo esc_html($post['post_type']); ?></span>
                            <span class="occurrences-count">(<?php echo esc_html($post['count']); ?>)</span>
                        </li>
                    <?php endforeach; ?>
                    
                    <?php if ($posts_count > 5) : ?>
                        <li class="see-all">
                            <a href="#" class="show-all-posts" data-shortcode="<?php echo esc_attr($shortcode); ?>">
                                Vedi tutti i <?php echo esc_html($posts_count); ?> contenuti →
                            </a>
                        </li>
                        <li class="all-posts" style="display: none;" data-shortcode="<?php echo esc_attr($shortcode); ?>">
                            <?php foreach (array_slice($data['posts'], 5) as $post) : ?>
                                <div style="margin: 5px 0;">
                                    <a href="<?php echo esc_url(get_edit_post_link($post['ID'])); ?>" target="_blank">
                                        <?php echo esc_html($post['post_title']); ?>
                                    </a>
                                    <span class="post-type-badge"><?php echo esc_html($post['post_type']); ?></span>
                                    <span class="occurrences-count">(<?php echo esc_html($post['count']); ?>)</span>
                                </div>
                            <?php endforeach; ?>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        <?php
    }

    /**
     * Ottiene i dati di utilizzo (da cache o scansiona)
     */
    private static function get_usage_data(bool $force_refresh = false): array
    {
        $cache_key = 'shortcode_usage_data';
        
        if (!$force_refresh) {
            $cached = wp_cache_get($cache_key, self::CACHE_GROUP);
            if ($cached !== false) {
                return $cached;
            }
        }
        
        // Scansiona tutti i contenuti
        $usage_data = self::scan_all_shortcodes();
        
        // Salva in cache
        wp_cache_set($cache_key, $usage_data, self::CACHE_GROUP, self::CACHE_EXPIRATION);
        
        return $usage_data;
    }

    /**
     * Scansiona tutti gli shortcode nel database
     */
    private static function scan_all_shortcodes(): array
    {
        global $wpdb;
        
        // Ottieni tutti gli shortcode registrati
        $registry = ShortcodeRegistry::getRegistry();
        $all_shortcodes = array_keys($registry);
        
        // Aggiungi anche shortcode registrati direttamente
        global $shortcode_tags;
        foreach ($shortcode_tags as $tag => $callback) {
            if (strpos($tag, 'md_') === 0 || in_array($tag, ['link_colori', 'grafica3d', 'archistar', 'kitchen_finder', 'app_nav', 'carousel', 'list', 'grid'])) {
                if (!in_array($tag, $all_shortcodes)) {
                    $all_shortcodes[] = $tag;
                }
            }
        }
        
        // Scansiona tutti i contenuti
        $posts = $wpdb->get_results(
            "SELECT ID, post_title, post_type, post_status, post_content 
             FROM {$wpdb->posts} 
             WHERE post_status NOT IN ('trash','auto-draft','inherit')
             AND post_content != ''
             ORDER BY post_modified_gmt DESC",
            ARRAY_A
        );
        
        $usage_data = [
            'shortcodes' => [],
            'total_posts' => count($posts),
            'total_occurrences' => 0,
            'scan_time' => current_time('mysql'),
        ];
        
        foreach ($all_shortcodes as $shortcode) {
            $usage_data['shortcodes'][$shortcode] = [
                'posts' => [],
                'total_occurrences' => 0,
            ];
        }
        
        foreach ($posts as $post) {
            $content = $post['post_content'];
            
            foreach ($all_shortcodes as $shortcode) {
                $count = ShortcodeRegistry::countOccurrences($shortcode, $content);
                
                if ($count > 0) {
                    $usage_data['shortcodes'][$shortcode]['posts'][] = [
                        'ID' => $post['ID'],
                        'post_title' => $post['post_title'],
                        'post_type' => $post['post_type'],
                        'post_status' => $post['post_status'],
                        'count' => $count,
                    ];
                    $usage_data['shortcodes'][$shortcode]['total_occurrences'] += $count;
                    $usage_data['total_occurrences'] += $count;
                }
            }
        }
        
        // Rimuovi shortcode non utilizzati e ordina per occorrenze
        $usage_data['shortcodes'] = array_filter($usage_data['shortcodes'], function($data) {
            return !empty($data['posts']);
        });
        
        // Ordina per occorrenze totali (decrescente)
        uasort($usage_data['shortcodes'], function($a, $b) {
            return $b['total_occurrences'] - $a['total_occurrences'];
        });
        
        return $usage_data;
    }

    /**
     * Pulisce la cache
     */
    private static function clear_cache(): void
    {
        wp_cache_delete('shortcode_usage_data', self::CACHE_GROUP);
    }

    /**
     * Ottiene categoria per shortcode
     */
    private static function get_shortcode_category(string $shortcode): string
    {
        $category_map = [
            'visual' => ['md_quote', 'md_boxinfo', 'md_flipbox', 'md_slidingbox', 'md_blinkingbutton', 'md_progressbar', 'md_perfectpullquote', 'md_prezzo', 'md_flexlist'],
            'functional' => ['md_telefono', 'md_youtube'],
            'totaldesign' => ['kitchen_finder', 'app_nav', 'carousel', 'list', 'grid', 'link_colori', 'grafica3d', 'archistar'],
            'other' => [],
        ];
        
        foreach ($category_map as $category => $shortcodes) {
            if (in_array($shortcode, $shortcodes)) {
                return $category;
            }
        }
        
        return 'other';
    }

    /**
     * Ottiene etichetta categoria
     */
    private static function get_category_label(string $category): string
    {
        $labels = [
            'visual' => '🎨 Grafici',
            'functional' => '⚙️ Funzionali',
            'totaldesign' => '🏠 TotalDesign',
            'other' => '📦 Altri',
        ];
        
        return $labels[$category] ?? 'Altri';
    }

    /**
     * Renderizza CSS
     */
    private static function render_styles(): void
    {
        ?>
        <style>
            .shortcode-usage-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
                gap: 20px;
                margin: 20px 0;
            }
            .shortcode-usage-card {
                background: #fff;
                border: 1px solid #dcdcde;
                border-radius: 8px;
                padding: 20px;
                transition: all 0.3s ease;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .shortcode-usage-card:hover {
                border-color: #2271b1;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                transform: translateY(-2px);
            }
            .shortcode-card-header {
                margin-bottom: 15px;
                padding-bottom: 15px;
                border-bottom: 1px solid #e5e5e5;
            }
            .shortcode-card-title {
                margin: 0 0 8px 0;
                font-size: 16px;
                font-weight: 600;
                color: #1d2327;
                display: flex;
                align-items: center;
                gap: 10px;
                flex-wrap: wrap;
            }
            .shortcode-card-title code {
                background: #f6f7f7;
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 13px;
                color: #2271b1;
                font-weight: 600;
            }
            .shortcode-card-label {
                color: #646970;
                font-weight: 500;
            }
            .shortcode-card-category {
                font-size: 11px;
                color: #646970;
                text-transform: uppercase;
                font-weight: 600;
                letter-spacing: 0.5px;
                padding: 4px 8px;
                background: #f6f7f7;
                border-radius: 4px;
                display: inline-block;
            }
            .shortcode-card-stats {
                display: flex;
                gap: 20px;
                margin: 15px 0;
                padding: 15px;
                background: #f6f7f7;
                border-radius: 4px;
            }
            .stat-item {
                flex: 1;
                text-align: center;
            }
            .stat-value {
                display: block;
                font-size: 24px;
                font-weight: bold;
                color: #2271b1;
            }
            .stat-label {
                display: block;
                font-size: 12px;
                color: #646970;
                margin-top: 5px;
            }
            .shortcode-card-posts {
                margin-top: 15px;
            }
            .shortcode-card-posts strong {
                display: block;
                margin-bottom: 10px;
                color: #1d2327;
                font-size: 13px;
            }
            .post-list {
                list-style: none;
                margin: 0;
                padding: 0;
            }
            .post-list li {
                margin: 8px 0;
                padding: 8px;
                background: #f9f9f9;
                border-radius: 4px;
                font-size: 13px;
            }
            .post-list li a {
                color: #2271b1;
                text-decoration: none;
            }
            .post-list li a:hover {
                text-decoration: underline;
            }
            .post-type-badge {
                display: inline-block;
                margin-left: 8px;
                padding: 2px 6px;
                background: #e5e5e5;
                border-radius: 3px;
                font-size: 11px;
                color: #646970;
            }
            .occurrences-count {
                margin-left: 8px;
                color: #646970;
                font-size: 12px;
            }
            .see-all {
                text-align: center;
                margin-top: 10px;
            }
            .see-all a {
                color: #2271b1;
                font-weight: 600;
                text-decoration: none;
            }
            .see-all a:hover {
                text-decoration: underline;
            }
            .all-posts {
                margin-top: 10px;
            }
            .all-posts div {
                padding: 5px;
                background: #f9f9f9;
                border-radius: 4px;
                margin: 5px 0;
            }
            
            @media (max-width: 782px) {
                .shortcode-usage-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>
        <?php
    }

    /**
     * Renderizza JavaScript
     */
    private static function render_scripts(): void
    {
        ?>
        <script>
        (function() {
            document.addEventListener('DOMContentLoaded', function() {
                const showAllLinks = document.querySelectorAll('.show-all-posts');
                
                showAllLinks.forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const shortcode = this.dataset.shortcode;
                        const allPosts = document.querySelector(`.all-posts[data-shortcode="${shortcode}"]`);
                        const seeAll = this.closest('.see-all');
                        
                        if (allPosts && allPosts.style.display === 'none') {
                            allPosts.style.display = 'block';
                            seeAll.style.display = 'none';
                        }
                    });
                });
            });
        })();
        </script>
        <?php
    }
}
