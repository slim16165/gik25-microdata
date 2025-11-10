<?php
namespace gik25microdata\Logs\Support;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Estrae contesto di esecuzione da righe di log
 */
class ContextExtractor
{
    /**
     * Estrae il contesto di esecuzione da una riga di log
     * 
     * @param string $line Riga di log
     * @return array{context: string, script: string, details: array} Contesto identificato
     */
    public static function extract(string $line): array
    {
        $context = 'unknown';
        $script = '';
        $details = [];
        
        // Cerca pattern comuni per identificare il contesto
        // WP-CLI
        if (preg_match('/phar:\/\/.*\/wp\/php\/boot-phar\.php/i', $line) || 
            preg_match('/wp-cli\.php/i', $line) ||
            preg_match('/WP_CLI/i', $line)) {
            $context = 'wp_cli';
            $details['type'] = 'WP-CLI Command';
        }
        // AJAX
        elseif (preg_match('/admin-ajax\.php/i', $line)) {
            $context = 'ajax';
            if (preg_match('/admin-ajax\.php[^\s]*\s+(\w+)/', $line, $matches)) {
                $details['action'] = $matches[1] ?? 'unknown';
            }
            $details['type'] = 'AJAX Request';
        }
        // WP-CRON
        elseif (preg_match('/wp-cron\.php/i', $line) || 
                preg_match('/ActionScheduler/i', $line) ||
                preg_match('/action_scheduler/i', $line) ||
                preg_match('/do_action.*wp_scheduled/i', $line)) {
            $context = 'wp_cron';
            $details['type'] = 'WP-CRON / Action Scheduler';
        }
        // Frontend
        elseif (preg_match('/index\.php/i', $line) && !preg_match('/wp-admin|wp-includes/i', $line)) {
            $context = 'frontend';
            $details['type'] = 'Frontend Request';
        }
        // Backend/Admin
        elseif (preg_match('/wp-admin/i', $line)) {
            $context = 'backend';
            $details['type'] = 'Backend/Admin';
        }
        // REST API
        elseif (preg_match('/wp-json/i', $line) || preg_match('/rest_route/i', $line)) {
            $context = 'rest_api';
            $details['type'] = 'REST API';
        }
        
        // Estrai script filename se presente
        if (preg_match('/script_filename\s*=\s*(.+)/i', $line, $matches)) {
            $script = trim($matches[1]);
            $details['script'] = basename($script);
        } elseif (preg_match('/(\/[^\s]+\.php)/', $line, $matches)) {
            $script = $matches[1];
            $details['script'] = basename($script);
        }
        
        return [
            'context' => $context,
            'script' => $script,
            'details' => $details,
        ];
    }
}

