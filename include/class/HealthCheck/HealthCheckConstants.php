<?php
namespace gik25microdata\HealthCheck;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Costanti condivise per Health Check
 */
final class HealthCheckConstants
{
    /**
     * Labels per contesti di esecuzione
     */
    public const CONTEXT_LABELS = [
        'wp_cli' => 'WP-CLI',
        'ajax' => 'AJAX',
        'wp_cron' => 'WP-CRON',
        'frontend' => 'Frontend',
        'backend' => 'Backend',
        'rest_api' => 'REST API',
        'unknown' => 'Unknown',
    ];

    /**
     * Severità degli errori
     */
    public const SEVERITIES = [
        'fatal',
        'parse',
        'error',
        'exception',
        'warning',
    ];

    /**
     * Severità critiche (errori che bloccano il funzionamento)
     */
    public const CRITICAL_SEVERITIES = [
        'fatal',
        'parse',
        'error',
        'exception',
    ];

    /**
     * Colori per status
     */
    public const STATUS_COLORS = [
        'success' => '#46b450',
        'warning' => '#ffb900',
        'error' => '#dc3232',
    ];

    /**
     * Labels per tipi di errore
     */
    public const ERROR_TYPE_LABELS = [
        'fatal' => 'Fatal Error',
        'parse' => 'Parse Error',
        'error' => 'Uncaught Error',
        'exception' => 'Uncaught Exception',
        'warning' => 'PHP Warning',
        'database' => 'Database Error',
    ];

    /**
     * Labels per tipi di log tail
     */
    public const TAIL_LABELS = [
        'access_5xx' => 'HTTP 5xx (Nginx/Apache/PHP Access)',
        'nginx_error' => 'Nginx Error',
        'apache_error' => 'Apache Error',
        'php_error' => 'PHP Error',
        'php_slow' => 'PHP Slow',
        'wp_cron' => 'WP-Cron',
    ];

    /**
     * Configurazione per tail dei log
     */
    public const TAIL_LINES_PREVIEW = 15; // Righe per anteprima
    public const TAIL_LINES_DETAILS = 30; // Righe per dettagli
    public const TAIL_WINDOW_HOURS = 24; // Finestra temporale in ore

    /**
     * Cache configuration
     */
    public const CHECK_CACHE_KEY = 'health_check_results';
    public const CHECK_CACHE_EXPIRATION = 300; // 5 minuti

    /**
     * Ottieni label per contesto
     */
    public static function getContextLabel(string $context): string
    {
        return self::CONTEXT_LABELS[$context] ?? ucfirst($context);
    }

    /**
     * Verifica se una severità è critica
     */
    public static function isCriticalSeverity(string $severity): bool
    {
        return in_array($severity, self::CRITICAL_SEVERITIES, true);
    }

    /**
     * Ottieni colore per status
     */
    public static function getStatusColor(string $status): string
    {
        return self::STATUS_COLORS[$status] ?? '#666';
    }
}

