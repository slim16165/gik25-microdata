<?php
namespace gik25microdata\HealthCheck;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sistema di Health Check per verifica funzionalità plugin
 * 
 * Verifica:
 * - Shortcode registrati
 * - REST API endpoints
 * - AJAX endpoints
 * - CSS/JS caricati
 * - Tabelle database
 * - File esistenti
 */
class HealthChecker
{
    private const CHECK_CACHE_KEY = 'health_check_results';
    private const CHECK_CACHE_EXPIRATION = 300; // 5 minuti

    /**
     * Inizializza health check
     */
    public static function init(): void
    {
        // Pagina admin per health check
        add_action('admin_menu', [self::class, 'add_admin_page']);
        
        // AJAX endpoint per eseguire check
        add_action('wp_ajax_gik25_health_check', [self::class, 'ajax_run_checks']);
        
        // REST API endpoint per health check (per testing esterno)
        add_action('rest_api_init', [self::class, 'register_rest_endpoint']);
    }

    /**
     * Aggiungi pagina admin
     */
    public static function add_admin_page(): void
    {
        add_submenu_page(
            'tools.php',
            'Health Check Plugin',
            'Health Check',
            'manage_options',
            'gik25-health-check',
            [self::class, 'render_admin_page']
        );
    }

    /**
     * Render pagina admin
     */
    public static function render_admin_page(): void
    {
        $checks = self::run_all_checks();
        
        ?>
        <div class="wrap">
            <h1>Health Check - Revious Microdata</h1>
            <p>Verifica che tutte le funzionalità del plugin siano operative dopo un deploy.</p>
            
            <div style="margin: 20px 0;">
                <button type="button" class="button button-primary" id="run-health-check">
                    🔄 Esegui Health Check
                </button>
                <button type="button" class="button" id="export-results">
                    📥 Esporta Risultati
                </button>
            </div>

            <div id="health-check-results">
                <?php self::render_checks_results($checks); ?>
            </div>
        </div>

        <style>
            .health-check-item {
                padding: 15px;
                margin: 10px 0;
                border-left: 4px solid #ddd;
                background: #f9f9f9;
            }
            .health-check-item.success {
                border-left-color: #46b450;
                background: #f0f9f0;
            }
            .health-check-item.warning {
                border-left-color: #ffb900;
                background: #fffbf0;
            }
            .health-check-item.error {
                border-left-color: #dc3232;
                background: #fff0f0;
            }
            .health-check-item h3 {
                margin: 0 0 10px 0;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .health-check-item .details {
                margin-top: 10px;
                padding: 10px;
                background: white;
                border-radius: 4px;
                font-family: monospace;
                font-size: 12px;
            }
            .health-check-summary {
                padding: 20px;
                margin: 20px 0;
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
        </style>

        <script>
        jQuery(document).ready(function($) {
            $('#run-health-check').on('click', function() {
                var button = $(this);
                button.prop('disabled', true).text('⏳ Esecuzione...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'gik25_health_check',
                        nonce: '<?php echo wp_create_nonce('gik25_health_check'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#health-check-results').html(response.data.html);
                        } else {
                            alert('Errore: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('Errore nella richiesta AJAX');
                    },
                    complete: function() {
                        button.prop('disabled', false).text('🔄 Esegui Health Check');
                    }
                });
            });

            $('#export-results').on('click', function() {
                var results = $('#health-check-results').html();
                var blob = new Blob([results], { type: 'text/html' });
                var url = window.URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = url;
                a.download = 'health-check-' + new Date().toISOString().split('T')[0] + '.html';
                a.click();
            });
        });
        </script>
        <?php
    }

    /**
     * Render risultati check
     */
    private static function render_checks_results(array $checks): void
    {
        $total = count($checks);
        $success = count(array_filter($checks, fn($c) => $c['status'] === 'success'));
        $warnings = count(array_filter($checks, fn($c) => $c['status'] === 'warning'));
        $errors = count(array_filter($checks, fn($c) => $c['status'] === 'error'));

        ?>
        <div class="health-check-summary">
            <h2>Riepilogo</h2>
            <p>
                <strong>Totale:</strong> <?php echo $total; ?> | 
                <span style="color: #46b450;">✅ Successo: <?php echo $success; ?></span> | 
                <span style="color: #ffb900;">⚠️ Warning: <?php echo $warnings; ?></span> | 
                <span style="color: #dc3232;">❌ Errori: <?php echo $errors; ?></span>
            </p>
            <p><small>Ultimo check: <?php echo current_time('mysql'); ?></small></p>
        </div>

        <?php foreach ($checks as $check): ?>
            <div class="health-check-item <?php echo esc_attr($check['status']); ?>">
                <h3>
                    <?php
                    $icon = $check['status'] === 'success' ? '✅' : ($check['status'] === 'warning' ? '⚠️' : '❌');
                    echo $icon . ' ' . esc_html($check['name']);
                    ?>
                </h3>
                <p><?php echo esc_html($check['message']); ?></p>
                <?php if (!empty($check['details'])): ?>
                    <div class="details">
                        <pre><?php echo esc_html($check['details']); ?></pre>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <?php
    }

    /**
     * AJAX handler per eseguire check
     */
    public static function ajax_run_checks(): void
    {
        check_ajax_referer('gik25_health_check', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permessi insufficienti');
            return;
        }

        $checks = self::run_all_checks();
        
        ob_start();
        self::render_checks_results($checks);
        $html = ob_get_clean();

        wp_send_json_success(['html' => $html, 'checks' => $checks]);
    }

    /**
     * Registra REST API endpoint per health check
     */
    public static function register_rest_endpoint(): void
    {
        register_rest_route('gik25/v1', '/health-check', [
            'methods' => 'GET',
            'callback' => [self::class, 'rest_health_check'],
            'permission_callback' => function() {
                // Accesso pubblico ma limitato (puoi aggiungere autenticazione se necessario)
                return true;
            },
        ]);
    }

    /**
     * REST API handler per health check
     */
    public static function rest_health_check(): \WP_REST_Response
    {
        $checks = self::run_all_checks();
        
        $summary = [
            'total' => count($checks),
            'success' => count(array_filter($checks, fn($c) => $c['status'] === 'success')),
            'warnings' => count(array_filter($checks, fn($c) => $c['status'] === 'warning')),
            'errors' => count(array_filter($checks, fn($c) => $c['status'] === 'error')),
            'timestamp' => current_time('mysql'),
            'checks' => $checks,
        ];

        return new \WP_REST_Response($summary, 200);
    }

    /**
     * Esegui tutti i check
     */
    public static function run_all_checks(): array
    {
        $checks = [];

        // 1. Check shortcode registrati
        $checks[] = self::check_shortcodes();

        // 2. Check REST API endpoints
        $checks[] = self::check_rest_api();

        // 3. Check AJAX endpoints
        $checks[] = self::check_ajax_endpoints();

        // 4. Check file esistenza
        $checks[] = self::check_files();

        // 5. Check tabelle database
        $checks[] = self::check_database_tables();

        // 6. Check CSS/JS caricati
        $checks[] = self::check_assets();

        // 7. Check classi PHP
        $checks[] = self::check_classes();

        return $checks;
    }

    /**
     * Check shortcode registrati
     */
    private static function check_shortcodes(): array
    {
        global $shortcode_tags;
        
        $expected_shortcodes = [
            'kitchen_finder', 'md_boxinfo', 'boxinfo', 'boxinformativo',
            'md_quote', 'quote', 'youtube', 'telefono', 'slidingbox',
            'progressbar', 'prezzo', 'flipbox', 'flexlist', 'blinkingbutton',
            'perfectpullquote', 'app_nav', 'carousel', 'list', 'grid',
        ];

        $missing = [];
        $registered = [];

        foreach ($expected_shortcodes as $tag) {
            if (isset($shortcode_tags[$tag])) {
                $registered[] = $tag;
            } else {
                $missing[] = $tag;
            }
        }

        $status = empty($missing) ? 'success' : 'error';
        $message = empty($missing) 
            ? sprintf('Tutti gli shortcode registrati (%d)', count($registered))
            : sprintf('Shortcode mancanti: %s', implode(', ', $missing));

        return [
            'name' => 'Shortcode Registrati',
            'status' => $status,
            'message' => $message,
            'details' => 'Registrati: ' . implode(', ', $registered) . "\n" . 
                        (empty($missing) ? '' : 'Mancanti: ' . implode(', ', $missing)),
        ];
    }

    /**
     * Check REST API endpoints
     */
    private static function check_rest_api(): array
    {
        $endpoints = [
            '/wp-json/wp-mcp/v1/categories',
            '/wp-json/wp-mcp/v1/posts/recent',
            '/wp-json/wp-mcp/v1/posts/search?q=test',
        ];

        $working = [];
        $failed = [];

        foreach ($endpoints as $endpoint) {
            $url = home_url($endpoint);
            $response = wp_remote_get($url, ['timeout' => 5]);
            
            if (is_wp_error($response)) {
                $failed[] = $endpoint . ' (' . $response->get_error_message() . ')';
            } elseif (wp_remote_retrieve_response_code($response) === 200) {
                $working[] = $endpoint;
            } else {
                $failed[] = $endpoint . ' (HTTP ' . wp_remote_retrieve_response_code($response) . ')';
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

    /**
     * Check AJAX endpoints
     */
    private static function check_ajax_endpoints(): array
    {
        $expected_actions = [
            'kitchen_finder_calculate',
            'kitchen_finder_pdf',
        ];

        // Verifica che gli hook siano registrati
        global $wp_filter;
        $registered = [];
        $missing = [];

        foreach ($expected_actions as $action) {
            $hook_logged = 'wp_ajax_' . $action;
            $hook_nopriv = 'wp_ajax_nopriv_' . $action;
            
            if (isset($wp_filter[$hook_logged]) || isset($wp_filter[$hook_nopriv])) {
                $registered[] = $action;
            } else {
                $missing[] = $action;
            }
        }

        $status = empty($missing) ? 'success' : 'error';
        $message = empty($missing)
            ? sprintf('Tutti gli endpoint AJAX registrati (%d)', count($registered))
            : sprintf('Endpoint AJAX mancanti: %s', implode(', ', $missing));

        return [
            'name' => 'AJAX Endpoints',
            'status' => $status,
            'message' => $message,
            'details' => 'Registrati: ' . implode(', ', $registered) . "\n" .
                        (empty($missing) ? '' : 'Mancanti: ' . implode(', ', $missing)),
        ];
    }

    /**
     * Check file esistenza
     */
    private static function check_files(): array
    {
        $plugin_dir = plugin_dir_path(__FILE__);
        $plugin_dir = dirname(dirname(dirname($plugin_dir)));

        $critical_files = [
            'include/class/PluginBootstrap.php',
            'include/class/Shortcodes/kitchenfinder.php',
            'include/class/Shortcodes/appnav.php',
            'assets/css/kitchen-finder.css',
            'assets/js/kitchen-finder.js',
            'assets/css/app-nav.css',
            'assets/js/app-nav.js',
        ];

        $existing = [];
        $missing = [];

        foreach ($critical_files as $file) {
            $path = $plugin_dir . '/' . $file;
            if (file_exists($path)) {
                $existing[] = $file;
            } else {
                $missing[] = $file;
            }
        }

        $status = empty($missing) ? 'success' : 'error';
        $message = empty($missing)
            ? sprintf('Tutti i file critici presenti (%d)', count($existing))
            : sprintf('File mancanti: %d/%d', count($missing), count($critical_files));

        return [
            'name' => 'File Critici',
            'status' => $status,
            'message' => $message,
            'details' => 'Presenti: ' . implode(', ', $existing) . "\n" .
                        (empty($missing) ? '' : 'Mancanti: ' . implode(', ', $missing)),
        ];
    }

    /**
     * Check tabelle database
     */
    private static function check_database_tables(): array
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

    /**
     * Check assets (CSS/JS)
     */
    private static function check_assets(): array
    {
        $plugin_dir = plugin_dir_path(__FILE__);
        $plugin_dir = dirname(dirname(dirname($plugin_dir)));
        $plugin_url = plugin_dir_url($plugin_dir . '/revious-microdata.php');

        $assets = [
            'assets/css/kitchen-finder.css',
            'assets/js/kitchen-finder.js',
            'assets/css/app-nav.css',
            'assets/js/app-nav.js',
        ];

        $accessible = [];
        $failed = [];

        foreach ($assets as $asset) {
            $url = $plugin_url . $asset;
            $response = wp_remote_head($url, ['timeout' => 5]);
            
            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                $accessible[] = $asset;
            } else {
                $failed[] = $asset;
            }
        }

        $status = empty($failed) ? 'success' : 'warning';
        $message = empty($failed)
            ? sprintf('Tutti gli asset accessibili (%d)', count($accessible))
            : sprintf('Asset inaccessibili: %d/%d', count($failed), count($assets));

        return [
            'name' => 'Assets (CSS/JS)',
            'status' => $status,
            'message' => $message,
            'details' => 'Accessibili: ' . implode(', ', $accessible) . "\n" .
                        (empty($failed) ? '' : 'Inaccessibili: ' . implode(', ', $failed)),
        ];
    }

    /**
     * Check classi PHP
     */
    private static function check_classes(): array
    {
        $expected_classes = [
            'gik25microdata\PluginBootstrap',
            'gik25microdata\Shortcodes\KitchenFinder',
            'gik25microdata\Shortcodes\AppNav',
            'gik25microdata\REST\MCPApi',
            'gik25microdata\Widgets\ContextualWidgets',
        ];

        $existing = [];
        $missing = [];

        foreach ($expected_classes as $class) {
            if (class_exists($class)) {
                $existing[] = $class;
            } else {
                $missing[] = $class;
            }
        }

        $status = empty($missing) ? 'success' : 'error';
        $message = empty($missing)
            ? sprintf('Tutte le classi caricate (%d)', count($existing))
            : sprintf('Classi mancanti: %d/%d', count($missing), count($expected_classes));

        return [
            'name' => 'Classi PHP',
            'status' => $status,
            'message' => $message,
            'details' => 'Caricate: ' . implode(', ', $existing) . "\n" .
                        (empty($missing) ? '' : 'Mancanti: ' . implode(', ', $missing)),
        ];
    }
}

