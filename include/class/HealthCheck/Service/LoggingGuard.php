<?php
namespace gik25microdata\HealthCheck\Service;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Service per gestire il logging degli errori durante l'esecuzione dei check
 */
class LoggingGuard
{
    /**
     * Disabilita il logging degli errori in modo sicuro
     * @return array Stato originale da ripristinare
     */
    public static function disable(): array
    {
        return [
            'error_reporting' => @error_reporting(0),
            'display_errors' => @ini_get('display_errors'),
            'log_errors' => @ini_get('log_errors'),
            'error_log' => @ini_get('error_log'),
        ];
    }
    
    /**
     * Ripristina il logging degli errori
     * @param array $state Stato originale da ripristinare
     */
    public static function restore(array $state): void
    {
        if (isset($state['error_reporting'])) {
            @error_reporting($state['error_reporting']);
        }
        if (isset($state['display_errors'])) {
            @ini_set('display_errors', $state['display_errors']);
        }
        if (isset($state['log_errors'])) {
            @ini_set('log_errors', $state['log_errors']);
        }
        if (isset($state['error_log'])) {
            @ini_set('error_log', $state['error_log']);
        }
    }
}

