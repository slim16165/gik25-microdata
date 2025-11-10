<?php
namespace gik25microdata\HealthCheck\Check;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check per verificare tabelle database
 */
class DatabaseTablesCheck
{
    /**
     * Esegue il check
     * 
     * @return array Risultato del check
     */
    public static function run(): array
    {
        global $wpdb;
        
        $tables = [
            $wpdb->prefix . 'carousel_collections',
            $wpdb->prefix . 'carousel_items',
        ];

        $existing = [];
        $missing = [];

        foreach ($tables as $table) {
            $result = $wpdb->get_var("SHOW TABLES LIKE '$table'");
            if ($result === $table) {
                $existing[] = str_replace($wpdb->prefix, '', $table);
            } else {
                $missing[] = str_replace($wpdb->prefix, '', $table);
            }
        }

        $status = empty($missing) ? 'success' : 'warning'; // Warning perché le tabelle sono opzionali
        $message = empty($missing)
            ? sprintf('Tutte le tabelle presenti (%d)', count($existing))
            : sprintf('Tabelle mancanti (opzionali): %s', implode(', ', $missing));

        return [
            'name' => 'Tabelle Database',
            'status' => $status,
            'message' => $message,
            'details' => 'Presenti: ' . implode(', ', $existing) . "\n" .
                        (empty($missing) ? '' : 'Mancanti (opzionali): ' . implode(', ', $missing)),
        ];
    }
}

