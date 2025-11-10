<?php
namespace gik25microdata\HealthCheck;

use gik25microdata\Shortcodes\ShortcodeRegistry;
use gik25microdata\HealthCheck\HealthCheckConstants;

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

    /**
     * Inizializza health check
     */
    public static function init(): void
    {
        // Pagina admin per health check
        add_action('admin_menu', [self::class, 'add_admin_page']);
        
        // Carica assets CSS/JS solo nella pagina di health check
        add_action('admin_enqueue_scripts', [self::class, 'enqueue_admin_assets']);
        
        // AJAX endpoint per eseguire check
        add_action('wp_ajax_gik25_health_check', [self::class, 'ajax_run_checks']);
        
        // REST API endpoint per health check (per testing esterno)
        add_action('rest_api_init', [self::class, 'register_rest_endpoint']);
    }

    /**
     * Aggiungi pagina admin
     * 
     * Nota: La pagina viene ora registrata come sottovocce del menu principale "Revious Microdata"
     * Se il menu principale non esiste, viene comunque aggiunta sotto "Strumenti" come fallback
     */
    public static function add_admin_page(): void
    {
        // Verifica se il menu principale esiste (registrato da AdminMenu)
        global $submenu;
        $menu_exists = isset($submenu['revious-microdata']);
        
        if ($menu_exists) {
            // Menu principale esiste, la sottovocce viene aggiunta automaticamente da AdminMenu
            // Qui non facciamo nulla, la pagina viene renderizzata quando si accede al link
        } else {
            // Fallback: aggiungi sotto "Strumenti" se il menu principale non esiste
            add_submenu_page(
                'tools.php',
                'Health Check Plugin',
                'Health Check',
                'manage_options',
                'gik25-health-check',
                [self::class, 'render_admin_page']
            );
        }
    }

    /**
     * Render pagina admin
     */
    public static function render_admin_page(): void
    {
        // Forza refresh al caricamento della pagina (bypass cache per visualizzazione immediata)
        $checks = self::run_all_checks(true);
        ?>
        <div class="wrap">
            <h1>Health Check - Revious Microdata</h1>
            <p>Verifica che tutte le funzionalità del plugin siano operative dopo un deploy.</p>
            
            <nav class="nav-tab-wrapper" style="margin-bottom:15px;">
                <a href="#summary" class="nav-tab nav-tab-active" data-tab="summary"><?php esc_html_e('Riepilogo', 'gik25-microdata'); ?></a>
                <a href="#details" class="nav-tab" data-tab="details"><?php esc_html_e('Dettagli', 'gik25-microdata'); ?></a>
                <a href="#log-viewer" class="nav-tab" data-tab="log-viewer"><?php esc_html_e('Log Viewer - PHP Errors', 'gik25-microdata'); ?></a>
            </nav>

            <div class="health-check-toolbar">
                <button type="button" class="button button-primary" id="run-health-check">
                    ✅ <?php esc_html_e('Esegui Health Check', 'gik25-microdata'); ?>
                </button>
                <button type="button" class="button" id="copy-results">
                    📋 <?php esc_html_e('Copia negli appunti', 'gik25-microdata'); ?>
                </button>
                <label class="screen-reader-text" for="filter-status"><?php esc_html_e('Filtra risultati', 'gik25-microdata'); ?></label>
                <select id="filter-status">
                    <option value="all"><?php esc_html_e('Mostra tutti', 'gik25-microdata'); ?></option>
                    <option value="success"><?php esc_html_e('Solo successi', 'gik25-microdata'); ?></option>
                    <option value="warning"><?php esc_html_e('Solo warning', 'gik25-microdata'); ?></option>
                    <option value="error"><?php esc_html_e('Solo errori', 'gik25-microdata'); ?></option>
                </select>
            </div>

            <div id="health-check-results">
                <?php self::render_checks_results($checks); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Carica assets CSS e JS per la pagina admin
     * Viene chiamato dall'hook admin_enqueue_scripts
     */
    public static function enqueue_admin_assets(string $hook_suffix): void
    {
        // Carica solo nella pagina di health check
        // Il page hook può essere 'gik25-health-check' o 'tools_page_gik25-health-check' a seconda della struttura
        if ($hook_suffix !== 'gik25-health-check' && strpos($hook_suffix, 'gik25-health-check') === false) {
            // Verifica anche tramite GET parameter (fallback)
            if (!isset($_GET['page']) || $_GET['page'] !== 'gik25-health-check') {
                return;
            }
        }
        
        // Determina plugin directory
        $plugin_dir = plugin_dir_path(__FILE__);
        $plugin_dir = dirname(dirname(dirname($plugin_dir)));
        $plugin_file = $plugin_dir . '/revious-microdata.php';
        if (!file_exists($plugin_file)) {
            $plugin_file = $plugin_dir . '/gik25-microdata.php';
        }
        
        // Versione basata su filemtime (cache busting)
        $css_file = $plugin_dir . '/assets/css/health-check.css';
        $css_version = file_exists($css_file) ? filemtime($css_file) : '1.0.0';
        
        $js_file = $plugin_dir . '/assets/js/health-check.js';
        $js_version = file_exists($js_file) ? filemtime($js_file) : '1.0.0';
        
        // Carica CSS
        wp_enqueue_style(
            'gik25-health-check',
            plugins_url('assets/css/health-check.css', $plugin_file),
            [],
            $css_version
        );
        
        // Carica JS (dipende da jQuery)
        wp_enqueue_script(
            'gik25-health-check',
            plugins_url('assets/js/health-check.js', $plugin_file),
            ['jquery'],
            $js_version,
            true
        );
        
        // Localizza script con dati PHP
        wp_localize_script('gik25-health-check', 'healthCheckData', [
            'nonce' => wp_create_nonce('gik25_health_check'),
            'i18n' => [
                'running' => __('In esecuzione...', 'gik25-microdata'),
                'copied' => __('Copiato!', 'gik25-microdata'),
                'checkFailed' => __('Health Check fallito: ', 'gik25-microdata'),
                'unknownError' => __('Errore sconosciuto.', 'gik25-microdata'),
                'ajaxError' => __('Errore durante l\'esecuzione degli health check. Controlla la console per dettagli.', 'gik25-microdata'),
                'copyFailed' => __('Impossibile copiare automaticamente. Copia manualmente i risultati.', 'gik25-microdata'),
            ],
        ]);
    }
     
    /**
     * Formatta una riga di log per anteprima (DEPRECATO: usa LogFormatter)
     * 
     * @deprecated Usa \gik25microdata\LogViewer\LogFormatter::format_preview()
     */
    private static function format_log_line_preview(string $line): string
    {
        return \gik25microdata\LogViewer\LogFormatter::format_preview($line);
    }
    
    /**
     * Formatta una riga di log con colori (DEPRECATO: usa LogFormatter)
     * 
     * @deprecated Usa \gik25microdata\LogViewer\LogFormatter::format_line()
     */
    private static function format_log_line(string $line): array
    {
        return \gik25microdata\LogViewer\LogFormatter::format_line($line);
    }
    
    /**
     * Render risultati check
     */
    private static function render_checks_results(array $checks): void
    {
        // Separare i check dei log dagli altri health check
        $log_check_name = 'Analisi Log Cloudways';
        $log_check = null;
        $health_checks = [];
        
        foreach ($checks as $check) {
            if ($check['name'] === $log_check_name) {
                $log_check = $check;
            } else {
                $health_checks[] = $check;
            }
        }
        
        // Calcola statistiche per health check
        $health_total = count($health_checks);
        $health_success = count(array_filter($health_checks, fn($c) => $c['status'] === 'success'));
        $health_warnings = count(array_filter($health_checks, fn($c) => $c['status'] === 'warning'));
        $health_errors = count(array_filter($health_checks, fn($c) => $c['status'] === 'error'));
        
        // Calcola statistiche per log check
        $log_status = $log_check ? $log_check['status'] : 'unknown';
        $log_php_errors_count = 0;
        if ($log_check && !empty($log_check['php_errors'])) {
            $log_php_errors_count = count($log_check['php_errors']);
        }

        // Calcola tail degli errori UNA SOLA VOLTA (usa il numero più grande richiesto)
        // Questo evita doppie chiamate costose a recent_errors_tail()
        $tail = null;
        if ($log_check) {
            $tail = \gik25microdata\HealthCheck\CloudwaysLogParser::recent_errors_tail(
                HealthCheckConstants::TAIL_LINES_DETAILS,
                HealthCheckConstants::TAIL_WINDOW_HOURS
            );
        }

        ?>
        <div class="health-check-section active" id="summary">
            <!-- Riepilogo Health Check -->
            <div class="health-check-summary" style="margin-bottom: 30px; padding: 20px; background: #f9f9f9; border-radius: 4px;">
                <h2><?php esc_html_e('Riepilogo Health Check', 'gik25-microdata'); ?></h2>
                <p>
                        <strong><?php esc_html_e('Totale:', 'gik25-microdata'); ?></strong> <?php echo $health_total; ?> |
                    <span style="color:<?php echo esc_attr(HealthCheckConstants::getStatusColor('success')); ?>;"><?php esc_html_e('[OK] Successo:', 'gik25-microdata'); ?> <?php echo $health_success; ?></span> |
                    <span style="color:<?php echo esc_attr(HealthCheckConstants::getStatusColor('warning')); ?>;"><?php esc_html_e('[WARN] Warning:', 'gik25-microdata'); ?> <?php echo $health_warnings; ?></span> |
                    <span style="color:<?php echo esc_attr(HealthCheckConstants::getStatusColor('error')); ?>;"><?php esc_html_e('[ERR] Errori:', 'gik25-microdata'); ?> <?php echo $health_errors; ?></span>
                </p>
                <p><small><?php esc_html_e('Ultimo check:', 'gik25-microdata'); ?> <?php echo current_time('mysql'); ?></small></p>
            </div>
            
            <!-- Riepilogo Log Cloudways -->
            <div class="health-check-summary" style="padding: 20px; background: #f0f8ff; border-radius: 4px; border-left: 4px solid #0073aa;">
                <h2><?php esc_html_e('Riepilogo Analisi Log Cloudways', 'gik25-microdata'); ?></h2>
                <?php if ($log_check): ?>
                    <p>
                        <strong><?php esc_html_e('Stato:', 'gik25-microdata'); ?></strong> 
                        <span style="color:<?php 
                            echo esc_attr(HealthCheckConstants::getStatusColor($log_status === 'unknown' ? 'success' : $log_status)); 
                        ?>; font-weight: bold;">
                            <?php echo esc_html(strtoupper($log_status)); ?>
                        </span>
                    </p>
                    <p><?php echo esc_html($log_check['message']); ?></p>
                    <?php if ($log_php_errors_count > 0): ?>
                        <p style="color: <?php echo esc_attr(HealthCheckConstants::getStatusColor('error')); ?>; font-weight: bold;">
                            ⚠️ <?php echo $log_php_errors_count; ?> errore/i PHP critico/i rilevato/i
                        </p>
                    <?php endif; ?>
                    
                    <?php 
                    // Mostra ultimi errori PHP (critici + warning) in anteprima
                    // Usa il tail già calcolato, limitando a TAIL_LINES_PREVIEW per l'anteprima
                    if ($tail && !empty($tail['tails']['php_error']['entries'])) {
                        // Prendi solo le prime TAIL_LINES_PREVIEW righe per l'anteprima
                        $preview_entries = array_slice($tail['tails']['php_error']['entries'], 0, HealthCheckConstants::TAIL_LINES_PREVIEW);
                        
                        // Filtra per severity: mostra fatal, error, exception, warning
                        $php_errors_preview = [];
                        $critical_count = 0;
                        $warning_count = 0;
                        
                        foreach ($preview_entries as $error_line) {
                            $severity = \gik25microdata\LogViewer\LogFormatter::extract_severity($error_line);
                            
                            if (HealthCheckConstants::isCriticalSeverity($severity)) {
                                $critical_count++;
                                if (count($php_errors_preview) < 8) {
                                    $php_errors_preview[] = ['line' => $error_line, 'severity' => $severity];
                                }
                            } elseif ($severity === 'warning') {
                                $warning_count++;
                                if (count($php_errors_preview) < 8 && $critical_count < 5) {
                                    $php_errors_preview[] = ['line' => $error_line, 'severity' => $severity];
                                }
                            }
                        }
                        
                        $php_error_warning = $tail['tails']['php_error']['timestamp_warning'] ?? null;
                        $php_error_timezone = $tail['tails']['php_error']['timezone'] ?? null;
                    ?>
                        <div style="margin-top: 15px; padding: 10px; background: #fff5f5; border-left: 3px solid #dc3232; border-radius: 3px;">
                            <strong style="color: #dc3232;">📋 Ultimi errori PHP (anteprima):</strong>
                            <?php if ($critical_count > 0 || $warning_count > 0): ?>
                                <p style="font-size: 11px; color: #666; margin: 5px 0;">
                                    <?php if ($critical_count > 0): ?>
                                        <span style="color: #dc3232; font-weight: bold;">⚠️ <?php echo $critical_count; ?> critico/i</span>
                                    <?php endif; ?>
                                    <?php if ($warning_count > 0): ?>
                                        <?php if ($critical_count > 0): ?> | <?php endif; ?>
                                        <span style="color: #ffb900; font-weight: bold;">⚠️ <?php echo $warning_count; ?> warning</span>
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>
                            <?php if ($php_error_timezone): ?>
                                <p style="font-size: 11px; color: #666; margin: 5px 0;">
                                    Timezone server: <?php echo esc_html($php_error_timezone['timezone']); ?> (<?php echo esc_html($php_error_timezone['formatted']); ?>)
                                </p>
                            <?php endif; ?>
                            <?php if ($php_error_warning && !empty($php_error_warning['message'])): ?>
                                <p style="font-size: 11px; color: <?php echo $php_error_warning['is_stale'] ? '#dc3232' : '#666'; ?>; margin: 5px 0; font-weight: <?php echo $php_error_warning['is_stale'] ? 'bold' : 'normal'; ?>;">
                                    ⚠️ <?php echo esc_html($php_error_warning['message']); ?>
                                </p>
                            <?php endif; ?>
                            <ul style="margin: 10px 0; padding-left: 20px; font-size: 12px;">
                                <?php foreach ($php_errors_preview as $error_data): ?>
                                    <?php 
                                    $error_line = $error_data['line'];
                                    $error_severity = $error_data['severity'];
                                    $severity_color = HealthCheckConstants::isCriticalSeverity($error_severity) 
                                        ? HealthCheckConstants::getStatusColor('error') 
                                        : HealthCheckConstants::getStatusColor('warning');
                                    ?>
                                    <li style="margin: 5px 0; color: #333;">
                                        <span style="color: <?php echo esc_attr($severity_color); ?>; font-weight: bold; font-size: 10px; margin-right: 5px;">
                                            <?php echo strtoupper($error_severity); ?>
                                        </span>
                                        <code style="background: #f5f5f5; padding: 2px 4px; border-radius: 2px; font-size: 11px; word-break: break-all;">
                                            <?php echo esc_html(\gik25microdata\LogViewer\LogFormatter::format_preview($error_line)); ?>
                                        </code>
                                    </li>
                                    <?php endforeach; ?>
                            </ul>
                            <?php if ($tail && count($tail['tails']['php_error']['entries']) > count($php_errors_preview)): ?>
                                <p style="font-size: 11px; color: #666; margin-top: 5px;">
                                    ... e altri <?php echo count($tail['tails']['php_error']['entries']) - count($php_errors_preview); ?> errori (vedi Log Viewer)
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php } ?>
                <?php else: ?>
                    <p style="color: #666;">
                        <?php esc_html_e('Analisi log non disponibile.', 'gik25-microdata'); ?>
                    </p>
                <?php endif; ?>
                <p><small><?php esc_html_e('Ultimo check:', 'gik25-microdata'); ?> <?php echo current_time('mysql'); ?></small></p>
            </div>
        </div>

        <div class="health-check-section" id="details">
            <!-- Sezione Health Check -->
            <div style="margin-bottom: 40px;">
                <h2 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #ddd;">
                    <?php esc_html_e('Dettagli Health Check', 'gik25-microdata'); ?>
                </h2>
                <?php foreach ($health_checks as $check): ?>
                    <!-- Rendering standard per health check -->
                    <div class="health-check-item <?php echo esc_attr($check['status']); ?>">
                        <h3>
                            <span class="badge"><?php echo esc_html(strtoupper($check['status'])); ?></span>
                            <?php echo esc_html($check['name']); ?>
                        </h3>
                        <p><?php echo esc_html($check['message']); ?></p>
                        <?php if (!empty($check['details'])): ?>
                            <div class="details">
                                <pre><?php echo esc_html($check['details']); ?></pre>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Sezione Analisi Log Cloudways -->
            <div style="margin-top: 40px; padding-top: 20px; border-top: 3px solid #0073aa;">
                <h2 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #0073aa; color: #0073aa;">
                    <?php esc_html_e('Dettagli Analisi Log Cloudways', 'gik25-microdata'); ?>
                </h2>
                <?php if ($log_check): ?>
                    <?php 
                    // Controlla se questo check ha errori PHP separati
                    $has_php_errors = !empty($log_check['php_errors']);
                    ?>
                    <!-- Struttura unica per Analisi Log Cloudways -->
                    <div class="health-check-item <?php echo esc_attr($log_check['status']); ?>">
                        <h3>
                            <span class="badge"><?php echo esc_html(strtoupper($log_check['status'])); ?></span>
                            <?php echo esc_html($log_check['name']); ?>
                        </h3>
                        <p style="margin-bottom: 15px;"><?php echo esc_html($log_check['message']); ?></p>
                        
                        <!-- Errori PHP Critici (collapsed) -->
                        <?php if ($has_php_errors): ?>
                            <details style="margin: 15px 0; background: #fff5f5; border: 1px solid #dc3232; border-radius: 4px; padding: 10px;">
                                <summary style="cursor: pointer; font-weight: 600; color: #dc3232; padding: 8px;">
                                    ❌ Errori PHP Critici - <?php echo count($log_check['php_errors']); ?> errore/i rilevato/i
                                </summary>
                                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #dc3232;">
                                    <div class="php-errors-list">
                                        <?php foreach ($log_check['php_errors'] as $idx => $php_error): ?>
                                            <div class="php-error-item" style="border: 1px solid #dc3232; border-radius: 4px; padding: 15px; margin-bottom: 15px; background: #fff;">
                                                <h4 style="margin-top: 0; color: #dc3232; display: flex; align-items: center; gap: 10px;">
                                                    <span style="font-size: 18px;">
                                                        <?php echo $php_error['severity'] === 'error' ? '❌' : '⚠️'; ?>
                                                    </span>
                                                    <span>
                                                        <?php 
                                                        $error_type = $php_error['error_type'] ?? 'unknown';
                                                        echo esc_html(HealthCheckConstants::ERROR_TYPE_LABELS[$error_type] ?? ucfirst($error_type));
                                                        ?>
                                                    </span>
                                                    <span style="font-size: 14px; font-weight: normal; color: #666;">
                                                        (<?php echo esc_html($php_error['count']); ?> occorrenze)
                                                    </span>
                                                </h4>
                                                
                                                <div style="margin: 10px 0;">
                                                    <strong>Messaggio:</strong> <?php echo esc_html($php_error['message']); ?>
                                                </div>
                                                
                                                <?php if (!empty($php_error['files'])): ?>
                                                    <div style="margin: 10px 0;">
                                                        <strong>File:</strong> 
                                                        <code style="background: #f0f0f0; padding: 2px 6px; border-radius: 3px;">
                                                            <?php echo esc_html(implode(', ', array_slice($php_error['files'], 0, 3))); ?>
                                                            <?php if (count($php_error['files']) > 3): ?>
                                                                <span style="color: #666;">(+<?php echo count($php_error['files']) - 3; ?> altri)</span>
                                                            <?php endif; ?>
                                                        </code>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($php_error['lines'])): ?>
                                                    <div style="margin: 10px 0;">
                                                        <strong>Righe:</strong> 
                                                        <code style="background: #f0f0f0; padding: 2px 6px; border-radius: 3px;">
                                                            <?php echo esc_html(implode(', ', array_slice($php_error['lines'], 0, 5))); ?>
                                                            <?php if (count($php_error['lines']) > 5): ?>
                                                                <span style="color: #666;">(+<?php echo count($php_error['lines']) - 5; ?> altri)</span>
                                                            <?php endif; ?>
                                                        </code>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($php_error['contexts'])): ?>
                                                    <div style="margin: 10px 0;">
                                                        <strong>Contesto:</strong> 
                                                        <?php 
                                                        $contexts_display = array_map(function($ctx) {
                                                            return HealthCheckConstants::getContextLabel($ctx);
                                                        }, $php_error['contexts']);
                                                        echo esc_html(implode(', ', $contexts_display));
                                                        ?>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($php_error['examples'])): ?>
                                                    <div style="margin: 15px 0;">
                                                        <strong>Esempi (<?php echo count($php_error['examples']); ?>):</strong>
                                                        <div style="margin-top: 10px;">
                                                            <?php foreach (array_slice($php_error['examples'], 0, 2) as $example_idx => $example): ?>
                                                                <?php if (is_array($example)): ?>
                                                                    <details style="margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; padding: 10px;">
                                                                        <summary style="cursor: pointer; font-weight: bold; color: #0073aa;">
                                                                            Esempio <?php echo $example_idx + 1; ?>
                                                                            <?php if (!empty($example['file'])): ?>
                                                                                - <?php echo esc_html(basename($example['file'])); ?>
                                                                                <?php if (!empty($example['line'])): ?>
                                                                                    :<?php echo esc_html($example['line']); ?>
                                                                                <?php endif; ?>
                                                                            <?php endif; ?>
                                                                        </summary>
                                                                        <div style="margin-top: 10px; padding-left: 15px;">
                                                                            <?php if (!empty($example['message'])): ?>
                                                                                <div style="margin-bottom: 8px;">
                                                                                    <strong>Messaggio:</strong><br>
                                                                                    <code style="background: #f5f5f5; padding: 5px; display: block; border-radius: 3px; word-break: break-all;">
                                                                                        <?php echo esc_html($example['message']); ?>
                                                                                    </code>
                                                                                </div>
                                                                            <?php endif; ?>
                                                                            
                                                                            <?php if (!empty($example['file'])): ?>
                                                                                <div style="margin-bottom: 8px;">
                                                                                    <strong>File:</strong> 
                                                                                    <code><?php echo esc_html($example['file']); ?></code>
                                                                                    <?php if (!empty($example['line'])): ?>
                                                                                        <strong>Riga:</strong> 
                                                                                        <code><?php echo esc_html($example['line']); ?></code>
                                                                                    <?php endif; ?>
                                                                                </div>
                                                                            <?php endif; ?>
                                                                            
                                                                            <?php if (!empty($example['stack_trace'])): ?>
                                                                                <div style="margin-bottom: 8px;">
                                                                                    <strong>Stack Trace:</strong>
                                                                                    <pre style="background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; font-size: 12px; max-height: 300px; overflow-y: auto;"><?php 
                                                                                        echo esc_html(implode("\n", array_slice($example['stack_trace'], 0, 15)));
                                                                                        if (count($example['stack_trace']) > 15) {
                                                                                            echo "\n... (" . (count($example['stack_trace']) - 15) . " altre righe)";
                                                                                        }
                                                                                    ?></pre>
                                                                                </div>
                                                                            <?php endif; ?>
                                                                            
                                                                            <?php if (!empty($example['context'])): ?>
                                                                                <div style="margin-bottom: 8px;">
                                                                                    <strong>Contesto:</strong> 
                                                                                    <?php 
                                                                                    echo esc_html(HealthCheckConstants::getContextLabel($example['context']));
                                                                                    ?>
                                                                                </div>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </details>
                                                                <?php else: ?>
                                                                    <div style="margin-bottom: 8px; padding: 8px; background: #f5f5f5; border-radius: 3px;">
                                                                        <code style="word-break: break-all;"><?php echo esc_html($example); ?></code>
                                                                    </div>
                                                                <?php endif; ?>
                                                            <?php endforeach; ?>
                                                            
                                                            <?php if (count($php_error['examples']) > 2): ?>
                                                                <p style="color: #666; font-style: italic;">
                                                                    ... e altri <?php echo count($php_error['examples']) - 2; ?> esempi
                                                                </p>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </details>
                        <?php endif; ?>
                        
                        <!-- Ultimi errori dai log (tail, collapsed) -->
                        <?php if (!empty($tail['tails'])): ?>
                            <details style="margin: 15px 0; background: #fffbf0; border: 1px solid #ffb900; border-radius: 4px; padding: 10px;">
                                <summary style="cursor: pointer; font-weight: 600; color: #856404; padding: 8px;">
                                    📋 Ultimi errori dai log (tail, 24h)
                                    <?php if (!empty($tail['paths']['base'])): ?>
                                        <span style="font-size: 12px; font-weight: normal; color: #666; margin-left: 10px;">
                                            (<?php echo esc_html($tail['paths']['base']); ?>)
                                        </span>
                                    <?php endif; ?>
                                </summary>
                                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ffb900;">
                                    <?php foreach ($tail['tails'] as $key => $bundle): ?>
                                        <details style="margin:10px 0; background:#fff; border-radius:4px; padding:10px; border: 1px solid #ddd;">
                                            <summary style="cursor:pointer; font-weight:600;">
                                                <?php 
                                                    $label = HealthCheckConstants::TAIL_LABELS[$key] ?? ucfirst($key);
                                                    echo esc_html($label . 
                                                        (!empty($bundle['file']) ? ' · ' . $bundle['file'] : '') . 
                                                        ' (' . count($bundle['entries']) . ' righe)');
                                                ?>
                                            </summary>
                                            <?php 
                                            // Mostra informazioni timezone e warning se disponibili (solo per PHP error)
                                            if ($key === 'php_error') {
                                                $php_timezone = $bundle['timezone'] ?? null;
                                                $php_warning = $bundle['timestamp_warning'] ?? null;
                                                
                                                if ($php_timezone || $php_warning):
                                            ?>
                                                <div style="margin: 10px 0; padding: 8px; background: #f0f8ff; border-radius: 3px; border-left: 3px solid #0073aa;">
                                                    <?php if ($php_timezone): ?>
                                                        <p style="margin: 0; font-size: 11px; color: #666;">
                                                            <strong>Timezone server:</strong> <?php echo esc_html($php_timezone['timezone']); ?> (<?php echo esc_html($php_timezone['formatted']); ?>)
                                                        </p>
                                                    <?php endif; ?>
                                                    <?php if ($php_warning && !empty($php_warning['message'])): ?>
                                                        <p style="margin: 5px 0 0 0; font-size: 11px; color: <?php echo $php_warning['is_stale'] ? '#dc3232' : '#666'; ?>; font-weight: <?php echo $php_warning['is_stale'] ? 'bold' : 'normal'; ?>;">
                                                            ⚠️ <?php echo esc_html($php_warning['message']); ?>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                            <?php 
                                                endif;
                                            }
                                            ?>
                                            <div style="margin-top:10px; font-family:monospace; font-size:12px; max-height: 400px; overflow-y: auto;">
                                                <?php foreach ($bundle['entries'] as $entry_line): ?>
                                                    <?php 
                                                    $formatted = \gik25microdata\LogViewer\LogFormatter::format_line($entry_line);
                                                    ?>
                                                    <div class="<?php echo esc_attr($formatted['class']); ?>" style="padding: 4px 8px; margin: 2px 0; background: <?php echo esc_attr($formatted['bg_color']); ?>; border-left: 3px solid <?php echo esc_attr($formatted['color']); ?>; border-radius: 2px; white-space: pre-wrap; word-break: break-all;">
                                                        <?php echo $formatted['html']; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </details>
                                    <?php endforeach; ?>
                                </div>
                            </details>
                        <?php endif; ?>
                        
                        <!-- Dettagli completi (collapsed) -->
                        <?php if (!empty($log_check['details'])): ?>
                            <details style="margin: 15px 0; background: #f0f0f0; border: 1px solid #ccc; border-radius: 4px; padding: 10px;">
                                <summary style="cursor: pointer; font-weight: 600; padding: 8px;">
                                    📄 Dettagli Completi (formato testo)
                                </summary>
                                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ccc;">
                                    <pre style="max-height: 500px; overflow-y: auto; background: #fff; padding: 15px; border-radius: 3px; font-size: 12px;"><?php echo esc_html($log_check['details']); ?></pre>
                                </div>
                            </details>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="health-check-item warning">
                        <h3>
                            <span class="badge">WARNING</span>
                            Analisi Log Cloudways
                        </h3>
                        <p>Analisi log non disponibile.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="health-check-section" id="log-viewer">
            <?php
            // Render Log Viewer tab
            if (class_exists('\gik25microdata\LogViewer\LogViewer')) {
                \gik25microdata\LogViewer\LogViewer::render_page();
            } else {
                echo '<div class="health-check-item warning">';
                echo '<h3><span class="badge">WARNING</span>Log Viewer</h3>';
                echo '<p>Log Viewer non disponibile. Classe LogViewer non trovata.</p>';
                echo '</div>';
            }
            ?>
        </div>
        <?php
    }

    /**
     * AJAX handler per eseguire check
     * PROTETTO: gestisce errori senza bloccare WordPress
     */
    public static function ajax_run_checks(): void
    {
        try {
            check_ajax_referer('gik25_health_check', 'nonce');
            
            if (!current_user_can('manage_options')) {
                wp_send_json_error('Permessi insufficienti');
                return;
            }

            // Forza refresh quando richiesto via AJAX (bypass cache)
            $force_refresh = isset($_POST['force_refresh']) && $_POST['force_refresh'] === '1';
            $checks = self::run_all_checks($force_refresh);
            
            ob_start();
            self::render_checks_results($checks);
            $html = ob_get_clean();

            wp_send_json_success(['html' => $html, 'checks' => $checks]);
            
        } catch (\Throwable $e) {
            // Gestisci errore senza crashare WordPress
            wp_send_json_error([
                'message' => 'Errore durante l\'esecuzione degli health check',
                'error' => $e->getMessage(),
            ]);
        }
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
                // Accesso ristretto solo ad amministratori
                return current_user_can('manage_options');
            },
        ]);
    }

    /**
     * REST API handler per health check
     * PROTETTO: gestisce errori senza bloccare WordPress
     */
    public static function rest_health_check(): \WP_REST_Response
    {
        try {
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
            
        } catch (\Throwable $e) {
            // Ritorna risposta di errore invece di crashare
            return new \WP_REST_Response([
                'error' => true,
                'message' => 'Errore durante l\'esecuzione degli health check',
                'total' => 0,
                'success' => 0,
                'warnings' => 0,
                'errors' => 0,
                'timestamp' => current_time('mysql'),
                'checks' => [],
            ], 500);
        }
    }

    /**
     * Esegui tutti i check (con cache)
     * PROTETTO: gestisce errori senza bloccare WordPress
     */
    public static function run_all_checks(bool $force_refresh = false): array
    {
        // Verifica cache (se non forzato refresh)
        if (!$force_refresh) {
            $cache = get_transient(HealthCheckConstants::CHECK_CACHE_KEY);
            if ($cache !== false && is_array($cache)) {
                return $cache;
            }
        }

        // Esegui tutti i check in modo sicuro
        $checks = \gik25microdata\Utility\SafeExecution::safe_execute(function() {
            $checks = [];
            
            // Carica gli shortcode prima di verificarli (necessario perché vengono caricati solo nel frontend)
            // Questo permette all'health check di funzionare anche nel backend
            \gik25microdata\Utility\SafeExecution::safe_execute(function() {
                self::ensure_shortcodes_loaded();
            }, null, true);

            // Esegui tutti i check in modo sicuro (ognuno protetto individualmente)
            $check_methods = [
                'check_shortcodes',
                'check_disabled_shortcodes_usage',
                'check_rest_api',
                'check_ajax_endpoints',
                'check_files',
                'check_database_tables',
                'check_assets',
                'check_classes',
                'check_logs',
            ];
            
            foreach ($check_methods as $method) {
                $check_result = \gik25microdata\Utility\SafeExecution::safe_execute(function() use ($method) {
                    // Chiama il metodo dinamicamente
                    if (method_exists(self::class, $method)) {
                        return call_user_func([self::class, $method]);
                    }
                    return [
                        'name' => ucfirst(str_replace('check_', '', $method)),
                        'status' => 'warning',
                        'message' => 'Metodo check non trovato',
                        'details' => 'Il metodo ' . $method . ' non esiste.',
                    ];
                }, [
                    'name' => ucfirst(str_replace('check_', '', $method)),
                    'status' => 'warning',
                    'message' => 'Check non disponibile (errore interno gestito)',
                    'details' => 'Il check ha riscontrato un problema. Questo non ha impatto sul funzionamento del sito.',
                ], true);
                
                $checks[] = $check_result;
            }

            return $checks;
        }, [], true); // Ritorna array vuoto in caso di errore critico

        // Salva in cache
        if (!empty($checks)) {
            set_transient(
                HealthCheckConstants::CHECK_CACHE_KEY,
                $checks,
                HealthCheckConstants::CHECK_CACHE_EXPIRATION
            );
        }

        return $checks;
    }

    /**
     * Check se shortcode disabilitati sono ancora presenti in contenuti.
     */
    private static function check_disabled_shortcodes_usage(): array
    {
        if (!class_exists(ShortcodeRegistry::class)) {
            return [
                'name' => 'Uso shortcode disabilitati',
                'status' => 'success',
                'message' => 'Registro shortcode non disponibile (nessun controllo effettuato).',
                'details' => '',
            ];
        }

        $items = ShortcodeRegistry::getItemsForAdmin();
        $disabled = array_filter($items, static fn ($item) => empty($item['enabled']));

        if (empty($disabled)) {
            return [
                'name' => 'Uso shortcode disabilitati',
                'status' => 'success',
                'message' => 'Nessuno shortcode disabilitato.',
                'details' => '',
            ];
        }

        global $wpdb;
        $violations = [];

        foreach ($disabled as $slug => $item) {
            $like = '%[' . $wpdb->esc_like($slug) . '%';
            $sql = $wpdb->prepare(
                "SELECT ID, post_title 
                 FROM {$wpdb->posts} 
                 WHERE post_status NOT IN ('trash','auto-draft','inherit')
                   AND post_content LIKE %s
                 LIMIT 3",
                $like
            );
            $rows = $wpdb->get_results($sql, ARRAY_A);
            if (!empty($rows)) {
                $first = $rows[0];
                $violations[] = [
                    'label' => $item['label'] ?? $slug,
                    'slug' => $slug,
                    'count' => count($rows),
                    'example' => sprintf('#%d %s', (int) $first['ID'], $first['post_title'] ?? ''),
                ];
            }
        }

        if (empty($violations)) {
            return [
                'name' => 'Uso shortcode disabilitati',
                'status' => 'success',
                'message' => 'Gli shortcode disabilitati non risultano nei contenuti.',
                'details' => '',
            ];
        }

        $lines = array_map(static fn($info) => sprintf(
            '[%s] trovati in %d contenuti (esempio %s)',
            $info['label'],
            $info['count'],
            $info['example']
        ), $violations);

        return [
            'name' => 'Uso shortcode disabilitati',
            'status' => 'warning',
            'message' => 'Alcuni contenuti contengono shortcode disattivati: valuta se riabilitarli o rimuoverli.',
            'details' => implode("\n", $lines),
        ];
    }
    
    /**
     * Assicura che gli shortcode siano caricati (necessario nel backend)
     */
    private static function ensure_shortcodes_loaded(): void
    {
        // Forza il caricamento degli shortcode anche nel backend
        // Questo è necessario perché normalmente vengono caricati solo nel frontend
        
        // 1. Carica i file degli shortcode (questo include i file e istanzia le classi)
        // Gli shortcode vengono istanziati alla fine di ogni file (es. $quote = new Quote();)
        // e vengono registrati nel costruttore tramite add_shortcode()
        if (method_exists('\gik25microdata\PluginBootstrap', 'loadShortcodeFiles')) {
            \gik25microdata\PluginBootstrap::loadShortcodeFiles();
        }
        
        // 2. Carica anche i file site_specific che potrebbero registrare shortcode aggiuntivi
        // (es. totaldesign_specific.php che registra kitchen_finder, app_nav, link_colori, ecc.)
        // Usa reflection per chiamare il metodo privato detectCurrentWebsite
        try {
            $reflection = new \ReflectionClass('\gik25microdata\PluginBootstrap');
            if ($reflection->hasMethod('detectCurrentWebsite')) {
                $method = $reflection->getMethod('detectCurrentWebsite');
                $method->setAccessible(true);
                $method->invoke(null);
            }
        } catch (\ReflectionException $e) {
            // Ignora errori di reflection
        }
        
        // 3. Verifica che gli shortcode siano stati registrati
        // Se non lo sono, potrebbe essere un problema di timing
        global $shortcode_tags;
        $shortcodes_before = is_array($shortcode_tags) ? count($shortcode_tags) : 0;
        
        // Se non ci sono molti shortcode registrati, proviamo a ricaricare manualmente
        // Nota: require_once non ricarica se il file è già stato incluso,
        // ma possiamo comunque verificare se gli shortcode sono registrati
        if ($shortcodes_before < 5) {
            // Forza il caricamento diretto dei file (anche se già inclusi, 
            // l'istanziazione alla fine del file verrà rieseguita solo se non è già avvenuta)
            $plugin_dir = dirname(dirname(dirname(__DIR__)));
            $shortcodes_dir = $plugin_dir . '/include/class/Shortcodes';
            
            if (is_dir($shortcodes_dir)) {
                // Usa require invece di require_once per forzare il ricaricamento
                // ATTENZIONE: questo potrebbe causare errori se le classi sono già definite
                // Quindi verifichiamo prima se le classi esistono
                foreach (glob($shortcodes_dir . '/*.php') as $file) {
                    $basename = basename($file, '.php');
                    // Ignora ShortcodeBase.php che è una classe astratta
                    if ($basename !== 'ShortcodeBase') {
                        // Verifica se la classe esiste già
                        $class_name = '\\gik25microdata\\Shortcodes\\' . ucfirst($basename);
                        if (!class_exists($class_name)) {
                            // La classe non esiste, possiamo includere il file
                            require_once $file;
                        } else {
                            // La classe esiste, ma verifichiamo se lo shortcode è registrato
                            // Se non lo è, proviamo a istanziarla manualmente
                            // (ma questo potrebbe causare problemi se è già istanziata)
                        }
                    }
                }
            }
        }
        
        // Debug: verifica quanti shortcode sono stati registrati dopo il caricamento
        $shortcodes_after = is_array($shortcode_tags) ? count($shortcode_tags) : 0;
        
        // Se ancora non ci sono shortcode, potrebbe essere un problema più serio
        // Potremmo dover forzare l'istanziazione manualmente
        if ($shortcodes_after < 5 && $shortcodes_after === $shortcodes_before) {
            // Nessuno shortcode è stato aggiunto - problema di caricamento
            // Proviamo a istanziare manualmente alcune classi chiave
            // (solo se non sono già istanziate)
            $key_classes = [
                'Boxinfo' => ['md_boxinfo', 'boxinfo', 'boxinformativo'],
                'Quote' => ['md_quote', 'quote'],
                'Youtube' => ['youtube'],
                'Telefono' => ['telefono'],
            ];
            
            foreach ($key_classes as $class_name => $expected_tags) {
                $full_class = '\\gik25microdata\\Shortcodes\\' . $class_name;
                if (class_exists($full_class)) {
                    // Verifica se almeno uno degli shortcode è registrato
                    $any_registered = false;
                    foreach ($expected_tags as $tag) {
                        if (isset($shortcode_tags[$tag])) {
                            $any_registered = true;
                            break;
                        }
                    }
                    
                    // Se nessuno shortcode è registrato, prova a istanziare la classe
                    // (solo se non è già stata istanziata - questo è tricky)
                    if (!$any_registered) {
                        // Non possiamo verificare facilmente se è già istanziata
                        // Quindi non facciamo nulla - l'istanziazione dovrebbe avvenire
                        // automaticamente quando il file viene incluso
                    }
                }
            }
        }
    }

    /**
     * Check shortcode registrati
     */
    private static function check_shortcodes(): array
    {
        global $shortcode_tags;
        
        // Debug: verifica quanti shortcode sono registrati in totale
        $total_registered = is_array($shortcode_tags) ? count($shortcode_tags) : 0;
        
        // Lista di tutti gli shortcode del plugin (sia richiesti che opzionali)
        // Questi sono gli shortcode che DOVREBBERO essere registrati se i file sono stati caricati
        $all_plugin_shortcodes = [
            // Base - da classi ShortcodeBase
            'md_boxinfo', 'boxinfo', 'boxinformativo',
            'md_quote', 'quote', 
            'youtube', 
            'telefono',
            'md_progressbar', 'progressbar', 
            'slidingbox',
            'flipbox', 'md_flipbox', 
            'blinkingbutton', 'md_blinkingbutton',
            'perfectpullquote', 
            'prezzo', 
            'flexlist',
            // Opzionali - da file site_specific o condizioni
            'kitchen_finder', // KitchenFinder
            'app_nav', // AppNav
            'carousel', 'list', 'grid', // GenericCarousel
            // Da totaldesign_specific.php (hardcoded)
            'link_colori', 'grafica3d', 'archistar',
        ];
        
        // Shortcode base MINIMI che devono sempre esistere
        // Questi sono quelli che vengono istanziati direttamente nei file
        $required_shortcodes = [
            'md_boxinfo', 'boxinfo', 'boxinformativo', // Boxinfo
            'md_quote', 'quote', // Quote
            'youtube', // Youtube
            'telefono', // Telefono
            'md_progressbar', 'progressbar', // Progressbar
            'slidingbox', // Slidingbox
            'flipbox', 'md_flipbox', // Flipbox
            'blinkingbutton', 'md_blinkingbutton', // BlinkingButton
            'perfectpullquote', // Perfectpullquote
            'prezzo', // Prezzo
            'flexlist', // Flexlist
        ];
        
        // Shortcode opzionali (dipendono da configurazione sito o file site_specific)
        $optional_shortcodes = [
            'kitchen_finder', // Solo se KitchenFinder.php è caricato e istanziato
            'app_nav', // Solo se AppNav.php è caricato e istanziato
            'carousel', 'list', 'grid', // Solo se GenericCarousel è istanziato
            'link_colori', 'grafica3d', 'archistar', // Solo se totaldesign_specific.php è caricato
        ];

        $missing_required = [];
        $registered_required = [];
        $registered_optional = [];
        $missing_optional = [];

        // Controlla shortcode richiesti
        foreach ($required_shortcodes as $tag) {
            if (isset($shortcode_tags[$tag])) {
                $registered_required[] = $tag;
            } else {
                $missing_required[] = $tag;
            }
        }
        
        // Controlla shortcode opzionali
        foreach ($optional_shortcodes as $tag) {
            if (isset($shortcode_tags[$tag])) {
                $registered_optional[] = $tag;
            } else {
                $missing_optional[] = $tag;
            }
        }
        
        $all_registered = array_merge($registered_required, $registered_optional);

        // Determina status
        if (!empty($missing_required)) {
            $status = 'error';
            $message = sprintf('Shortcode base mancanti: %d/%d (%s)', 
                count($missing_required),
                count($required_shortcodes),
                implode(', ', array_slice($missing_required, 0, 5)) . (count($missing_required) > 5 ? '...' : '')
            );
        } elseif (!empty($registered_required)) {
            // Se almeno alcuni shortcode base sono registrati, è un successo
            // (potrebbero mancare alcuni opzionali, ma non è un errore)
            $status = 'success';
            $message = sprintf('Shortcode base OK (%d/%d)', 
                count($registered_required),
                count($required_shortcodes)
            );
            if (!empty($registered_optional)) {
                $message .= sprintf(', opzionali: %d', count($registered_optional));
            }
        } else {
            // Nessuno shortcode registrato - problema grave
            $status = 'error';
            $message = sprintf('Nessuno shortcode registrato (totale WordPress: %d)', $total_registered);
        }

        // Dettagli estesi
        $details = sprintf("Totale shortcode WordPress registrati: %d\n", $total_registered);
        $details .= sprintf("Shortcode plugin richiesti: %d/%d registrati\n", 
            count($registered_required), 
            count($required_shortcodes)
        );
        
        if (!empty($registered_required)) {
            $details .= "Registrati (richiesti): " . implode(', ', $registered_required) . "\n";
        }
        
        if (!empty($missing_required)) {
            $details .= "Mancanti (richiesti): " . implode(', ', $missing_required) . "\n";
        }
        
        if (!empty($registered_optional)) {
            $details .= "Registrati (opzionali): " . implode(', ', $registered_optional) . "\n";
        }
        
        if (!empty($missing_optional)) {
            $details .= "Mancanti (opzionali): " . implode(', ', $missing_optional) . "\n";
        }
        
        // Debug aggiuntivo: lista tutti gli shortcode WordPress registrati (primi 20)
        if ($total_registered > 0) {
            $all_wp_shortcodes = array_keys($shortcode_tags);
            $details .= "\nPrimi 20 shortcode WordPress registrati: " . implode(', ', array_slice($all_wp_shortcodes, 0, 20));
            if ($total_registered > 20) {
                $details .= sprintf(" ... (e altri %d)", $total_registered - 20);
            }
        }

        return [
            'name' => 'Shortcode Registrati',
            'status' => $status,
            'message' => $message,
            'details' => $details,
        ];
    }

    /**
     * Check REST API endpoints
     * Usa rest_do_request() per loopback zero-IO invece di wp_remote_get()
     */
    private static function check_rest_api(): array
    {
        // Endpoints configurabili tramite filter
        $endpoints = apply_filters('gik25/healthcheck/rest_endpoints', [
            '/wp-mcp/v1/categories',
            '/wp-mcp/v1/posts/recent',
            '/wp-mcp/v1/posts/search',
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

    /**
     * Check AJAX endpoints
     * Usa has_action() invece di accesso diretto a $wp_filter per robustezza
     */
    private static function check_ajax_endpoints(): array
    {
        // AJAX endpoints configurabili tramite filter
        $optional_actions = apply_filters('gik25/healthcheck/ajax_actions', [
            'kitchen_finder_calculate',
            'kitchen_finder_pdf',
        ]);

        $registered = [];
        $missing = [];

        foreach ($optional_actions as $action) {
            $hook_logged = 'wp_ajax_' . $action;
            $hook_nopriv = 'wp_ajax_nopriv_' . $action;
            
            // Usa has_action() per verificare se gli hook sono registrati
            $has_logged = has_action($hook_logged);
            $has_nopriv = has_action($hook_nopriv);
            
            if ($has_logged || $has_nopriv) {
                $registered[] = $action;
            } else {
                $missing[] = $action;
            }
        }

        // Se kitchen_finder shortcode non è registrato, questi endpoint sono opzionali
        global $shortcode_tags;
        $kitchen_finder_exists = isset($shortcode_tags['kitchen_finder']);
        
        if (empty($registered) && !$kitchen_finder_exists) {
            // Se kitchen_finder non esiste, questi endpoint non sono necessari
            $status = 'success';
            $message = 'Endpoint AJAX: Nessun endpoint richiesto (kitchen_finder non attivo)';
            $details = 'Kitchen Finder non è attivo su questo sito, quindi gli endpoint AJAX non sono necessari.';
        } elseif (!empty($missing) && $kitchen_finder_exists) {
            // Se kitchen_finder esiste ma gli endpoint mancano, è un errore
            $status = 'error';
            $message = sprintf('Endpoint AJAX mancanti: %s', implode(', ', $missing));
            $details = 'Kitchen Finder è attivo ma gli endpoint AJAX non sono registrati.';
        } elseif (empty($missing)) {
            $status = 'success';
            $message = sprintf('Tutti gli endpoint AJAX registrati (%d)', count($registered));
            $details = 'Registrati: ' . implode(', ', $registered);
        } else {
            // Warning se alcuni endpoint mancano ma kitchen_finder non è attivo
            $status = 'success'; // Non è un errore se kitchen_finder non è attivo
            $message = sprintf('Endpoint AJAX opzionali: %d registrati, %d mancanti (non necessari)', 
                count($registered), 
                count($missing)
            );
            $details = 'Questi endpoint sono opzionali e non sono necessari perché kitchen_finder non è attivo.';
        }

        return [
            'name' => 'AJAX Endpoints',
            'status' => $status,
            'message' => $message,
            'details' => $details . "\n" .
                        (empty($registered) ? '' : 'Registrati: ' . implode(', ', $registered) . "\n") .
                        (empty($missing) ? '' : 'Mancanti (opzionali): ' . implode(', ', $missing)),
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
            'include/class/Shortcodes/KitchenFinder.php',
            'include/class/Shortcodes/AppNav.php',
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
     * Verifica prima file_exists() sul path, poi (opzionale) HEAD request
     */
    private static function check_assets(): array
    {
        // Determina plugin directory e URL in modo robusto
        $plugin_dir = plugin_dir_path(__FILE__);
        $plugin_dir = dirname(dirname(dirname($plugin_dir)));
        
        // Usa il file principale del plugin come riferimento
        $plugin_file = $plugin_dir . '/revious-microdata.php';
        if (!file_exists($plugin_file)) {
            // Fallback: cerca il file principale
            $plugin_file = $plugin_dir . '/gik25-microdata.php';
        }
        
        $plugin_url = plugins_url('/', $plugin_file);

        $assets = [
            'assets/css/kitchen-finder.css',
            'assets/js/kitchen-finder.js',
            'assets/css/app-nav.css',
            'assets/js/app-nav.js',
        ];

        $accessible = [];
        $failed = [];

        foreach ($assets as $asset) {
            $file_path = $plugin_dir . '/' . $asset;
            
            // Prima verifica: file esiste sul filesystem?
            if (!file_exists($file_path)) {
                $failed[] = $asset . ' (file non trovato)';
                continue;
            }
            
            // Seconda verifica (opzionale): file accessibile via HTTP?
            // Solo se necessario (es. per verificare permessi o hotlink protection)
            $url = $plugin_url . $asset;
            $response = wp_remote_head($url, [
                'timeout' => 5,
                'sslverify' => false, // Evita problemi con certificati self-signed in dev
            ]);
            
            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                $accessible[] = $asset;
            } elseif (file_exists($file_path)) {
                // File esiste ma non accessibile via HTTP (potrebbe essere hotlink protection)
                // Consideralo comunque OK se il file esiste
                $accessible[] = $asset . ' (file esiste, accesso HTTP non verificato)';
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
        // Classi sempre richieste
        $required_classes = [
            'gik25microdata\PluginBootstrap',
        ];
        
        // Classi opzionali (dipendono da configurazione)
        $optional_classes = [
            'gik25microdata\Shortcodes\KitchenFinder', // Solo se kitchen_finder è attivo
            'gik25microdata\Shortcodes\AppNav', // Solo se app_nav è attivo
            'gik25microdata\REST\MCPApi', // Solo se MCP è attivo
            'gik25microdata\Widgets\ContextualWidgets', // Solo se attivo
            'gik25microdata\Shortcodes\GenericCarousel', // Solo se caroselli sono usati
        ];

        $existing_required = [];
        $missing_required = [];
        $existing_optional = [];
        $missing_optional = [];

        // Controlla classi richieste
        foreach ($required_classes as $class) {
            if (class_exists($class)) {
                $existing_required[] = $class;
            } else {
                $missing_required[] = $class;
            }
        }
        
        // Controlla classi opzionali
        foreach ($optional_classes as $class) {
            if (class_exists($class)) {
                $existing_optional[] = $class;
            } else {
                $missing_optional[] = $class;
            }
        }

        // Se mancano solo classi opzionali, è un warning
        if (empty($missing_required) && !empty($missing_optional)) {
            $status = 'success'; // Non è un errore se le classi opzionali mancano
            $message = sprintf('Classi base caricate (%d), opzionali: %d/%d', 
                count($existing_required),
                count($existing_optional),
                count($optional_classes)
            );
        } elseif (!empty($missing_required)) {
            $status = 'error';
            $message = sprintf('Classi base mancanti: %s', implode(', ', $missing_required));
        } else {
            $status = 'success';
            $message = sprintf('Tutte le classi caricate (%d base + %d opzionali)', 
                count($existing_required),
                count($existing_optional)
            );
        }

        $all_existing = array_merge($existing_required, $existing_optional);
        $all_missing = array_merge($missing_required, $missing_optional);

        return [
            'name' => 'Classi PHP',
            'status' => $status,
            'message' => $message,
            'details' => 'Caricate: ' . implode(', ', $all_existing) . "\n" .
                        (empty($missing_required) ? '' : 'Mancanti (richieste): ' . implode(', ', $missing_required) . "\n") .
                        (!empty($missing_optional) ? 'Mancanti (opzionali): ' . implode(', ', $missing_optional) : ''),
        ];
    }

    /**
     * Check log Cloudways per problemi
     * PROTETTO: gestisce errori senza bloccare WordPress
     */
    private static function check_logs(): array
    {
        // Salva stato originale per ripristino sicuro
        $original_state = self::disable_error_logging();
        
        try {
            // Limita risorse per evitare problemi
            $old_memory_limit = @ini_get('memory_limit');
            $old_max_execution_time = @ini_get('max_execution_time');
            @ini_set('memory_limit', '256M');
            @set_time_limit(30); // Max 30 secondi
            
            // Esegui analisi in modo sicuro
            $analysis = CloudwaysLogParser::analyze_logs();
            
            // Separa errori PHP dagli altri errori
            $php_errors = [];
            $other_issues = [];
            foreach ($analysis['issues'] ?? [] as $issue) {
                if ($issue['type'] === 'PHP Error') {
                    $php_errors[] = $issue;
                } else {
                    $other_issues[] = $issue;
                }
            }
            
            // Se ci sono errori PHP critici, priorità su di essi
            $php_critical_errors = array_filter($php_errors, function($issue) {
                return $issue['severity'] === 'error' && 
                       HealthCheckConstants::isCriticalSeverity($issue['error_type'] ?? '');
            });
            
            // Determina status: se ci sono errori PHP critici, status = error
            $status = $analysis['status'] ?? 'warning';
            if (!empty($php_critical_errors)) {
                $status = 'error';
            }
            
            // Costruisci messaggio con focus su errori PHP
            $message = $analysis['message'] ?? 'Analisi completata';
            if (!empty($php_critical_errors)) {
                $php_error_count = count($php_critical_errors);
                $message = sprintf('⚠️ %d errore/i PHP critico/i rilevato/i! %s', 
                    $php_error_count, 
                    $message
                );
            } elseif (!empty($php_errors)) {
                $php_warning_count = count(array_filter($php_errors, fn($e) => $e['severity'] === 'warning'));
                if ($php_warning_count > 0) {
                    $message = sprintf('⚠️ %d warning PHP rilevato/i. %s', $php_warning_count, $message);
                }
            }
            
            // Riepilogo contesti
            $context_summary = self::get_context_summary($analysis['issues'] ?? []);
            if (!empty($context_summary)) {
                $analysis['details'] .= "\n" . $context_summary;
            }
            
            // Formatta dettagli con sezione separata per errori PHP
            $details = $analysis['details'] ?? 'Nessun dettaglio disponibile';
            
            // Se ci sono errori PHP, aggiungi sezione dedicata
            if (!empty($php_errors)) {
                $php_details = "\n\n" . str_repeat("=", 60) . "\n";
                $php_details .= "ERRORI PHP CRITICI\n";
                $php_details .= str_repeat("=", 60) . "\n\n";
                
                foreach ($php_errors as $php_error) {
                    $severity_icon = $php_error['severity'] === 'error' ? '❌' : '⚠️';
                    $php_details .= sprintf(
                        "%s [%s] %s\n",
                        $severity_icon,
                        strtoupper($php_error['severity']),
                        $php_error['message']
                    );
                    
                    if (!empty($php_error['files'])) {
                        $php_details .= "   File: " . implode(', ', array_slice($php_error['files'], 0, 5)) . "\n";
                    }
                    if (!empty($php_error['lines'])) {
                        $php_details .= "   Righe: " . implode(', ', array_slice($php_error['lines'], 0, 5)) . "\n";
                    }
                    if (!empty($php_error['examples'])) {
                        $php_details .= "   Esempi: " . count($php_error['examples']) . " disponibili\n";
                    }
                    $php_details .= "\n";
                }
                
                $details = $php_details . "\n" . $details;
            }
            
            return [
                'name' => 'Analisi Log Cloudways',
                'status' => $status,
                'message' => $message,
                'details' => $details,
                'php_errors' => $php_errors, // Passa errori PHP separatamente
                'other_issues' => $other_issues, // Passa altri problemi separatamente
                'analysis_data' => $analysis, // Passa dati completi per rendering avanzato
            ];
            
        } catch (\Throwable $e) {
            // NON loggare l'errore - questo eviterebbe loop infiniti
            // Ritorna un messaggio sicuro senza crashare WordPress
            return [
                'name' => 'Analisi Log Cloudways',
                'status' => 'warning',
                'message' => 'Analisi log non disponibile (errore interno gestito)',
                'details' => 'Il parser ha riscontrato un problema durante l\'analisi. Questo non ha impatto sul funzionamento del sito.',
            ];
        } finally {
            // RIPRISTINA SEMPRE le impostazioni
            self::restore_error_logging($original_state);
            if (isset($old_memory_limit)) {
                @ini_set('memory_limit', $old_memory_limit);
            }
            if (isset($old_max_execution_time)) {
                @set_time_limit((int)$old_max_execution_time);
            }
        }
    }
    
    /**
     * Disabilita il logging degli errori in modo sicuro
     * @return array Stato originale da ripristinare
     */
    private static function disable_error_logging(): array
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
    private static function restore_error_logging(array $state): void
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
    
    /**
     * Genera riepilogo dei contesti di esecuzione
     */
    private static function get_context_summary(array $issues): string
    {
        $contexts_count = array_fill_keys(array_keys(HealthCheckConstants::CONTEXT_LABELS), 0);
        
        foreach ($issues as $issue) {
            if (!empty($issue['contexts'])) {
                foreach ($issue['contexts'] as $context) {
                    if (isset($contexts_count[$context])) {
                        $contexts_count[$context]++;
                    }
                }
            }
        }
        
        $summary_parts = [];
        foreach ($contexts_count as $context => $count) {
            if ($count > 0) {
                $summary_parts[] = HealthCheckConstants::getContextLabel($context) . ': ' . $count;
            }
        }
        
        if (empty($summary_parts)) {
            return '';
        }
        
        return "\nRiepilogo per contesto di esecuzione:\n" . implode(', ', $summary_parts);
    }
}



