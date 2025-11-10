<?php
namespace gik25microdata\HealthCheck\View;

use gik25microdata\Logs\Analysis\CloudwaysLogParser;
use gik25microdata\HealthCheck\HealthCheckConstants;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * View per la pagina admin di Health Check
 */
class AdminPageView
{
    /**
     * Renderizza la pagina admin
     * 
     * @param array $checks Array di risultati dei check
     */
    public static function renderAdminPage(array $checks): void
    {
        ?>
        <div class="wrap">
            <h1>Health Check - Revious Microdata</h1>
            <p>Verifica che tutte le funzionalità del plugin siano operative dopo un deploy.</p>
            
            <nav class="nav-tab-wrapper">
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
                <?php self::renderChecksResults($checks); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Renderizza i risultati dei check
     * 
     * @param array $checks Array di risultati dei check
     */
    public static function renderChecksResults(array $checks): void
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
            $tail = CloudwaysLogParser::recent_errors_tail(
                HealthCheckConstants::TAIL_LINES_DETAILS,
                HealthCheckConstants::TAIL_WINDOW_HOURS
            );
        }

        ?>
        <div class="health-check-section active" id="summary">
            <!-- Riepilogo Health Check -->
            <div class="health-check-summary">
                <h2><?php esc_html_e('Riepilogo Health Check', 'gik25-microdata'); ?></h2>
                <p>
                        <strong><?php esc_html_e('Totale:', 'gik25-microdata'); ?></strong> <?php echo $health_total; ?> |
                    <span class="status-color-success"><?php esc_html_e('[OK] Successo:', 'gik25-microdata'); ?> <?php echo $health_success; ?></span> |
                    <span class="status-color-warning"><?php esc_html_e('[WARN] Warning:', 'gik25-microdata'); ?> <?php echo $health_warnings; ?></span> |
                    <span class="status-color-error"><?php esc_html_e('[ERR] Errori:', 'gik25-microdata'); ?> <?php echo $health_errors; ?></span>
                </p>
                <p><small><?php esc_html_e('Ultimo check:', 'gik25-microdata'); ?> <?php echo current_time('mysql'); ?></small></p>
            </div>
            
            <!-- Riepilogo Log Cloudways -->
            <div class="health-check-summary log-summary">
                <h2><?php esc_html_e('Riepilogo Analisi Log Cloudways', 'gik25-microdata'); ?></h2>
                <?php if ($log_check): ?>
                    <p>
                        <strong><?php esc_html_e('Stato:', 'gik25-microdata'); ?></strong> 
                        <span class="status-badge status-color-<?php echo esc_attr($log_status === 'unknown' ? 'success' : $log_status); ?>">
                            <?php echo esc_html(strtoupper($log_status)); ?>
                        </span>
                    </p>
                    <p><?php echo esc_html($log_check['message']); ?></p>
                    <?php if ($log_php_errors_count > 0): ?>
                        <p class="status-color-error status-badge">
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
                            $severity = \gik25microdata\Logs\Viewer\LogFormatter::extract_severity($error_line);
                            
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
                        <div class="php-errors-preview">
                            <strong>📋 Ultimi errori PHP (anteprima):</strong>
                            <?php if ($critical_count > 0 || $warning_count > 0): ?>
                                <p>
                                    <?php if ($critical_count > 0): ?>
                                        <span class="critical-count">⚠️ <?php echo $critical_count; ?> critico/i</span>
                                    <?php endif; ?>
                                    <?php if ($warning_count > 0): ?>
                                        <?php if ($critical_count > 0): ?> | <?php endif; ?>
                                        <span class="warning-count">⚠️ <?php echo $warning_count; ?> warning</span>
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>
                            <?php if ($php_error_timezone): ?>
                                <p>
                                    Timezone server: <?php echo esc_html($php_error_timezone['timezone']); ?> (<?php echo esc_html($php_error_timezone['formatted']); ?>)
                                </p>
                            <?php endif; ?>
                            <?php if ($php_error_warning && !empty($php_error_warning['message'])): ?>
                                <p class="<?php echo $php_error_warning['is_stale'] ? 'status-color-error status-badge' : ''; ?>">
                                    ⚠️ <?php echo esc_html($php_error_warning['message']); ?>
                                </p>
                            <?php endif; ?>
                            <ul>
                                <?php foreach ($php_errors_preview as $error_data): ?>
                                    <?php 
                                    $error_line = $error_data['line'];
                                    $error_severity = $error_data['severity'];
                                    $severity_class = HealthCheckConstants::isCriticalSeverity($error_severity) ? 'status-color-error' : 'status-color-warning';
                                    ?>
                                    <li>
                                        <span class="severity-badge <?php echo esc_attr($severity_class); ?>">
                                            <?php echo strtoupper($error_severity); ?>
                                        </span>
                                        <code>
                                            <?php echo esc_html(\gik25microdata\Logs\Viewer\LogFormatter::format_preview($error_line)); ?>
                                        </code>
                                    </li>
                                    <?php endforeach; ?>
                            </ul>
                            <?php if ($tail && count($tail['tails']['php_error']['entries']) > count($php_errors_preview)): ?>
                                <p>
                                    ... e altri <?php echo count($tail['tails']['php_error']['entries']) - count($php_errors_preview); ?> errori (vedi Log Viewer)
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php } ?>
                <?php else: ?>
                    <p>
                        <?php esc_html_e('Analisi log non disponibile.', 'gik25-microdata'); ?>
                    </p>
                <?php endif; ?>
                <p><small><?php esc_html_e('Ultimo check:', 'gik25-microdata'); ?> <?php echo current_time('mysql'); ?></small></p>
            </div>
        </div>

        <div class="health-check-section" id="details">
            <!-- Sezione Health Check -->
            <div class="health-check-details-section">
                <h2>
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
            <div class="health-check-details-section log-details-section">
                <h2>
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
                        <p><?php echo esc_html($log_check['message']); ?></p>
                        
                        <!-- Errori PHP Critici (collapsed) -->
                        <?php if ($has_php_errors): ?>
                            <details class="php-errors-details">
                                <summary>
                                    ❌ Errori PHP Critici - <?php echo count($log_check['php_errors']); ?> errore/i rilevato/i
                                </summary>
                                <div class="details-content">
                                    <div class="php-errors-list">
                                        <?php foreach ($log_check['php_errors'] as $idx => $php_error): ?>
                                            <div class="php-error-item">
                                                <h4>
                                                    <span class="icon">
                                                        <?php echo $php_error['severity'] === 'error' ? '❌' : '⚠️'; ?>
                                                    </span>
                                                    <span>
                                                        <?php 
                                                        $error_type = $php_error['error_type'] ?? 'unknown';
                                                        echo esc_html(HealthCheckConstants::ERROR_TYPE_LABELS[$error_type] ?? ucfirst($error_type));
                                                        ?>
                                                    </span>
                                                    <span class="count">
                                                        (<?php echo esc_html($php_error['count']); ?> occorrenze)
                                                    </span>
                                                </h4>
                                                
                                                <div class="error-field">
                                                    <strong>Messaggio:</strong> <?php echo esc_html($php_error['message']); ?>
                                                </div>
                                                
                                                <?php if (!empty($php_error['files'])): ?>
                                                    <div class="error-field">
                                                        <strong>File:</strong> 
                                                        <code>
                                                            <?php echo esc_html(implode(', ', array_slice($php_error['files'], 0, 3))); ?>
                                                            <?php if (count($php_error['files']) > 3): ?>
                                                                <span class="more-count">(+<?php echo count($php_error['files']) - 3; ?> altri)</span>
                                                            <?php endif; ?>
                                                        </code>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($php_error['lines'])): ?>
                                                    <div class="error-field">
                                                        <strong>Righe:</strong> 
                                                        <code>
                                                            <?php echo esc_html(implode(', ', array_slice($php_error['lines'], 0, 5))); ?>
                                                            <?php if (count($php_error['lines']) > 5): ?>
                                                                <span class="more-count">(+<?php echo count($php_error['lines']) - 5; ?> altri)</span>
                                                            <?php endif; ?>
                                                        </code>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($php_error['contexts'])): ?>
                                                    <div class="error-field">
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
                                                    <div class="php-error-example">
                                                        <strong>Esempi (<?php echo count($php_error['examples']); ?>):</strong>
                                                        <div>
                                                            <?php foreach (array_slice($php_error['examples'], 0, 2) as $example_idx => $example): ?>
                                                                <?php if (is_array($example)): ?>
                                                                    <details>
                                                                        <summary>
                                                                            Esempio <?php echo $example_idx + 1; ?>
                                                                            <?php if (!empty($example['file'])): ?>
                                                                                - <?php echo esc_html(basename($example['file'])); ?>
                                                                                <?php if (!empty($example['line'])): ?>
                                                                                    :<?php echo esc_html($example['line']); ?>
                                                                                <?php endif; ?>
                                                                            <?php endif; ?>
                                                                        </summary>
                                                                        <div class="example-content">
                                                                            <?php if (!empty($example['message'])): ?>
                                                                                <div class="example-field">
                                                                                    <strong>Messaggio:</strong><br>
                                                                                    <code>
                                                                                        <?php echo esc_html($example['message']); ?>
                                                                                    </code>
                                                                                </div>
                                                                            <?php endif; ?>
                                                                            
                                                                            <?php if (!empty($example['file'])): ?>
                                                                                <div class="example-field">
                                                                                    <strong>File:</strong> 
                                                                                    <code><?php echo esc_html($example['file']); ?></code>
                                                                                    <?php if (!empty($example['line'])): ?>
                                                                                        <strong>Riga:</strong> 
                                                                                        <code><?php echo esc_html($example['line']); ?></code>
                                                                                    <?php endif; ?>
                                                                                </div>
                                                                            <?php endif; ?>
                                                                            
                                                                            <?php if (!empty($example['stack_trace'])): ?>
                                                                                <div class="example-field">
                                                                                    <strong>Stack Trace:</strong>
                                                                                    <pre><?php 
                                                                                        echo esc_html(implode("\n", array_slice($example['stack_trace'], 0, 15)));
                                                                                        if (count($example['stack_trace']) > 15) {
                                                                                            echo "\n... (" . (count($example['stack_trace']) - 15) . " altre righe)";
                                                                                        }
                                                                                    ?></pre>
                                                                                </div>
                                                                            <?php endif; ?>
                                                                            
                                                                            <?php if (!empty($example['context'])): ?>
                                                                                <div class="example-field">
                                                                                    <strong>Contesto:</strong> 
                                                                                    <?php 
                                                                                    echo esc_html(HealthCheckConstants::getContextLabel($example['context']));
                                                                                    ?>
                                                                                </div>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </details>
                                                                <?php else: ?>
                                                                    <div class="example-simple">
                                                                        <code><?php echo esc_html($example); ?></code>
                                                                    </div>
                                                                <?php endif; ?>
                                                            <?php endforeach; ?>
                                                            
                                                            <?php if (count($php_error['examples']) > 2): ?>
                                                                <p class="more-examples">
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
                            <details class="tail-errors-details">
                                <summary>
                                    📋 Ultimi errori dai log (tail, 24h)
                                    <?php if (!empty($tail['paths']['base'])): ?>
                                        <span class="file-path">
                                            (<?php echo esc_html($tail['paths']['base']); ?>)
                                        </span>
                                    <?php endif; ?>
                                </summary>
                                <div class="tail-content">
                                    <?php foreach ($tail['tails'] as $key => $bundle): ?>
                                        <details class="tail-bundle">
                                            <summary>
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
                                                <div class="timezone-info">
                                                    <?php if ($php_timezone): ?>
                                                        <p>
                                                            <strong>Timezone server:</strong> <?php echo esc_html($php_timezone['timezone']); ?> (<?php echo esc_html($php_timezone['formatted']); ?>)
                                                        </p>
                                                    <?php endif; ?>
                                                    <?php if ($php_warning && !empty($php_warning['message'])): ?>
                                                        <p class="warning <?php echo $php_warning['is_stale'] ? 'stale' : ''; ?>">
                                                            ⚠️ <?php echo esc_html($php_warning['message']); ?>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                            <?php 
                                                endif;
                                            }
                                            ?>
                                            <div class="entries">
                                                <?php foreach ($bundle['entries'] as $entry_line): ?>
                                                    <?php 
                                                    $formatted = \gik25microdata\Logs\Viewer\LogFormatter::format_line($entry_line);
                                                    ?>
                                                    <div class="entry <?php echo esc_attr($formatted['class']); ?>" style="background: <?php echo esc_attr($formatted['bg_color']); ?>; border-left: 3px solid <?php echo esc_attr($formatted['color']); ?>;">
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
                            <details class="full-details">
                                <summary>
                                    📄 Dettagli Completi (formato testo)
                                </summary>
                                <div class="details-content">
                                    <pre><?php echo esc_html($log_check['details']); ?></pre>
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
            if (class_exists('\gik25microdata\Logs\Viewer\LogViewer')) {
                // Enqueue assets per Log Viewer
                \gik25microdata\Logs\Viewer\LogViewer::enqueue_assets();
                \gik25microdata\Logs\Viewer\LogViewer::render_page();
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
}

