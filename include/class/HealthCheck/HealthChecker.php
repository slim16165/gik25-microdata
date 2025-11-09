<?php
namespace gik25microdata\HealthCheck;

use gik25microdata\Shortcodes\ShortcodeRegistry;

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
        $checks = self::run_all_checks();
        ?>
        <div class="wrap">
            <h1>Health Check - Revious Microdata</h1>
            <p>Verifica che tutte le funzionalità del plugin siano operative dopo un deploy.</p>
            
            <nav class="nav-tab-wrapper" style="margin-bottom:15px;">
                <a href="#summary" class="nav-tab nav-tab-active" data-tab="summary"><?php esc_html_e('Riepilogo', 'gik25-microdata'); ?></a>
                <a href="#details" class="nav-tab" data-tab="details"><?php esc_html_e('Dettagli', 'gik25-microdata'); ?></a>
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

        <style>
            .health-check-toolbar {
                margin: 10px 0 20px;
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                align-items: center;
            }
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
            .health-check-item .badge {
                display: inline-block;
                padding: 2px 8px;
                border-radius: 10px;
                font-size: 11px;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: #fff;
            }
            .health-check-item.success .badge { background: #46b450; }
            .health-check-item.warning .badge { background: #ffb900; }
            .health-check-item.error .badge { background: #dc3232; }
            .health-check-item .details {
                margin-top: 10px;
                padding: 10px;
                background: white;
                border-radius: 4px;
                font-family: monospace;
                font-size: 12px;
                white-space: pre-wrap;
            }
            .health-check-summary {
                padding: 20px;
                margin: 20px 0;
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
            .health-check-section { display: none; }
            .health-check-section.active { display: block; }
        </style>

        <script>
        jQuery(document).ready(function($) {
            var healthCheckNonce = '<?php echo esc_js(wp_create_nonce('gik25_health_check')); ?>';
            var $resultsContainer = $('#health-check-results');
            var copyFeedbackTimeout = null;

            function bindHealthCheckUI(activeTab) {
                var $tabs = $('.nav-tab-wrapper a');
                var targetTab = activeTab || $tabs.filter('.nav-tab-active').data('tab') || 'summary';

                $tabs.removeClass('nav-tab-active');
                $tabs.filter('[data-tab="' + targetTab + '"]').addClass('nav-tab-active');

                $('.health-check-section').removeClass('active');
                $('#' + targetTab).addClass('active');

                $tabs.off('click').on('click', function(e) {
                    e.preventDefault();
                    bindHealthCheckUI($(this).data('tab'));
                });

                $('#filter-status').off('change').on('change', function() {
                    var val = $(this).val();
                    $('.health-check-item').show();
                    if (val !== 'all') {
                        $('.health-check-item').not('.' + val).hide();
                    }
                }).trigger('change');
            }

            function setRunningState(isRunning) {
                var $button = $('#run-health-check');
                if (isRunning) {
                    $button.data('original-html', $button.html());
                    $button.prop('disabled', true).html('⏳ <?php echo esc_js(__('In esecuzione...', 'gik25-microdata')); ?>');
                } else {
                    var original = $button.data('original-html');
                    if (original) {
                        $button.html(original);
                    }
                    $button.prop('disabled', false);
                }
            }

            function renderResults(html, activeTab) {
                $resultsContainer.html(html);
                bindHealthCheckUI(activeTab);
            }

            $('#run-health-check').off('click').on('click', function() {
                setRunningState(true);
                var activeTab = $('.nav-tab-wrapper a.nav-tab-active').data('tab') || 'summary';

                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'gik25_health_check',
                        nonce: healthCheckNonce
                    }
                }).done(function(response) {
                    if (response && response.success && response.data && response.data.html) {
                        renderResults(response.data.html, activeTab);
                    } else {
                        var message = (response && response.data && response.data.message) ? response.data.message : 'Errore sconosciuto.';
                        window.alert('Health Check fallito: ' + message);
                    }
                }).fail(function(xhr) {
                    console.error('Health Check AJAX error', xhr);
                    window.alert('Errore durante l\'esecuzione degli health check. Controlla la console per dettagli.');
                }).always(function() {
                    setRunningState(false);
                });
            });

            $('#copy-results').off('click').on('click', function() {
                var $button = $(this);
                var originalHtml = $button.html();

                function restoreButton() {
                    if (copyFeedbackTimeout) {
                        clearTimeout(copyFeedbackTimeout);
                    }
                    copyFeedbackTimeout = setTimeout(function() {
                        $button.html(originalHtml);
                    }, 2000);
                }

                var text = formatHealthCheckResults();

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(function() {
                        $button.html('✅ <?php echo esc_js(__('Copiato!', 'gik25-microdata')); ?>');
                        restoreButton();
                    }).catch(function() {
                        fallbackCopy(text, $button, originalHtml, restoreButton);
                    });
                } else {
                    fallbackCopy(text, $button, originalHtml, restoreButton);
                }
            });

            function fallbackCopy(text, $button, originalHtml, restoreButton) {
                var $textarea = $('<textarea>', {
                    text: text,
                    css: { position: 'absolute', left: '-9999px', top: '0' }
                }).appendTo('body');

                $textarea.trigger('focus').trigger('select');
                try {
                    document.execCommand('copy');
                    $button.html('✅ <?php echo esc_js(__('Copiato!', 'gik25-microdata')); ?>');
                } catch (err) {
                    console.error('Clipboard fallback failed', err);
                    window.alert('Impossibile copiare automaticamente. Copia manualmente i risultati.');
                }
                $textarea.remove();
                restoreButton();
            }

            function formatHealthCheckResults() {
                var lines = [];
                lines.push('=== Health Check - Revious Microdata ===');

                var summaryText = $resultsContainer.find('.health-check-summary').text().replace(/\s+/g, ' ').trim();
                if (summaryText) {
                    lines.push(summaryText);
                }

                $resultsContainer.find('.health-check-item').each(function() {
                    var $item = $(this);
                    var status = 'INFO';
                    if ($item.hasClass('error')) {
                        status = 'ERROR';
                    } else if ($item.hasClass('warning')) {
                        status = 'WARNING';
                    } else if ($item.hasClass('success')) {
                        status = 'SUCCESS';
                    }

                    var title = $.trim($item.find('h3').text());
                    var message = $.trim($item.find('p').first().text());

                    lines.push('');
                    lines.push('[' + status + '] ' + title);
                    if (message) {
                        lines.push('   ' + message);
                    }

                    var detailsText = $.trim($item.find('.details').text());
                    if (detailsText) {
                        lines.push('   Dettagli:');
                        detailsText.split(/\n/).forEach(function(line) {
                            var trimmed = $.trim(line);
                            if (trimmed.length) {
                                lines.push('      ' + trimmed);
                            }
                        });
                    }
                });

                return lines.join('\n');
            }

            bindHealthCheckUI('summary');
        });
        </script>
         <?php
     }    /**
     * Render risultati check
     */
    private static function render_checks_results(array $checks): void
    {
        $total = count($checks);
        $success = count(array_filter($checks, fn($c) => $c['status'] === 'success'));
        $warnings = count(array_filter($checks, fn($c) => $c['status'] === 'warning'));
        $errors = count(array_filter($checks, fn($c) => $c['status'] === 'error'));

        ?>
        <div class="health-check-section active" id="summary">
            <div class="health-check-summary">
                <h2><?php esc_html_e('Riepilogo', 'gik25-microdata'); ?></h2>
                <p>
                    <strong><?php esc_html_e('Totale:', 'gik25-microdata'); ?></strong> <?php echo $total; ?> |
                    <span style="color:#46b450;"><?php esc_html_e('[OK] Successo:', 'gik25-microdata'); ?> <?php echo $success; ?></span> |
                    <span style="color:#ffb900;"><?php esc_html_e('[WARN] Warning:', 'gik25-microdata'); ?> <?php echo $warnings; ?></span> |
                    <span style="color:#dc3232;"><?php esc_html_e('[ERR] Errori:', 'gik25-microdata'); ?> <?php echo $errors; ?></span>
                </p>
                <p><small><?php esc_html_e('Ultimo check:', 'gik25-microdata'); ?> <?php echo current_time('mysql'); ?></small></p>
            </div>
        </div>

        <div class="health-check-section" id="details">
            <?php foreach ($checks as $check): ?>
                <?php 
                // Controlla se questo check ha errori PHP separati
                $has_php_errors = !empty($check['php_errors']);
                $log_check_name = 'Analisi Log Cloudways';
                ?>
                <?php if ($check['name'] === $log_check_name): ?>
                    <!-- Struttura unica per Analisi Log Cloudways -->
                    <div class="health-check-item <?php echo esc_attr($check['status']); ?>">
                        <h3>
                            <span class="badge"><?php echo esc_html(strtoupper($check['status'])); ?></span>
                            <?php echo esc_html($check['name']); ?>
                        </h3>
                        <p style="margin-bottom: 15px;"><?php echo esc_html($check['message']); ?></p>
                        
                        <?php 
                        // Tail degli ultimi errori grezzi dai log
                        $tail = \gik25microdata\HealthCheck\CloudwaysLogParser::recent_errors_tail(30, 24); 
                        ?>
                        
                        <!-- Errori PHP Critici (collapsed) -->
                        <?php if ($has_php_errors): ?>
                            <details style="margin: 15px 0; background: #fff5f5; border: 1px solid #dc3232; border-radius: 4px; padding: 10px;">
                                <summary style="cursor: pointer; font-weight: 600; color: #dc3232; padding: 8px;">
                                    ❌ Errori PHP Critici - <?php echo count($check['php_errors']); ?> errore/i rilevato/i
                                </summary>
                                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #dc3232;">
                                    <div class="php-errors-list">
                                        <?php foreach ($check['php_errors'] as $idx => $php_error): ?>
                                            <div class="php-error-item" style="border: 1px solid #dc3232; border-radius: 4px; padding: 15px; margin-bottom: 15px; background: #fff;">
                                                <h4 style="margin-top: 0; color: #dc3232; display: flex; align-items: center; gap: 10px;">
                                                    <span style="font-size: 18px;">
                                                        <?php echo $php_error['severity'] === 'error' ? '❌' : '⚠️'; ?>
                                                    </span>
                                                    <span>
                                                        <?php 
                                                        $error_type_labels = [
                                                            'fatal' => 'Fatal Error',
                                                            'parse' => 'Parse Error',
                                                            'error' => 'Uncaught Error',
                                                            'exception' => 'Uncaught Exception',
                                                            'warning' => 'PHP Warning',
                                                            'database' => 'Database Error',
                                                        ];
                                                        $error_type = $php_error['error_type'] ?? 'unknown';
                                                        echo esc_html($error_type_labels[$error_type] ?? ucfirst($error_type));
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
                                                        $context_labels = [
                                                            'wp_cli' => 'WP-CLI',
                                                            'ajax' => 'AJAX',
                                                            'wp_cron' => 'WP-CRON',
                                                            'frontend' => 'Frontend',
                                                            'backend' => 'Backend',
                                                            'rest_api' => 'REST API',
                                                            'unknown' => 'Unknown',
                                                        ];
                                                        $contexts_display = array_map(function($ctx) use ($context_labels) {
                                                            return $context_labels[$ctx] ?? $ctx;
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
                                                                                    $context_labels = [
                                                                                        'wp_cli' => 'WP-CLI',
                                                                                        'ajax' => 'AJAX',
                                                                                        'wp_cron' => 'WP-CRON',
                                                                                        'frontend' => 'Frontend',
                                                                                        'backend' => 'Backend',
                                                                                        'rest_api' => 'REST API',
                                                                                        'unknown' => 'Unknown',
                                                                                    ];
                                                                                    $ctx_label = $context_labels[$example['context']] ?? $example['context'];
                                                                                    echo esc_html($ctx_label);
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
                                                    $labels = [
                                                        'access_5xx' => 'HTTP 5xx (Nginx/Apache/PHP Access)',
                                                        'nginx_error'      => 'Nginx Error',
                                                        'apache_error'     => 'Apache Error',
                                                        'php_error'        => 'PHP Error',
                                                        'php_slow'         => 'PHP Slow',
                                                        'wp_cron'          => 'WP-Cron',
                                                    ];
                                                    echo esc_html(($labels[$key] ?? ucfirst($key)) . 
                                                        (!empty($bundle['file']) ? ' · ' . $bundle['file'] : '') . 
                                                        ' (' . count($bundle['entries']) . ' righe)');
                                                ?>
                                            </summary>
                                            <pre class="details" style="margin-top:10px; white-space:pre-wrap; font-family:monospace; font-size:12px; background: #f5f5f5; padding: 10px; border-radius: 3px; max-height: 400px; overflow-y: auto;">
<?php echo esc_html(implode("\n", $bundle['entries'])); ?>
                                            </pre>
                                        </details>
                                    <?php endforeach; ?>
                                </div>
                            </details>
                        <?php endif; ?>
                        
                        <!-- Dettagli completi (collapsed) -->
                        <?php if (!empty($check['details'])): ?>
                            <details style="margin: 15px 0; background: #f0f0f0; border: 1px solid #ccc; border-radius: 4px; padding: 10px;">
                                <summary style="cursor: pointer; font-weight: 600; padding: 8px;">
                                    📄 Dettagli Completi (formato testo)
                                </summary>
                                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ccc;">
                                    <pre style="max-height: 500px; overflow-y: auto; background: #fff; padding: 15px; border-radius: 3px; font-size: 12px;"><?php echo esc_html($check['details']); ?></pre>
                                </div>
                            </details>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <!-- Rendering standard per altri check -->
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
                <?php endif; ?>
            <?php endforeach; ?>
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

            $checks = self::run_all_checks();
            
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
                // Accesso pubblico ma limitato (puoi aggiungere autenticazione se necessario)
                return true;
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
     * Esegui tutti i check
     * PROTETTO: gestisce errori senza bloccare WordPress
     */
    public static function run_all_checks(): array
    {
        // Esegui tutti i check in modo sicuro
        return \gik25microdata\Utility\SafeExecution::safe_execute(function() {
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
        // AJAX endpoints opzionali (solo se kitchen_finder è attivo)
        $optional_actions = [
            'kitchen_finder_calculate',
            'kitchen_finder_pdf',
        ];

        // Verifica che gli hook siano registrati
        global $wp_filter;
        $registered = [];
        $missing = [];

        foreach ($optional_actions as $action) {
            $hook_logged = 'wp_ajax_' . $action;
            $hook_nopriv = 'wp_ajax_nopriv_' . $action;
            
            // Verifica anche se le callback sono registrate e non vuote
            $has_logged = isset($wp_filter[$hook_logged]) && 
                         !empty($wp_filter[$hook_logged]->callbacks);
            $has_nopriv = isset($wp_filter[$hook_nopriv]) && 
                         !empty($wp_filter[$hook_nopriv]->callbacks);
            
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
                       in_array($issue['error_type'] ?? '', ['fatal', 'parse', 'error', 'exception']);
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
        $contexts_count = [
            'wp_cli' => 0,
            'ajax' => 0,
            'wp_cron' => 0,
            'frontend' => 0,
            'backend' => 0,
            'rest_api' => 0,
            'unknown' => 0,
        ];
        
        foreach ($issues as $issue) {
            if (!empty($issue['contexts'])) {
                foreach ($issue['contexts'] as $context) {
                    if (isset($contexts_count[$context])) {
                        $contexts_count[$context]++;
                    }
                }
            }
        }
        
        $context_labels = [
            'wp_cli' => 'WP-CLI',
            'ajax' => 'AJAX',
            'wp_cron' => 'WP-CRON',
            'frontend' => 'Frontend',
            'backend' => 'Backend',
            'rest_api' => 'REST API',
            'unknown' => 'Unknown',
        ];
        
        $summary_parts = [];
        foreach ($contexts_count as $context => $count) {
            if ($count > 0) {
                $summary_parts[] = $context_labels[$context] . ': ' . $count;
            }
        }
        
        if (empty($summary_parts)) {
            return '';
        }
        
        return "\nRiepilogo per contesto di esecuzione:\n" . implode(', ', $summary_parts);
    }
}



