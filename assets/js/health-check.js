/**
 * Health Check JavaScript
 * Gestione UI per la pagina di Health Check
 */

(function($) {
    'use strict';

    /**
     * Inizializza Health Check UI
     */
    function initHealthCheck() {
        var healthCheckData = window.healthCheckData || {};
        var $resultsContainer = $('#health-check-results');
        var copyFeedbackTimeout = null;

        /**
         * Gestisce la UI dei tab
         */
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

        /**
         * Imposta lo stato di esecuzione del check
         */
        function setRunningState(isRunning) {
            var $button = $('#run-health-check');
            if (isRunning) {
                $button.data('original-html', $button.html());
                $button.prop('disabled', true).html('⏳ ' + (healthCheckData.i18n?.running || 'In esecuzione...'));
            } else {
                var original = $button.data('original-html');
                if (original) {
                    $button.html(original);
                }
                $button.prop('disabled', false);
            }
        }

        /**
         * Renderizza i risultati
         */
        function renderResults(html, activeTab) {
            $resultsContainer.html(html);
            bindHealthCheckUI(activeTab);
        }

        /**
         * Esegue l'health check via AJAX
         */
        $('#run-health-check').off('click').on('click', function() {
            setRunningState(true);
            var activeTab = $('.nav-tab-wrapper a.nav-tab-active').data('tab') || 'summary';

            $.ajax({
                url: ajaxurl,
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'gik25_health_check',
                    nonce: healthCheckData.nonce || '',
                    force_refresh: '1' // Forza refresh bypass cache
                }
            }).done(function(response) {
                if (response && response.success && response.data && response.data.html) {
                    renderResults(response.data.html, activeTab);
                } else {
                    var message = (response && response.data && response.data.message) 
                        ? response.data.message 
                        : (healthCheckData.i18n?.unknownError || 'Errore sconosciuto.');
                    window.alert((healthCheckData.i18n?.checkFailed || 'Health Check fallito: ') + message);
                }
            }).fail(function(xhr) {
                console.error('Health Check AJAX error', xhr);
                window.alert(healthCheckData.i18n?.ajaxError || 'Errore durante l\'esecuzione degli health check. Controlla la console per dettagli.');
            }).always(function() {
                setRunningState(false);
            });
        });

        /**
         * Copia risultati negli appunti
         */
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
                    $button.html('✅ ' + (healthCheckData.i18n?.copied || 'Copiato!'));
                    restoreButton();
                }).catch(function() {
                    fallbackCopy(text, $button, originalHtml, restoreButton);
                });
            } else {
                fallbackCopy(text, $button, originalHtml, restoreButton);
            }
        });

        /**
         * Fallback per copia negli appunti (browser vecchi)
         */
        function fallbackCopy(text, $button, originalHtml, restoreButton) {
            var $textarea = $('<textarea>', {
                text: text,
                css: { position: 'absolute', left: '-9999px', top: '0' }
            }).appendTo('body');

            $textarea.trigger('focus').trigger('select');
            try {
                document.execCommand('copy');
                $button.html('✅ ' + (healthCheckData.i18n?.copied || 'Copiato!'));
            } catch (err) {
                console.error('Clipboard fallback failed', err);
                window.alert(healthCheckData.i18n?.copyFailed || 'Impossibile copiare automaticamente. Copia manualmente i risultati.');
            }
            $textarea.remove();
            restoreButton();
        }

        /**
         * Formatta i risultati per la copia
         */
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

        // Inizializza UI
        bindHealthCheckUI('summary');
    }

    // Inizializza quando jQuery è pronto
    $(document).ready(initHealthCheck);

})(jQuery);

