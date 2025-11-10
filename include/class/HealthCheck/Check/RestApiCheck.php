<?php
namespace gik25microdata\HealthCheck\Check;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check per verificare REST API endpoints
 */
class RestApiCheck
{
    /**
     * Esegue il check
     * 
     * @return array Risultato del check
     */
    public static function run(): array
    {
        // Endpoints configurabili tramite filter
        $endpoints = apply_filters('gik25/healthcheck/rest_endpoints', [
            '/wp-mcp/v1/categories',
            '/wp-mcp/v1/posts/recent',
            '/wp-mcp/v1/posts/search?q=test',
        ]);

        $working = [];
        $failed = [];

        foreach ($endpoints as $endpoint) {
            try {
                // Usa rest_do_request() per loopback interno (zero I/O)
                $request = new \WP_REST_Request('GET', $endpoint);
                
                // Aggiungi parametri query se presenti nell'endpoint
                if (strpos($endpoint, '?') !== false) {
                    parse_str(parse_url($endpoint, PHP_URL_QUERY), $params);
                    foreach ($params as $key => $value) {
                        $request->set_param($key, $value);
                    }
                    // Rimuovi query string dal path
                    $endpoint = strtok($endpoint, '?');
                    $request->set_route($endpoint);
                }
                
                $response = rest_do_request($request);
                
                if ($response->is_error()) {
                    $error = $response->as_error();
                    $failed[] = $endpoint . ' (' . $error->get_error_message() . ')';
                } elseif ($response->get_status() === 200) {
                    $working[] = $endpoint;
                } else {
                    $failed[] = $endpoint . ' (HTTP ' . $response->get_status() . ')';
                }
            } catch (\Throwable $e) {
                $failed[] = $endpoint . ' (Exception: ' . $e->getMessage() . ')';
            }
        }

        $status = empty($failed) ? 'success' : (count($failed) < count($endpoints) ? 'warning' : 'error');
        $message = empty($failed)
            ? sprintf('Tutti gli endpoint REST API funzionano (%d)', count($working))
            : sprintf('Endpoint falliti: %d/%d', count($failed), count($endpoints));

        return [
            'name' => 'REST API Endpoints',
            'status' => $status,
            'message' => $message,
            'details' => 'Funzionanti: ' . implode(', ', $working) . "\n" .
                        (empty($failed) ? '' : 'Falliti: ' . implode(', ', $failed)),
        ];
    }
}

