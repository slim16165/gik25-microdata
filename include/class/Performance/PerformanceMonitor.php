<?php
namespace gik25microdata\Performance;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Performance Monitoring System
 * 
 * Monitora page load time, query DB, memory usage
 */
class PerformanceMonitor
{
    const TABLE_NAME = 'revious_performance_logs';
    
    private static $start_time;
    private static $start_memory;
    private static $queries_start;
    
    /**
     * Inizializza monitor
     */
    public static function init(): void
    {
        self::createTable();
        self::startMonitoring();
        
        add_action('wp_footer', [self::class, 'endMonitoring'], 999);
        add_action('admin_footer', [self::class, 'endMonitoring'], 999);
    }
    
    /**
     * Crea tabella performance
     */
    private static function createTable(): void
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            page_url varchar(500) NOT NULL,
            page_type varchar(50) DEFAULT NULL,
            load_time decimal(10,3) DEFAULT NULL,
            memory_peak bigint(20) DEFAULT NULL,
            memory_usage bigint(20) DEFAULT NULL,
            query_count int(11) DEFAULT NULL,
            query_time decimal(10,3) DEFAULT NULL,
            slow_queries text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY page_url (page_url(255)),
            KEY created_at (created_at)
        ) {$charset_collate};";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Inizia monitoraggio
     */
    public static function startMonitoring(): void
    {
        self::$start_time = microtime(true);
        self::$start_memory = memory_get_usage();
        self::$queries_start = $GLOBALS['wpdb']->num_queries ?? 0;
    }
    
    /**
     * Termina monitoraggio e salva
     */
    public static function endMonitoring(): void
    {
        global $wpdb;
        
        $load_time = microtime(true) - self::$start_time;
        $memory_peak = memory_get_peak_usage();
        $memory_usage = memory_get_usage() - self::$start_memory;
        $query_count = ($GLOBALS['wpdb']->num_queries ?? 0) - self::$queries_start;
        $query_time = $wpdb->queries ? array_sum(array_column($wpdb->queries, 1)) : 0;
        
        // Rileva slow queries
        $slow_queries = self::detectSlowQueries($wpdb->queries ?? []);
        
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        
        $data = [
            'page_url' => esc_url_raw((is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']),
            'page_type' => self::getPageType(),
            'load_time' => round($load_time, 3),
            'memory_peak' => $memory_peak,
            'memory_usage' => $memory_usage,
            'query_count' => $query_count,
            'query_time' => round($query_time, 3),
            'slow_queries' => !empty($slow_queries) ? wp_json_encode($slow_queries) : null,
        ];
        
        // Salva solo se non è admin o se è abilitato
        if (!is_admin() || get_option('revious_performance_track_admin', false)) {
            $wpdb->insert($table_name, $data);
        }
    }
    
    /**
     * Rileva slow queries
     */
    private static function detectSlowQueries(array $queries, float $threshold = 0.1): array
    {
        $slow = [];
        
        foreach ($queries as $query) {
            if (is_array($query) && isset($query[1]) && $query[1] > $threshold) {
                $slow[] = [
                    'query' => substr($query[0] ?? '', 0, 200),
                    'time' => $query[1],
                ];
            }
        }
        
        return array_slice($slow, 0, 10); // Max 10 slow queries
    }
    
    /**
     * Ottiene tipo pagina
     */
    private static function getPageType(): string
    {
        if (is_front_page()) return 'home';
        if (is_single()) return 'single';
        if (is_page()) return 'page';
        if (is_category()) return 'category';
        if (is_tag()) return 'tag';
        if (is_archive()) return 'archive';
        if (is_search()) return 'search';
        if (is_404()) return '404';
        if (is_admin()) return 'admin';
        
        return 'other';
    }
    
    /**
     * Ottiene report performance
     */
    public static function getReport(int $days = 7): array
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        $date_from = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                COUNT(*) as total_pages,
                AVG(load_time) as avg_load_time,
                MAX(load_time) as max_load_time,
                AVG(query_count) as avg_queries,
                AVG(query_time) as avg_query_time,
                AVG(memory_peak) as avg_memory_peak
            FROM {$table_name}
            WHERE created_at >= %s",
            $date_from
        ), ARRAY_A);
        
        return $stats ?: [];
    }
    
    /**
     * Ottiene pagine più lente
     */
    public static function getSlowestPages(int $limit = 10): array
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT 
                page_url,
                page_type,
                AVG(load_time) as avg_load_time,
                MAX(load_time) as max_load_time,
                COUNT(*) as views
            FROM {$table_name}
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY page_url, page_type
            ORDER BY avg_load_time DESC
            LIMIT %d",
            $limit
        ), ARRAY_A) ?: [];
    }
}
