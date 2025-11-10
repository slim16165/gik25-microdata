<?php
namespace gik25microdata\Logs\Viewer;

use gik25microdata\Logs\Analysis\CloudwaysLogParser;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * REST API per Log Viewer
 * 
 * Endpoints:
 * - GET /wp-json/gik25/v1/logs/errors - Lista errori PHP con filtri
 */
class LogViewerAPI
{
    /**
     * Inizializza REST API
     */
    public static function init(): void
    {
        // Verifica che WordPress sia caricato
        if (!function_exists('add_action')) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('LogViewerAPI: WordPress non ancora caricato');
            }
            return;
        }
        
        // Registra le route quando REST API è inizializzata
        // Priority 10 per assicurarsi che sia eseguito dopo altri plugin
        add_action('rest_api_init', [self::class, 'register_routes'], 10);
        
        // Debug: verifica che l'hook sia stato aggiunto
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('LogViewerAPI: Hook rest_api_init registrato');
        }
    }
    
    /**
     * Registra route REST API
     */
    public static function register_routes(): void
    {
        // Verifica che le funzioni WordPress siano disponibili
        if (!function_exists('register_rest_route')) {
            error_log('LogViewerAPI: register_rest_route non disponibile');
            return;
        }
        
        register_rest_route('gik25/v1', '/logs/errors', [
            'methods' => 'GET',
            'callback' => [self::class, 'get_errors'],
            'permission_callback' => [self::class, 'check_permission'],
            'args' => [
                'severity' => [
                    'type' => 'string',
                    'description' => 'Filtro severity (comma-separated: fatal,error,warning,info)',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'file' => [
                    'type' => 'string',
                    'description' => 'Filtro file (pattern matching)',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'since' => [
                    'type' => 'integer',
                    'description' => 'Timestamp Unix (from)',
                    'sanitize_callback' => 'absint',
                ],
                'until' => [
                    'type' => 'integer',
                    'description' => 'Timestamp Unix (to)',
                    'sanitize_callback' => 'absint',
                ],
                'context' => [
                    'type' => 'string',
                    'description' => 'Filtro contesto (comma-separated: wp_cli,ajax,wp_cron,frontend,backend,rest_api,unknown)',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'hours' => [
                    'type' => 'integer',
                    'description' => 'Limita l\'analisi alle ultime N ore (0 = intero log)',
                    'default' => 0,
                    'sanitize_callback' => 'absint',
                    'validate_callback' => function($param) {
                        return $param <= 720; // Max 30 giorni per sicurezza
                    },
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Numero massimo risultati (default: 1000)',
                    'default' => 1000,
                    'sanitize_callback' => 'absint',
                    'validate_callback' => function($param) {
                        return $param >= 1 && $param <= 10000;
                    },
                ],
                'offset' => [
                    'type' => 'integer',
                    'description' => 'Offset paginazione (default: 0)',
                    'default' => 0,
                    'sanitize_callback' => 'absint',
                ],
                'format' => [
                    'type' => 'string',
                    'description' => 'Formato output (json, csv)',
                    'default' => 'json',
                    'enum' => ['json', 'csv'],
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);
    }
    
    /**
     * Verifica permessi
     */
    public static function check_permission(): bool
    {
        return current_user_can('manage_options');
    }
    
    /**
     * GET /wp-json/gik25/v1/logs/errors
     * 
     * @param \WP_REST_Request $request Richiesta REST
     * @return \WP_REST_Response|\WP_Error Risposta REST
     */
    public static function get_errors(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $filters = [
                'severity' => $request->get_param('severity'),
                'file' => $request->get_param('file'),
                'since' => $request->get_param('since'),
                'until' => $request->get_param('until'),
                'context' => $request->get_param('context'),
                'limit' => $request->get_param('limit') ?: 1000,
                'offset' => $request->get_param('offset') ?: 0,
            ];
            
            $format = $request->get_param('format') ?: 'json';
            
            // Estrai errori strutturati
            $result = CloudwaysLogParser::get_php_errors_structured($filters);
            
            // Se formato CSV, genera CSV
            if ($format === 'csv') {
                return self::export_csv($result);
            }
            
            // Altrimenti JSON
            return new \WP_REST_Response($result, 200);
            
        } catch (\Throwable $e) {
            return new \WP_REST_Response([
                'error' => true,
                'message' => 'Errore durante il recupero degli errori',
                'details' => $e->getMessage(),
                'total' => 0,
                'errors' => [],
            ], 500);
        }
    }
    
    /**
     * Esporta errori in formato CSV
     * 
     * @param array $result Risultato da get_php_errors_structured
     * @return \WP_REST_Response Risposta CSV
     */
    private static function export_csv(array $result): \WP_REST_Response
    {
        $csv_lines = [];
        
        // Header
        $csv_lines[] = [
            'ID',
            'Timestamp',
            'Data/Ora',
            'Severity',
            'Tipo',
            'Messaggio',
            'File',
            'Linea',
            'Contesto',
            'Occorrenze',
            'Prima occorrenza',
            'Ultima occorrenza',
        ];
        
        // Dati
        foreach ($result['errors'] ?? [] as $error) {
            $csv_lines[] = [
                $error['id'] ?? '',
                $error['timestamp'] ?? '',
                $error['timestamp'] ? date('Y-m-d H:i:s', $error['timestamp']) : '',
                $error['severity'] ?? '',
                $error['error_type'] ?? '',
                $error['message'] ?? '',
                $error['file'] ?? '',
                $error['line'] ?? '',
                implode(', ', $error['contexts'] ?? []),
                $error['count'] ?? 0,
                $error['first_seen'] ? date('Y-m-d H:i:s', $error['first_seen']) : '',
                $error['last_seen'] ? date('Y-m-d H:i:s', $error['last_seen']) : '',
            ];
        }
        
        // Genera CSV
        $csv_content = '';
        foreach ($csv_lines as $line) {
            $csv_content .= self::csv_encode_line($line) . "\n";
        }
        
        // Ritorna CSV come risposta
        $response = new \WP_REST_Response($csv_content, 200);
        $response->header('Content-Type', 'text/csv; charset=utf-8');
        $response->header('Content-Disposition', 'attachment; filename="php-errors-' . date('Y-m-d-His') . '.csv"');
        
        return $response;
    }
    
    /**
     * Codifica una riga CSV
     * 
     * @param array $fields Campi da codificare
     * @return string Riga CSV codificata
     */
    private static function csv_encode_line(array $fields): string
    {
        $encoded = [];
        foreach ($fields as $field) {
            // Escapa virgolette e avvolgi in virgolette se necessario
            $field = str_replace('"', '""', $field);
            if (strpos($field, ',') !== false || strpos($field, '"') !== false || strpos($field, "\n") !== false) {
                $field = '"' . $field . '"';
            }
            $encoded[] = $field;
        }
        return implode(',', $encoded);
    }
}

