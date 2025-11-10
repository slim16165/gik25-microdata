/**
 * Log Viewer JavaScript
 * Gestione UI per visualizzazione avanzata errori PHP con Grid.js
 */

(function() {
    'use strict';

    // Variabili globali (saranno popolate da wp_localize_script)
    var restUrl = window.logViewerData?.restUrl || '';
    var nonce = window.logViewerData?.nonce || '';
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
        
        var loadingEl = document.getElementById('log-viewer-loading');
        var infoEl = document.getElementById('log-viewer-info');
        
        if (loadingEl) {
            loadingEl.style.display = 'inline';
        }
        if (infoEl) {
            infoEl.style.display = 'none';
        }
        
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
            if (loadingEl) {
                loadingEl.style.display = 'none';
            }
            
            if (data.error) {
                if (infoEl) {
                    infoEl.textContent = 'Errore: ' + (data.message || data.details || 'Errore sconosciuto');
                    infoEl.style.display = 'inline';
                    infoEl.style.color = '#dc3232';
                }
                // Mostra messaggio di errore anche nella griglia
                if (!grid) {
                    var container = document.getElementById('log-viewer-grid');
                    if (container) {
                        container.innerHTML = '<div class="log-viewer-error-message"><strong>Errore:</strong> ' + escapeHtml(data.message || data.details || 'Errore sconosciuto') + '</div>';
                    }
                }
                return;
            }
            
            var total = data.total || 0;
            var errors = data.errors || [];
            
            var reasonNote = (data.debug && data.debug.reason) ? ' | ' + data.debug.reason : '';
            if (infoEl) {
                infoEl.textContent = 'Totale: ' + total + ' errori | Mostrati: ' + errors.length + reasonNote;
                infoEl.style.display = 'inline';
                infoEl.style.color = '#666';
            }
            
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
                    container.innerHTML = '<div class="log-viewer-empty-message"><strong>Nessun errore trovato</strong><br><small>Prova a modificare i filtri o verifica che ci siano errori PHP nei log.</small></div>';
                }
                return;
            }
            
            // Verifica che Grid.js sia disponibile
            if (typeof gridjs === 'undefined') {
                console.error('Grid.js non caricato');
                var container = document.getElementById('log-viewer-grid');
                if (container) {
                    container.innerHTML = '<div class="log-viewer-error-message"><strong>Errore:</strong> Grid.js non è stato caricato correttamente.</div>';
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
            if (loadingEl) {
                loadingEl.style.display = 'none';
            }
            var errorMessage = 'Errore: ' + error.message;
            if (infoEl) {
                infoEl.textContent = errorMessage;
                infoEl.style.display = 'inline';
                infoEl.style.color = '#dc3232';
            }
            
            // Mostra messaggio di errore nella griglia
            var container = document.getElementById('log-viewer-grid');
            if (container) {
                container.innerHTML = '<div class="log-viewer-error-message"><strong>Errore durante il caricamento:</strong><br>' + escapeHtml(error.message) + '<br><br><small>Verifica che:<br>1. L\'endpoint REST sia registrato correttamente<br>2. Il nonce sia valido<br>3. I permessi siano corretti (manage_options)</small></div>';
            }
            
            console.error('Log Viewer Error:', error);
            console.error('REST URL:', url);
            console.error('Nonce:', nonce);
        });
    }
    
    // Crea contenuto dettagli
    function createDetailsContent(error) {
        var html = '<div class="log-detail-item"><strong>ID:</strong> ' + (error.id || '-') + '</div>';
        
        if (error.message) {
            html += '<div class="log-detail-item"><strong>Messaggio:</strong><br><code class="log-detail-code">' + escapeHtml(error.message) + '</code></div>';
        }
        
        if (error.file) {
            html += '<div class="log-detail-item"><strong>File:</strong> ' + escapeHtml(error.file) + '</div>';
        }
        
        if (error.line) {
            html += '<div class="log-detail-item"><strong>Linea:</strong> ' + error.line + '</div>';
        }
        
        if (error.files && error.files.length > 1) {
            html += '<div class="log-detail-item"><strong>Altri file:</strong> ' + error.files.slice(1).map(function(f) { return escapeHtml(f); }).join(', ') + '</div>';
        }
        
        if (error.stack_trace && error.stack_trace.length > 0) {
            html += '<div class="log-detail-item"><strong>Stack Trace:</strong><pre>' + escapeHtml(error.stack_trace.join('\n')) + '</pre></div>';
        }
        
        if (error.contexts && error.contexts.length > 0) {
            html += '<div class="log-detail-item"><strong>Contesti:</strong> ' + error.contexts.join(', ') + '</div>';
        }
        
        if (error.first_seen) {
            html += '<div class="log-detail-item"><strong>Prima occorrenza:</strong> ' + new Date(error.first_seen * 1000).toLocaleString('it-IT') + '</div>';
        }
        
        if (error.last_seen) {
            html += '<div class="log-detail-item"><strong>Ultima occorrenza:</strong> ' + new Date(error.last_seen * 1000).toLocaleString('it-IT') + '</div>';
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
        if (severitySelect) {
            currentFilters.severity = Array.from(severitySelect.selectedOptions).map(function(opt) { return opt.value; });
        }
        var fileInput = document.getElementById('filter-file');
        if (fileInput) {
            currentFilters.file = fileInput.value.trim();
        }
        var contextSelect = document.getElementById('filter-context');
        if (contextSelect) {
            currentFilters.context = Array.from(contextSelect.selectedOptions).map(function(opt) { return opt.value; });
        }
        currentFilters.offset = 0;
    }
    
    // Inizializza quando il DOM è pronto
    function initLogViewer() {
        // Applica filtri (aggiorna immediatamente)
        var applyBtn = document.getElementById('btn-apply-filters');
        if (applyBtn) {
            applyBtn.addEventListener('click', function() {
                updateFiltersFromUI();
                initGrid();
            });
        }
        
        // Listeners per aggiornamento automatico
        var severitySelect = document.getElementById('filter-severity');
        if (severitySelect) {
            severitySelect.addEventListener('change', function() {
                scheduleFilterUpdate();
            });
        }
        
        var fileInput = document.getElementById('filter-file');
        if (fileInput) {
            fileInput.addEventListener('input', function() {
                scheduleFilterUpdate();
            });
        }
        
        var contextSelect = document.getElementById('filter-context');
        if (contextSelect) {
            contextSelect.addEventListener('change', function() {
                scheduleFilterUpdate();
            });
        }
        
        // Reset filtri
        var resetBtn = document.getElementById('btn-reset-filters');
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                if (severitySelect) {
                    severitySelect.selectedIndex = -1;
                }
                if (fileInput) {
                    fileInput.value = '';
                }
                if (contextSelect) {
                    contextSelect.selectedIndex = -1;
                }
                currentFilters = {
                    severity: ['fatal', 'error', 'warning'],
                    file: '',
                    context: [],
                    limit: 1000,
                    offset: 0
                };
                // Ripristina selezioni di default
                if (severitySelect) {
                    var warningOption = Array.from(severitySelect.options).find(function(opt) { return opt.value === 'warning'; });
                    if (warningOption) {
                        warningOption.selected = true;
                    }
                }
                initGrid();
            });
        }
        
        // Export CSV
        var exportCsvBtn = document.getElementById('btn-export-csv');
        if (exportCsvBtn) {
            exportCsvBtn.addEventListener('click', function() {
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
        }
        
        // Export JSON
        var exportJsonBtn = document.getElementById('btn-export-json');
        if (exportJsonBtn) {
            exportJsonBtn.addEventListener('click', function() {
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
        }
        
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
    }
    
    // Inizializza quando il DOM è pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLogViewer);
    } else {
        initLogViewer();
    }
})();

