<?php
namespace gik25microdata\Logs\Viewer;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Log Viewer - UI avanzata per visualizzazione errori PHP
 * 
 * Integra Grid.js per tabella avanzata con filtri, ricerca, export
 */
class LogViewer
{
    /**
     * Enqueue CSS e JS per Log Viewer
     */
    public static function enqueue_assets(): void
    {
        // Determina plugin directory
        $plugin_dir = dirname(dirname(dirname(dirname(__DIR__))));
        $plugin_file = $plugin_dir . '/revious-microdata.php';
        if (!file_exists($plugin_file)) {
            $plugin_file = $plugin_dir . '/gik25-microdata.php';
        }
        
        // Versione basata su filemtime (cache busting)
        $css_file = $plugin_dir . '/assets/css/log-viewer.css';
        $css_version = file_exists($css_file) ? filemtime($css_file) : '1.0.0';
        
        $js_file = $plugin_dir . '/assets/js/log-viewer.js';
        $js_version = file_exists($js_file) ? filemtime($js_file) : '1.0.0';
        
        // Grid.js CSS (CDN)
        wp_enqueue_style(
            'gridjs',
            'https://cdn.jsdelivr.net/npm/gridjs/dist/theme/mermaid.min.css',
            [],
            null
        );
        
        // Log Viewer CSS
        wp_enqueue_style(
            'gik25-log-viewer',
            plugins_url('assets/css/log-viewer.css', $plugin_file),
            ['gridjs'],
            $css_version
        );
        
        // Grid.js JS (CDN)
        wp_enqueue_script(
            'gridjs',
            'https://cdn.jsdelivr.net/npm/gridjs/dist/gridjs.umd.js',
            [],
            null,
            true
        );
        
        // Log Viewer JS
        wp_enqueue_script(
            'gik25-log-viewer',
            plugins_url('assets/js/log-viewer.js', $plugin_file),
            ['gridjs'],
            $js_version,
            true
        );
        
        // Localizza script con dati PHP
        $rest_url = rest_url('gik25/v1/logs');
        $nonce = wp_create_nonce('wp_rest');
        
        wp_localize_script('gik25-log-viewer', 'logViewerData', [
            'restUrl' => $rest_url,
            'nonce' => $nonce,
        ]);
    }
    
    /**
     * Render pagina Log Viewer
     */
    public static function render_page(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('Non hai i permessi per accedere a questa pagina.'));
        }
        
        // Verifica che REST API sia disponibile
        $rest_url = rest_url('gik25/v1/logs');
        $nonce = wp_create_nonce('wp_rest');
        
        // Debug: verifica che l'endpoint sia registrato (solo se REST API è disponibile)
        $endpoint_exists = false;
        if (function_exists('rest_get_server')) {
            try {
                $rest_server = rest_get_server();
                if ($rest_server) {
                    $rest_routes = $rest_server->get_routes();
                    $endpoint_exists = isset($rest_routes['/gik25/v1/logs']);
                    
                    // Se l'endpoint non esiste, logga per debug
                    if (!$endpoint_exists && defined('WP_DEBUG') && WP_DEBUG && WP_DEBUG_LOG) {
                        error_log('LogViewer: Endpoint REST /gik25/v1/logs non trovato.');
                    }
                }
            } catch (\Throwable $e) {
                // Ignora errori durante la verifica
                if (defined('WP_DEBUG') && WP_DEBUG && WP_DEBUG_LOG) {
                    error_log('LogViewer: Errore durante verifica endpoint: ' . $e->getMessage());
                }
            }
        }
        ?>
        <div class="log-viewer-container">
            <h2>Log Viewer - PHP Errors</h2>
            <p>Visualizzazione avanzata degli errori PHP con filtri, ricerca e export. I dati vengono caricati in tempo reale quando modifichi i filtri.</p>
            
            <!-- Toolbar con filtri e export -->
            <div class="log-viewer-toolbar">
                <div>
                    <!-- Filtri -->
                    <div class="log-viewer-filter-group">
                        <label for="filter-severity">Severity:</label>
                        <select id="filter-severity" multiple>
                            <option value="fatal">Fatal</option>
                            <option value="error">Error</option>
                            <option value="warning" selected>Warning</option>
                            <option value="info">Info</option>
                        </select>
                        <small>Ctrl+Click per selezioni multiple</small>
                    </div>
                    
                    <div class="log-viewer-filter-group">
                        <label for="filter-file">File (pattern):</label>
                        <input type="text" id="filter-file" placeholder="es. HealthChecker.php">
                    </div>
                    
                    <div class="log-viewer-filter-group">
                        <label for="filter-context">Contesto:</label>
                        <select id="filter-context" multiple>
                            <option value="wp_cli">WP-CLI</option>
                            <option value="ajax">AJAX</option>
                            <option value="wp_cron">WP-CRON</option>
                            <option value="frontend">Frontend</option>
                            <option value="backend">Backend</option>
                            <option value="rest_api">REST API</option>
                        </select>
                    </div>
                    
                    <div class="log-viewer-actions">
                        <label>Azioni:</label>
                        <button type="button" id="btn-apply-filters" class="button button-primary">Applica Filtri</button>
                        <button type="button" id="btn-reset-filters" class="button">Reset</button>
                        <button type="button" id="btn-export-csv" class="button">Export CSV</button>
                        <button type="button" id="btn-export-json" class="button">Export JSON</button>
                    </div>
                </div>
                
                <!-- Info caricamento -->
                <div id="log-viewer-status">
                    <span id="log-viewer-loading">⏳ Caricamento...</span>
                    <span id="log-viewer-info"></span>
                </div>
            </div>
            
            <!-- Container Grid.js -->
            <div id="log-viewer-grid"></div>
        </div>
        <?php
    }
}

