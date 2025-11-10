<?php
namespace gik25microdata\Security;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Security Enhancements
 * 
 * Rate limiting, brute force protection, security headers
 */
class SecurityManager
{
    const TABLE_NAME = 'revious_security_logs';
    const RATE_LIMIT_REQUESTS = 100; // Richieste per ora
    const RATE_LIMIT_WINDOW = 3600; // 1 ora
    
    /**
     * Inizializza security manager
     */
    public static function init(): void
    {
        self::createTable();
        add_action('init', [self::class, 'checkRateLimit']);
        add_action('wp_headers', [self::class, 'addSecurityHeaders']);
        add_action('login_errors', [self::class, 'preventBruteForceInfo']);
    }
    
    /**
     * Crea tabella security logs
     */
    private static function createTable(): void
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            ip_address varchar(45) NOT NULL,
            action varchar(50) NOT NULL,
            blocked tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY ip_address (ip_address),
            KEY created_at (created_at)
        ) {$charset_collate};";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Controlla rate limit
     */
    public static function checkRateLimit(): void
    {
        $ip = self::getClientIp();
        
        if (self::isRateLimited($ip)) {
            self::logSecurityEvent($ip, 'rate_limit_exceeded', true);
            wp_die('Troppe richieste. Riprova più tardi.', 'Rate Limit Exceeded', ['response' => 429]);
        }
        
        self::logSecurityEvent($ip, 'request', false);
    }
    
    /**
     * Verifica se IP è rate limited
     */
    private static function isRateLimited(string $ip): bool
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        $window_start = date('Y-m-d H:i:s', time() - self::RATE_LIMIT_WINDOW);
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name}
            WHERE ip_address = %s
            AND created_at >= %s
            AND blocked = 0",
            $ip,
            $window_start
        ));
        
        return $count >= self::RATE_LIMIT_REQUESTS;
    }
    
    /**
     * Aggiunge security headers
     */
    public static function addSecurityHeaders(array $headers): array
    {
        $headers['X-Content-Type-Options'] = 'nosniff';
        $headers['X-Frame-Options'] = 'SAMEORIGIN';
        $headers['X-XSS-Protection'] = '1; mode=block';
        $headers['Referrer-Policy'] = 'strict-origin-when-cross-origin';
        
        if (is_ssl()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }
        
        return $headers;
    }
    
    /**
     * Previene info leak su brute force
     */
    public static function preventBruteForceInfo(string $error): string
    {
        // Generico messaggio per non rivelare se username esiste
        return 'Credenziali non valide.';
    }
    
    /**
     * Logga evento sicurezza
     */
    private static function logSecurityEvent(string $ip, string $action, bool $blocked): void
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        
        $wpdb->insert($table_name, [
            'ip_address' => $ip,
            'action' => $action,
            'blocked' => $blocked ? 1 : 0,
        ]);
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
