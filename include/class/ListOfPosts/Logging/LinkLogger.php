<?php
namespace gik25microdata\ListOfPosts\Logging;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sistema di logging e debug avanzato
 */
class LinkLogger
{
    private const LOG_OPTION = 'gik25_link_logs';
    private const MAX_LOGS = 1000;
    
    /**
     * Logga un evento
     * 
     * @param string $level Livello (info, warning, error, debug)
     * @param string $message Messaggio
     * @param array $context Contesto aggiuntivo
     */
    public static function log(string $level, string $message, array $context = []): void
    {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return; // Non loggare se debug disabilitato
        }
        
        $log_entry = [
            'timestamp' => current_time('mysql'),
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'user_id' => get_current_user_id(),
            'url' => $_SERVER['REQUEST_URI'] ?? '',
        ];
        
        $logs = get_option(self::LOG_OPTION, []);
        $logs[] = $log_entry;
        
        // Mantieni solo gli ultimi MAX_LOGS
        if (count($logs) > self::MAX_LOGS) {
            $logs = array_slice($logs, -self::MAX_LOGS);
        }
        
        update_option(self::LOG_OPTION, $logs, false);
        
        // Log anche in error_log se abilitato
        if (function_exists('error_log')) {
            error_log(sprintf('[GIK25 Link] [%s] %s', strtoupper($level), $message));
        }
    }
    
    public static function info(string $message, array $context = []): void
    {
        self::log('info', $message, $context);
    }
    
    public static function warning(string $message, array $context = []): void
    {
        self::log('warning', $message, $context);
    }
    
    public static function error(string $message, array $context = []): void
    {
        self::log('error', $message, $context);
    }
    
    public static function debug(string $message, array $context = []): void
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            self::log('debug', $message, $context);
        }
    }
    
    /**
     * Ottiene i log
     * 
     * @param array $filters Filtri ['level' => ..., 'since' => ...]
     * @return array Array di log
     */
    public static function getLogs(array $filters = []): array
    {
        $logs = get_option(self::LOG_OPTION, []);
        
        if (!empty($filters['level'])) {
            $logs = array_filter($logs, function($log) use ($filters) {
                return $log['level'] === $filters['level'];
            });
        }
        
        if (!empty($filters['since'])) {
            $since = strtotime($filters['since']);
            $logs = array_filter($logs, function($log) use ($since) {
                return strtotime($log['timestamp']) >= $since;
            });
        }
        
        return array_values($logs);
    }
    
    /**
     * Pulisce i log
     * 
     * @param int $older_than Timestamp - elimina log più vecchi
     * @return int Numero di log eliminati
     */
    public static function clearLogs(?int $older_than = null): int
    {
        if ($older_than === null) {
            delete_option(self::LOG_OPTION);
            return self::MAX_LOGS;
        }
        
        $logs = get_option(self::LOG_OPTION, []);
        $before = count($logs);
        
        $logs = array_filter($logs, function($log) use ($older_than) {
            return strtotime($log['timestamp']) >= $older_than;
        });
        
        update_option(self::LOG_OPTION, array_values($logs), false);
        
        return $before - count($logs);
    }
}
