<?php
namespace gik25microdata\HealthCheck\Check;

use gik25microdata\Logs\Analysis\CloudwaysLogParser;
use gik25microdata\HealthCheck\HealthCheckConstants;
use gik25microdata\HealthCheck\Service\LoggingGuard;
use gik25microdata\HealthCheck\Service\ContextSummary;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check per analizzare log Cloudways
 */
class LogsCheck
{
    /**
     * Esegue il check
     * 
     * @return array Risultato del check
     */
    public static function run(): array
    {
        // Salva stato originale per ripristino sicuro
        $original_state = LoggingGuard::disable();
        
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
            $context_summary = ContextSummary::build($analysis['issues'] ?? []);
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
            LoggingGuard::restore($original_state);
            if (isset($old_memory_limit)) {
                @ini_set('memory_limit', $old_memory_limit);
            }
            if (isset($old_max_execution_time)) {
                @set_time_limit((int)$old_max_execution_time);
            }
        }
    }
}

