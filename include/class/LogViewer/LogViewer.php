<?php
namespace gik25microdata\LogViewer;

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
     * Render pagina Log Viewer
     */
    public static function render_page(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('Non hai i permessi per accedere a questa pagina.'));
        }
        
        // Verifica che REST API sia disponibile
        $rest_url = rest_url('gik25/v1/logs/errors');
        $nonce = wp_create_nonce('wp_rest');
        
        // Debug: verifica che l'endpoint sia registrato (solo se REST API è disponibile)
        $endpoint_exists = false;
        if (function_exists('rest_get_server')) {
            try {
                $rest_server = rest_get_server();
                if ($rest_server) {
                    $rest_routes = $rest_server->get_routes();
                    $endpoint_exists = isset($rest_routes['/gik25/v1/logs/errors']);
                    
                    // Se l'endpoint non esiste, logga per debug
                    if (!$endpoint_exists && defined('WP_DEBUG') && WP_DEBUG && WP_DEBUG_LOG) {
                        error_log('LogViewer: Endpoint REST /gik25/v1/logs/errors non trovato.');
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
        <div class="log-viewer-container" style="max-width: 100%; margin: 20px 0;">
            <h2 style="margin-bottom: 20px;">Log Viewer - PHP Errors</h2>
            <p style="color: #666; margin-bottom: 20px;">Visualizzazione avanzata degli errori PHP con filtri, ricerca e export. I dati vengono caricati in tempo reale quando modifichi i filtri.</p>
            
            <!-- Toolbar con filtri e export -->
            <div class="log-viewer-toolbar" style="margin-bottom: 20px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
                <div style="display: flex; flex-wrap: wrap; gap: 15px; align-items: center;">
                    <!-- Filtri -->
                    <div style="flex: 1; min-width: 200px;">
                        <label for="filter-severity" style="display: block; margin-bottom: 5px; font-weight: bold; font-size: 12px;">Severity:</label>
                        <select id="filter-severity" multiple style="width: 100%; min-height: 80px; padding: 5px;">
                            <option value="fatal">Fatal</option>
                            <option value="error">Error</option>
                            <option value="warning" selected>Warning</option>
                            <option value="info">Info</option>
                        </select>
                        <small style="color: #666; font-size: 11px;">Ctrl+Click per selezioni multiple</small>
                    </div>
                    
                    <div style="flex: 1; min-width: 200px;">
                        <label for="filter-file" style="display: block; margin-bottom: 5px; font-weight: bold; font-size: 12px;">File (pattern):</label>
                        <input type="text" id="filter-file" placeholder="es. HealthChecker.php" style="width: 100%; padding: 5px;">
                    </div>
                    
                    <div style="flex: 1; min-width: 150px;">
                        <label for="filter-context" style="display: block; margin-bottom: 5px; font-weight: bold; font-size: 12px;">Contesto:</label>
                        <select id="filter-context" multiple style="width: 100%; min-height: 80px; padding: 5px;">
                            <option value="wp_cli">WP-CLI</option>
                            <option value="ajax">AJAX</option>
                            <option value="wp_cron">WP-CRON</option>
                            <option value="frontend">Frontend</option>
                            <option value="backend">Backend</option>
                            <option value="rest_api">REST API</option>
                        </select>
                    </div>
                    
                    <div style="flex: 0 0 auto;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold; font-size: 12px;">Azioni:</label>
                        <button type="button" id="btn-apply-filters" class="button button-primary" style="margin-right: 5px;">Applica Filtri</button>
                        <button type="button" id="btn-reset-filters" class="button">Reset</button>
                        <button type="button" id="btn-export-csv" class="button" style="margin-left: 5px;">Export CSV</button>
                        <button type="button" id="btn-export-json" class="button">Export JSON</button>
                    </div>
                </div>
                
                <!-- Info caricamento -->
                <div id="log-viewer-status" style="margin-top: 10px; padding: 8px; background: #fff; border: 1px solid #ddd; border-radius: 3px; font-size: 12px;">
                    <span id="log-viewer-loading" style="display: none;">⏳ Caricamento...</span>
                    <span id="log-viewer-info" style="display: none;"></span>
                </div>
            </div>
            
            <!-- Container Grid.js -->
            <div id="log-viewer-grid" style="margin-top: 20px;"></div>
        </div>
        
        <!-- Grid.js CSS e JS -->
        <link href="https://cdn.jsdelivr.net/npm/gridjs/dist/theme/mermaid.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/gridjs/dist/gridjs.umd.js"></script>
        
        <style>
            .log-viewer-container {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            }
            .log-viewer-toolbar label {
                color: #23282d;
            }
            .log-viewer-toolbar input,
            .log-viewer-toolbar select {
                border: 1px solid #8c8f94;
                border-radius: 3px;
            }
            .log-viewer-toolbar input:focus,
            .log-viewer-toolbar select:focus {
                border-color: #2271b1;
                box-shadow: 0 0 0 1px #2271b1;
            }
            #log-viewer-grid .gridjs-wrapper {
                border: 1px solid #ddd;
                border-radius: 4px;
            }
            #log-viewer-grid .gridjs-tbody tr:hover {
                background-color: #f5f5f5;
            }
            .log-severity-badge {
                display: inline-block;
                padding: 2px 8px;
                border-radius: 3px;
                font-size: 11px;
                font-weight: bold;
                color: #fff;
            }
            .log-severity-fatal { background: #8b0000; }
            .log-severity-error { background: #dc3232; }
            .log-severity-warning { background: #ffb900; color: #000; }
            .log-severity-info { background: #666; }
            .log-context-badge {
                display: inline-block;
                padding: 2px 6px;
                border-radius: 3px;
                font-size: 10px;
                background: #e5e5e5;
                color: #333;
                margin: 2px;
            }
            .log-details-toggle {
                cursor: pointer;
                color: #2271b1;
                text-decoration: underline;
            }
            .log-details-toggle:hover {
                color: #135e96;
            }
            .log-details-content {
                display: none;
                margin-top: 10px;
                padding: 10px;
                background: #f9f9f9;
                border: 1px solid #ddd;
                border-radius: 3px;
                font-size: 12px;
            }
            .log-details-content pre {
                margin: 0;
                padding: 8px;
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 3px;
                overflow-x: auto;
                font-size: 11px;
            }
        </style>
        
        <script>
        (function() {
            var restUrl = <?php echo json_encode($rest_url); ?>;
            var nonce = <?php echo json_encode($nonce); ?>;
            var grid = null;
            var currentFilters = {
                severity: ['fatal', 'error', 'warning'],
                file: '',
                context: [],
                limit: 1000,
                offset: 0
            };
            
            // Inizializza Grid.js
            function initGrid() {
                if (grid) {
                    grid.destroy();
                }
                
                document.getElementById('log-viewer-loading').style.display = 'inline';
                document.getElementById('log-viewer-info').style.display = 'none';
                
                // Costruisci URL con filtri
                var url = restUrl + '?limit=' + currentFilters.limit + '&offset=' + currentFilters.offset;
                if (currentFilters.severity.length > 0) {
                    url += '&severity=' + currentFilters.severity.join(',');
                }
                if (currentFilters.file) {
                    url += '&file=' + encodeURIComponent(currentFilters.file);
                }
                if (currentFilters.context.length > 0) {
                    url += '&context=' + currentFilters.context.join(',');
                }
                
                // Fetch dati
                fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-WP-Nonce': nonce
                    }
                })
                .then(function(response) {
                    if (!response.ok) {
                        // Se è un 404, l'endpoint non è registrato
                        if (response.status === 404) {
                            throw new Error('Endpoint REST non trovato (404). Verifica che LogViewerAPI sia inizializzato correttamente.');
                        }
                        // Leggi il corpo della risposta per dettagli
                        return response.text().then(function(text) {
                            try {
                                var errorData = JSON.parse(text);
                                throw new Error('Errore HTTP ' + response.status + ': ' + (errorData.message || errorData.code || 'Errore sconosciuto'));
                            } catch (e) {
                                throw new Error('Errore HTTP ' + response.status + ': ' + text.substring(0, 100));
                            }
                        });
                    }
                    return response.json();
                })
                .then(function(data) {
                    document.getElementById('log-viewer-loading').style.display = 'none';
                    
                    if (data.error) {
                        document.getElementById('log-viewer-info').textContent = 'Errore: ' + (data.message || data.details || 'Errore sconosciuto');
                        document.getElementById('log-viewer-info').style.display = 'inline';
                        document.getElementById('log-viewer-info').style.color = '#dc3232';
                        // Mostra messaggio di errore anche nella griglia
                        if (!grid) {
                            var container = document.getElementById('log-viewer-grid');
                            if (container) {
                                container.innerHTML = '<div style="padding: 20px; background: #fff5f5; border: 1px solid #dc3232; border-radius: 4px; color: #dc3232;"><strong>Errore:</strong> ' + (data.message || data.details || 'Errore sconosciuto') + '</div>';
                            }
                        }
                        return;
                    }
                    
                    var total = data.total || 0;
                    var errors = data.errors || [];
                    
                    document.getElementById('log-viewer-info').textContent = 'Totale: ' + total + ' errori | Mostrati: ' + errors.length;
                    document.getElementById('log-viewer-info').style.display = 'inline';
                    document.getElementById('log-viewer-info').style.color = '#666';
                    
                    // Prepara dati per Grid.js
                    var gridData = errors.map(function(error) {
                        var timestamp = error.timestamp ? new Date(error.timestamp * 1000).toLocaleString('it-IT') : '-';
                        var severity = error.severity || 'info';
                        var severityBadge = '<span class="log-severity-badge log-severity-' + severity + '">' + severity.toUpperCase() + '</span>';
                        var file = error.file ? error.file.split('/').pop() : '-';
                        var line = error.line || '-';
                        var message = error.message || '';
                        if (message.length > 100) {
                            message = message.substring(0, 97) + '...';
                        }
                        var contexts = (error.contexts || []).map(function(ctx) {
                            return '<span class="log-context-badge">' + ctx + '</span>';
                        }).join('');
                        var count = error.count || 0;
                        var detailsId = 'details-' + error.id;
                        var detailsToggle = '<a href="#" class="log-details-toggle" onclick="toggleLogDetails(\'' + detailsId + '\'); return false;">Dettagli</a>';
                        
                        return [
                            timestamp,
                            severityBadge,
                            file,
                            line,
                            message,
                            contexts || '-',
                            count,
                            detailsToggle
                        ];
                    });
                    
                    // Se non ci sono dati, mostra messaggio
                    if (gridData.length === 0) {
                        var container = document.getElementById('log-viewer-grid');
                        if (container) {
                            container.innerHTML = '<div style="padding: 20px; background: #f0f8ff; border: 1px solid #2271b1; border-radius: 4px; color: #2271b1; text-align: center;"><strong>Nessun errore trovato</strong><br><small>Prova a modificare i filtri o verifica che ci siano errori PHP nei log.</small></div>';
                        }
                        return;
                    }
                    
                    // Crea Grid.js
                    grid = new gridjs.Grid({
                        columns: [
                            { name: 'Timestamp', width: '150px', sort: true },
                            { name: 'Severity', width: '100px', sort: true, formatter: function(cell) { return gridjs.html(cell); } },
                            { name: 'File', width: '200px', sort: true },
                            { name: 'Linea', width: '80px', sort: true },
                            { name: 'Messaggio', width: '300px', sort: true },
                            { name: 'Contesto', width: '150px', formatter: function(cell) { return gridjs.html(cell); } },
                            { name: 'Occorrenze', width: '100px', sort: true },
                            { name: 'Azioni', width: '100px', formatter: function(cell) { return gridjs.html(cell); } }
                        ],
                        data: gridData,
                        search: {
                            enabled: true,
                            placeholder: 'Cerca negli errori...'
                        },
                        pagination: {
                            enabled: true,
                            limit: 50,
                            summary: true
                        },
                        sort: true,
                        resizable: true,
                        style: {
                            table: {
                                'font-size': '12px'
                            },
                            th: {
                                'background-color': '#f0f0f0',
                                'font-weight': 'bold'
                            }
                        }
                    }).render(document.getElementById('log-viewer-grid'));
                    
                    // Aggiungi dettagli espandibili dopo il rendering
                    errors.forEach(function(error) {
                        var detailsId = 'details-' + error.id;
                        var detailsContent = createDetailsContent(error);
                        var container = document.getElementById('log-viewer-grid');
                        if (container) {
                            var detailsDiv = document.createElement('div');
                            detailsDiv.id = detailsId;
                            detailsDiv.className = 'log-details-content';
                            detailsDiv.innerHTML = detailsContent;
                            container.appendChild(detailsDiv);
                        }
                    });
                })
                .catch(function(error) {
                    document.getElementById('log-viewer-loading').style.display = 'none';
                    var errorMessage = 'Errore: ' + error.message;
                    document.getElementById('log-viewer-info').textContent = errorMessage;
                    document.getElementById('log-viewer-info').style.display = 'inline';
                    document.getElementById('log-viewer-info').style.color = '#dc3232';
                    
                    // Mostra messaggio di errore nella griglia
                    var container = document.getElementById('log-viewer-grid');
                    if (container) {
                        container.innerHTML = '<div style="padding: 20px; background: #fff5f5; border: 1px solid #dc3232; border-radius: 4px; color: #dc3232;"><strong>Errore durante il caricamento:</strong><br>' + escapeHtml(error.message) + '<br><br><small>Verifica che:<br>1. L\'endpoint REST sia registrato correttamente<br>2. Il nonce sia valido<br>3. I permessi siano corretti (manage_options)</small></div>';
                    }
                    
                    console.error('Log Viewer Error:', error);
                    console.error('REST URL:', url);
                    console.error('Nonce:', nonce);
                });
            }
            
            // Crea contenuto dettagli
            function createDetailsContent(error) {
                var html = '<div style="margin-bottom: 10px;"><strong>ID:</strong> ' + (error.id || '-') + '</div>';
                
                if (error.message) {
                    html += '<div style="margin-bottom: 10px;"><strong>Messaggio:</strong><br><code style="background: #f5f5f5; padding: 5px; display: block; border-radius: 3px; word-break: break-all;">' + escapeHtml(error.message) + '</code></div>';
                }
                
                if (error.file) {
                    html += '<div style="margin-bottom: 10px;"><strong>File:</strong> ' + escapeHtml(error.file) + '</div>';
                }
                
                if (error.line) {
                    html += '<div style="margin-bottom: 10px;"><strong>Linea:</strong> ' + error.line + '</div>';
                }
                
                if (error.files && error.files.length > 1) {
                    html += '<div style="margin-bottom: 10px;"><strong>Altri file:</strong> ' + error.files.slice(1).map(function(f) { return escapeHtml(f); }).join(', ') + '</div>';
                }
                
                if (error.stack_trace && error.stack_trace.length > 0) {
                    html += '<div style="margin-bottom: 10px;"><strong>Stack Trace:</strong><pre>' + escapeHtml(error.stack_trace.join('\n')) + '</pre></div>';
                }
                
                if (error.contexts && error.contexts.length > 0) {
                    html += '<div style="margin-bottom: 10px;"><strong>Contesti:</strong> ' + error.contexts.join(', ') + '</div>';
                }
                
                if (error.first_seen) {
                    html += '<div style="margin-bottom: 10px;"><strong>Prima occorrenza:</strong> ' + new Date(error.first_seen * 1000).toLocaleString('it-IT') + '</div>';
                }
                
                if (error.last_seen) {
                    html += '<div style="margin-bottom: 10px;"><strong>Ultima occorrenza:</strong> ' + new Date(error.last_seen * 1000).toLocaleString('it-IT') + '</div>';
                }
                
                return html;
            }
            
            // Escape HTML
            function escapeHtml(text) {
                var div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
            
            // Toggle dettagli (globale per essere chiamata da onclick)
            window.toggleLogDetails = function(detailsId) {
                var details = document.getElementById(detailsId);
                if (details) {
                    if (details.style.display === 'none') {
                        details.style.display = 'block';
                    } else {
                        details.style.display = 'none';
                    }
                }
            };
            
            // Debounce per aggiornamento automatico
            var filterUpdateTimeout = null;
            function scheduleFilterUpdate() {
                if (filterUpdateTimeout) {
                    clearTimeout(filterUpdateTimeout);
                }
                filterUpdateTimeout = setTimeout(function() {
                    updateFiltersFromUI();
                    initGrid();
                }, 500); // Aggiorna dopo 500ms di inattività
            }
            
            // Aggiorna filtri dall'UI
            function updateFiltersFromUI() {
                var severitySelect = document.getElementById('filter-severity');
                currentFilters.severity = Array.from(severitySelect.selectedOptions).map(function(opt) { return opt.value; });
                currentFilters.file = document.getElementById('filter-file').value.trim();
                var contextSelect = document.getElementById('filter-context');
                currentFilters.context = Array.from(contextSelect.selectedOptions).map(function(opt) { return opt.value; });
                currentFilters.offset = 0;
            }
            
            // Applica filtri (aggiorna immediatamente)
            document.getElementById('btn-apply-filters').addEventListener('click', function() {
                updateFiltersFromUI();
                initGrid();
            });
            
            // Listeners per aggiornamento automatico
            document.getElementById('filter-severity').addEventListener('change', function() {
                scheduleFilterUpdate();
            });
            document.getElementById('filter-file').addEventListener('input', function() {
                scheduleFilterUpdate();
            });
            document.getElementById('filter-context').addEventListener('change', function() {
                scheduleFilterUpdate();
            });
            
            // Reset filtri
            document.getElementById('btn-reset-filters').addEventListener('click', function() {
                document.getElementById('filter-severity').selectedIndex = -1;
                document.getElementById('filter-file').value = '';
                document.getElementById('filter-context').selectedIndex = -1;
                currentFilters = {
                    severity: ['fatal', 'error', 'warning'],
                    file: '',
                    context: [],
                    limit: 1000,
                    offset: 0
                };
                // Ripristina selezioni di default
                var severitySelect = document.getElementById('filter-severity');
                var warningOption = Array.from(severitySelect.options).find(function(opt) { return opt.value === 'warning'; });
                if (warningOption) {
                    warningOption.selected = true;
                }
                initGrid();
            });
            
            // Export CSV
            document.getElementById('btn-export-csv').addEventListener('click', function() {
                var url = restUrl + '?format=csv';
                if (currentFilters.severity.length > 0) {
                    url += '&severity=' + currentFilters.severity.join(',');
                }
                if (currentFilters.file) {
                    url += '&file=' + encodeURIComponent(currentFilters.file);
                }
                if (currentFilters.context.length > 0) {
                    url += '&context=' + currentFilters.context.join(',');
                }
                url += '&limit=10000'; // Export tutti i risultati
                
                window.open(url + '&_wpnonce=' + nonce, '_blank');
            });
            
            // Export JSON
            document.getElementById('btn-export-json').addEventListener('click', function() {
                var url = restUrl + '?format=json';
                if (currentFilters.severity.length > 0) {
                    url += '&severity=' + currentFilters.severity.join(',');
                }
                if (currentFilters.file) {
                    url += '&file=' + encodeURIComponent(currentFilters.file);
                }
                if (currentFilters.context.length > 0) {
                    url += '&context=' + currentFilters.context.join(',');
                }
                url += '&limit=10000'; // Export tutti i risultati
                
                fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-WP-Nonce': nonce
                    }
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    var blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
                    var url = URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = 'php-errors-' + new Date().toISOString().split('T')[0] + '.json';
                    a.click();
                    URL.revokeObjectURL(url);
                })
                .catch(function(error) {
                    alert('Errore durante l\'export JSON: ' + error.message);
                });
            });
            
            // Carica dati iniziali solo quando il tab è visibile
            // Usa un observer per rilevare quando il tab diventa attivo
            var logViewerInitialized = false;
            var observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        var logViewerSection = document.getElementById('log-viewer');
                        if (logViewerSection && logViewerSection.classList.contains('active') && !logViewerInitialized) {
                            logViewerInitialized = true;
                            // Piccolo delay per assicurarsi che il DOM sia pronto
                            setTimeout(function() {
                                initGrid();
                            }, 100);
                        }
                    }
                });
            });
            
            // Osserva cambiamenti nella sezione log-viewer
            var logViewerSection = document.getElementById('log-viewer');
            if (logViewerSection) {
                observer.observe(logViewerSection, {
                    attributes: true,
                    attributeFilter: ['class']
                });
                
                // Se il tab è già attivo (visibile al caricamento), inizializza subito
                if (logViewerSection.classList.contains('active')) {
                    logViewerInitialized = true;
                    setTimeout(function() {
                        initGrid();
                    }, 100);
                }
            } else {
                // Fallback: se la sezione non esiste ancora, aspetta un po' e riprova
                setTimeout(function() {
                    var section = document.getElementById('log-viewer');
                    if (section && !logViewerInitialized) {
                        logViewerInitialized = true;
                        initGrid();
                    }
                }, 500);
            }
        })();
        </script>
        <?php
    }
}

