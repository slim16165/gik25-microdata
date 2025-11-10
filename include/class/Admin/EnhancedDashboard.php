<?php
namespace gik25microdata\Admin;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Advanced Admin Dashboard
 * 
 * Dashboard potenziato con statistiche real-time
 */
class EnhancedDashboard
{
    /**
     * Inizializza dashboard
     */
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'addDashboardPage']);
        add_action('wp_ajax_revious_dashboard_stats', [self::class, 'getStats']);
    }
    
    /**
     * Aggiunge pagina dashboard
     */
    public static function addDashboardPage(): void
    {
        add_submenu_page(
            'revious-microdata',
            'Dashboard Avanzato',
            'Dashboard',
            'manage_options',
            'revious-dashboard',
            [self::class, 'renderDashboard']
        );
    }
    
    /**
     * Renderizza dashboard
     */
    public static function renderDashboard(): void
    {
        ?>
        <div class="wrap">
            <h1>Dashboard Avanzato</h1>
            
            <div class="revious-dashboard-grid">
                <div class="dashboard-widget">
                    <h2>Performance</h2>
                    <div id="performance-stats">Caricamento...</div>
                </div>
                
                <div class="dashboard-widget">
                    <h2>Analytics</h2>
                    <div id="analytics-stats">Caricamento...</div>
                </div>
                
                <div class="dashboard-widget">
                    <h2>Cache</h2>
                    <div id="cache-stats">Caricamento...</div>
                </div>
                
                <div class="dashboard-widget">
                    <h2>Notifiche</h2>
                    <div id="notifications-list">Caricamento...</div>
                </div>
            </div>
        </div>
        
        <style>
        .revious-dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .dashboard-widget {
            background: #fff;
            padding: 20px;
            border: 1px solid #ccd0d4;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            function loadStats() {
                $.ajax({
                    url: ajaxurl,
                    data: {
                        action: 'revious_dashboard_stats'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#performance-stats').html(response.data.performance);
                            $('#analytics-stats').html(response.data.analytics);
                            $('#cache-stats').html(response.data.cache);
                            $('#notifications-list').html(response.data.notifications);
                        }
                    }
                });
            }
            
            loadStats();
            setInterval(loadStats, 30000); // Aggiorna ogni 30 secondi
        });
        </script>
        <?php
    }
    
    /**
     * Ottiene statistiche
     */
    public static function getStats(): void
    {
        // Performance stats
        $perf_stats = [];
        if (class_exists('\gik25microdata\Performance\PerformanceMonitor')) {
            $perf_stats = \gik25microdata\Performance\PerformanceMonitor::getReport(7);
        }
        
        // Analytics stats
        $analytics_stats = [];
        if (class_exists('\gik25microdata\Analytics\AnalyticsTracker')) {
            $analytics_stats = \gik25microdata\Analytics\AnalyticsTracker::getStats(7);
        }
        
        // Cache stats
        $cache_stats = [];
        if (class_exists('\gik25microdata\Cache\CacheManager')) {
            $cache_stats = \gik25microdata\Cache\CacheManager::getStats();
        }
        
        // Notifications
        $notifications = [];
        if (class_exists('\gik25microdata\Notifications\NotificationManager')) {
            $notifications = \gik25microdata\Notifications\NotificationManager::getUnreadNotifications();
        }
        
        wp_send_json_success([
            'performance' => self::formatPerformanceStats($perf_stats),
            'analytics' => self::formatAnalyticsStats($analytics_stats),
            'cache' => self::formatCacheStats($cache_stats),
            'notifications' => self::formatNotifications($notifications),
        ]);
    }
    
    /**
     * Formatta stats performance
     */
    private static function formatPerformanceStats(array $stats): string
    {
        if (empty($stats)) {
            return '<p>Nessun dato disponibile</p>';
        }
        
        return sprintf(
            '<p>Load Time Medio: <strong>%.2fs</strong></p>
            <p>Query DB Medie: <strong>%d</strong></p>
            <p>Memory Peak: <strong>%s</strong></p>',
            $stats['avg_load_time'] ?? 0,
            round($stats['avg_queries'] ?? 0),
            size_format($stats['avg_memory_peak'] ?? 0)
        );
    }
    
    /**
     * Formatta stats analytics
     */
    private static function formatAnalyticsStats(array $stats): string
    {
        if (empty($stats)) {
            return '<p>Nessun dato disponibile</p>';
        }
        
        $html = '<ul>';
        foreach (array_slice($stats, 0, 5) as $stat) {
            $html .= sprintf(
                '<li>%s: <strong>%d</strong> (%d sessioni uniche)</li>',
                esc_html($stat['event_name']),
                $stat['count'],
                $stat['unique_sessions']
            );
        }
        $html .= '</ul>';
        
        return $html;
    }
    
    /**
     * Formatta stats cache
     */
    private static function formatCacheStats(array $stats): string
    {
        return sprintf(
            '<p>Transients: <strong>%d</strong></p>
            <p>Object Cache: <strong>%s</strong></p>',
            $stats['transients_count'] ?? 0,
            ($stats['object_cache_enabled'] ?? false) ? 'Abilitato' : 'Disabilitato'
        );
    }
    
    /**
     * Formatta notifiche
     */
    private static function formatNotifications(array $notifications): string
    {
        if (empty($notifications)) {
            return '<p>Nessuna notifica</p>';
        }
        
        $html = '<ul>';
        foreach (array_slice($notifications, 0, 5) as $notif) {
            $html .= sprintf(
                '<li><strong>%s</strong>: %s</li>',
                esc_html($notif['type']),
                esc_html($notif['message'])
            );
        }
        $html .= '</ul>';
        
        return $html;
    }
}
