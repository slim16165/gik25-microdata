<?php
namespace gik25microdata\Analytics;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sistema Analytics e Tracking Integrato
 * 
 * Tracking eventi utente, conversioni, heatmap
 */
class AnalyticsTracker
{
    const TABLE_NAME = 'revious_analytics_events';
    
    /**
     * Inizializza sistema analytics
     */
    public static function init(): void
    {
        self::createTable();
        add_action('wp_footer', [self::class, 'enqueueTrackingScript']);
        add_action('wp_ajax_revious_track_event', [self::class, 'handleTrackEvent']);
        add_action('wp_ajax_nopriv_revious_track_event', [self::class, 'handleTrackEvent']);
    }
    
    /**
     * Crea tabella analytics
     */
    private static function createTable(): void
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            event_type varchar(50) NOT NULL,
            event_name varchar(100) NOT NULL,
            post_id bigint(20) UNSIGNED DEFAULT NULL,
            user_id bigint(20) UNSIGNED DEFAULT NULL,
            session_id varchar(100) DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            user_agent text DEFAULT NULL,
            referrer text DEFAULT NULL,
            metadata longtext DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY event_type (event_type),
            KEY event_name (event_name),
            KEY post_id (post_id),
            KEY created_at (created_at)
        ) {$charset_collate};";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Traccia evento
     */
    public static function track(string $event_type, string $event_name, array $metadata = []): bool
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        
        $data = [
            'event_type' => sanitize_text_field($event_type),
            'event_name' => sanitize_text_field($event_name),
            'post_id' => get_the_ID() ?: null,
            'user_id' => get_current_user_id() ?: null,
            'session_id' => self::getSessionId(),
            'ip_address' => self::getClientIp(),
            'user_agent' => sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? ''),
            'referrer' => esc_url_raw(wp_get_referer() ?: ''),
            'metadata' => wp_json_encode($metadata),
        ];
        
        return $wpdb->insert($table_name, $data) !== false;
    }
    
    /**
     * Handler AJAX per tracking
     */
    public static function handleTrackEvent(): void
    {
        check_ajax_referer('revious_analytics', 'nonce');
        
        $event_type = sanitize_text_field($_POST['event_type'] ?? '');
        $event_name = sanitize_text_field($_POST['event_name'] ?? '');
        $metadata = json_decode(stripslashes($_POST['metadata'] ?? '{}'), true) ?: [];
        
        if (empty($event_type) || empty($event_name)) {
            wp_send_json_error(['message' => 'Invalid parameters']);
            return;
        }
        
        $result = self::track($event_type, $event_name, $metadata);
        
        wp_send_json_success(['tracked' => $result]);
    }
    
    /**
     * Enqueue tracking script
     */
    public static function enqueueTrackingScript(): void
    {
        $nonce = wp_create_nonce('revious_analytics');
        ?>
        <script>
        (function() {
            'use strict';
            
            const sessionId = sessionStorage.getItem('revious_session_id') || 
                             '<?php echo esc_js(uniqid('sess_', true)); ?>';
            sessionStorage.setItem('revious_session_id', sessionId);
            
            function trackEvent(eventType, eventName, metadata = {}) {
                const formData = new FormData();
                formData.append('action', 'revious_track_event');
                formData.append('nonce', '<?php echo esc_js($nonce); ?>');
                formData.append('event_type', eventType);
                formData.append('event_name', eventName);
                formData.append('metadata', JSON.stringify(metadata));
                
                fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                    method: 'POST',
                    body: formData
                }).catch(() => {}); // Silently fail
            }
            
            // Track page view
            trackEvent('page', 'view', {
                url: window.location.href,
                title: document.title
            });
            
            // Track scroll depth
            let maxScroll = 0;
            window.addEventListener('scroll', function() {
                const scrollPercent = Math.round(
                    (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100
                );
                if (scrollPercent > maxScroll) {
                    maxScroll = scrollPercent;
                    if ([25, 50, 75, 100].includes(scrollPercent)) {
                        trackEvent('engagement', 'scroll', { depth: scrollPercent });
                    }
                }
            });
            
            // Track time on page
            const startTime = Date.now();
            window.addEventListener('beforeunload', function() {
                const timeOnPage = Math.round((Date.now() - startTime) / 1000);
                trackEvent('engagement', 'time_on_page', { seconds: timeOnPage });
            });
            
            // Track clicks on links
            document.addEventListener('click', function(e) {
                const target = e.target.closest('a');
                if (target && target.href) {
                    trackEvent('click', 'link', {
                        url: target.href,
                        text: target.textContent?.trim().substring(0, 100)
                    });
                }
            });
            
            // Expose global function
            window.reviousTrack = trackEvent;
        })();
        </script>
        <?php
    }
    
    /**
     * Ottiene statistiche
     */
    public static function getStats(int $days = 30): array
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        $date_from = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $stats = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                event_type,
                event_name,
                COUNT(*) as count,
                COUNT(DISTINCT session_id) as unique_sessions
            FROM {$table_name}
            WHERE created_at >= %s
            GROUP BY event_type, event_name
            ORDER BY count DESC
            LIMIT 50",
            $date_from
        ), ARRAY_A);
        
        return $stats ?: [];
    }
    
    /**
     * Ottiene session ID
     */
    private static function getSessionId(): string
    {
        if (!session_id()) {
            session_start();
        }
        return session_id() ?: uniqid('sess_', true);
    }
    
    /**
     * Ottiene IP client
     */
    private static function getClientIp(): string
    {
        $ip_keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = sanitize_text_field($_SERVER[$key]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return '0.0.0.0';
    }
}
