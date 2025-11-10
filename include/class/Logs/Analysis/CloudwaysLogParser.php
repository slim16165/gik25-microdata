<?php
namespace gik25microdata\Logs\Analysis;

use gik25microdata\Logs\Resolver\LogSourceResolver;
use gik25microdata\Logs\Support\TimestampParser;
use gik25microdata\Logs\Support\ContextExtractor;
use gik25microdata\Logs\Support\TimezoneHelper;
use gik25microdata\Logs\Support\LogUtility;
use gik25microdata\Logs\Filter\ErrorFilter;
use gik25microdata\Logs\Reader\LogFileReader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Parser dei log Cloudways per rilevare problemi
 * 
 * Analizza:
 * - Nginx error/access logs
 * - Apache error/access logs
 * - PHP slow logs
 * - PHP error logs
 * - WordPress cron logs
 */
class CloudwaysLogParser
{
    // Metodi rimossi: spostati in classi dedicate
    // - get_ignorable_error_patterns() -> ErrorFilter::getIgnorablePatterns()
    // - should_ignore_error() -> ErrorFilter::shouldIgnore()
    // - extract_execution_context() -> ContextExtractor::extract()
    // - should_ignore_by_context() -> ErrorFilter::shouldIgnoreByContext()
    /**
     * Percorsi tipici dei file di log su Cloudways
     * 
     * DEPRECATO: Usa LogSourceResolver per discovery unificata.
     * Questo metodo mantiene compatibilità con codice esistente.
     * 
     * @return array Array con 'base' e file per tipo (compatibilità retroattiva)
     */
    private static function get_log_paths(): array
    {
        // Usa LogSourceResolver per discovery unificata
        $base = LogSourceResolver::find_logs_directory();
        
        if (empty($base)) {
            return [];
        }
        
        $paths = ['base' => $base];
        
        // Scopri tutti i file di log usando il resolver unificato
        // include_gz=false per compatibilità (i file .gz vengono gestiti separatamente)
        $discovered = LogSourceResolver::discover($base, false);
        
        // Raggruppa per tipo e prendi il primo file (più recente) di ogni tipo
        $by_type = [];
        foreach ($discovered as $file_info) {
            $type = $file_info['type'];
            if (!isset($by_type[$type])) {
                $by_type[$type] = [];
            }
            $by_type[$type][] = $file_info['path'];
        }
        
        // Per compatibilità, popola $paths con il primo file di ogni tipo
        foreach ($by_type as $type => $files) {
            if (!empty($files)) {
                $paths[$type] = $files[0]; // Il primo è già il più recente (ordinato da discover)
            }
        }
        
        // Per compatibilità retroattiva, aggiungi anche le chiavi _glob
        // (anche se ora usiamo direttamente LogSourceResolver::get_logs_by_type)
        // Queste chiavi vengono ancora usate da resolve_log_files() legacy
        $types_with_glob = ['php_error', 'nginx_error', 'nginx_access', 'apache_error', 'apache_access', 'php_access', 'php_slow', 'wp_cron'];
        foreach ($types_with_glob as $type) {
            // Le chiavi _glob non sono più necessarie, ma le manteniamo per compatibilità
            // Il codice legacy le userà, ma resolve_log_files() userà LogSourceResolver
        }
        
        return $paths;
    }
    
    /**
     * Raccoglie file reali (esclude .gz di default) dai pattern e li ordina per mtime (desc)
     * 
     * DEPRECATO: Usa LogSourceResolver::get_logs_by_type() o get_logs_by_types().
     * Questo metodo mantiene compatibilità con codice legacy.
     * 
     * @param array $globs Array di pattern glob o file singoli (legacy)
     * @param bool $include_gz Se true, include anche file .gz (default: false)
     * @return array Array di percorsi file ordinati per mtime (più recenti prima)
     */
    private static function collect_log_files(array $globs, bool $include_gz = false): array
    {
        // Se $globs contiene chiavi _glob legacy, usa LogSourceResolver
        $base = LogSourceResolver::find_logs_directory();
        if (empty($base)) {
            return [];
        }
        
        $files = [];
        
        // Per compatibilità, gestisci ancora file singoli e pattern legacy
        foreach ($globs as $g) {
            if (empty($g)) {
                continue;
            }
            
            // Se è un file singolo (non contiene wildcard)
            if (strpos($g, '*') === false && strpos($g, '?') === false && strpos($g, '{') === false) {
                if (is_readable($g) && is_file($g)) {
                    if ($include_gz || !str_ends_with($g, '.gz')) {
                        $files[$g] = @filemtime($g) ?: 0;
                    }
                }
            }
            // Se contiene pattern con GLOB_BRACE legacy (es. '{pattern1,pattern2}'), 
            // usa LogSourceResolver invece di GLOB_BRACE
            elseif (strpos($g, '{') !== false) {
                // Estrai il tipo dal pattern se possibile, altrimenti cerca tutti i tipi
                // Questo è un fallback per pattern legacy con GLOB_BRACE
                $all_files = LogSourceResolver::discover($base, $include_gz);
                foreach ($all_files as $file_info) {
                    $files[$file_info['path']] = $file_info['mtime'];
                }
            } else {
                // Pattern glob semplice (senza brace)
                $matches = glob($g, GLOB_NOSORT) ?: [];
                foreach ($matches as $f) {
                    if (is_readable($f) && is_file($f)) {
                        if ($include_gz || !str_ends_with($f, '.gz')) {
                            $files[$f] = @filemtime($f) ?: 0;
                        }
                    }
                }
            }
        }
        
        // Ordina per mtime (più recenti prima)
        arsort($files);
        
        return array_keys($files);
    }
    
    /**
     * Risolve i file effettivi per un tipo di log combinando percorso singolo e glob
     * 
     * DEPRECATO: Usa direttamente LogSourceResolver::get_logs_by_type().
     * Questo metodo mantiene compatibilità con codice legacy.
     * 
     * @param array  $paths      Array dei percorsi individuati da get_log_paths()
     * @param string $single_key Chiave per il file principale (es. php_error)
     * @param string $glob_key   Chiave per il glob (es. php_error_glob) - IGNORATO, usa LogSourceResolver
     * @param bool   $include_gz Se includere file .gz
     * @return array Lista di file ordinati per mtime (più recenti prima)
     */
    private static function resolve_log_files(array $paths, string $single_key, string $glob_key, bool $include_gz = false): array
    {
        $base = $paths['base'] ?? null;
        if (empty($base)) {
            return [];
        }
        
        // Usa LogSourceResolver per discovery unificata
        // Mappa le chiavi legacy ai tipi del resolver
        $type_map = [
            'php_error' => 'php_error',
            'php_error_glob' => 'php_error',
            'nginx_error' => 'nginx_error',
            'nginx_err_glob' => 'nginx_error',
            'apache_error' => 'apache_error',
            'apache_err_glob' => 'apache_error',
            'nginx_access' => 'nginx_access',
            'nginx_acc_glob' => 'nginx_access',
            'apache_access' => 'apache_access',
            'apache_acc_glob' => 'apache_access',
            'php_access' => 'php_access',
            'php_acc_glob' => 'php_access',
            'php_slow' => 'php_slow',
            'php_slow_glob' => 'php_slow',
            'wp_cron' => 'wp_cron',
            'wp_cron_glob' => 'wp_cron',
        ];
        
        $type = $type_map[$single_key] ?? $single_key;
        
        // Usa LogSourceResolver per ottenere i file
        return LogSourceResolver::get_logs_by_type($base, $type, $include_gz);
    }
    
    // Metodo rimosso: spostato in LogFileReader::tailFromFiles()
    
    /**
     * Analizza un file di log specifico (Apache/PHP/WordPress error log)
     * 
     * @param string $log_file_path Percorso completo al file di log da analizzare
     * @param int $max_lines Numero massimo di righe da analizzare (default: 5000)
     * @return array Risultati dell'analisi con errori e warning trovati
     */
    public static function analyze_specific_log_file(string $log_file_path, int $max_lines = 5000): array
    {
        // SICUREZZA: disabilita completamente il logging durante l'analisi
        $old_error_reporting = error_reporting(0);
        $old_display_errors = ini_get('display_errors');
        $old_log_errors = ini_get('log_errors');
        ini_set('display_errors', '0');
        ini_set('log_errors', '0');
        
        try {
            // Normalizza il percorso (gestisce sia Windows che Linux)
            $log_file_path = str_replace('\\', '/', $log_file_path);
            
            if (!file_exists($log_file_path) || !is_readable($log_file_path)) {
                return [
                    'status' => 'error',
                    'message' => 'File di log non trovato o non leggibile',
                    'file' => $log_file_path,
                    'issues' => [],
                ];
            }
            
            // Determina il tipo di log dal nome del file
            $filename = basename($log_file_path);
            $is_apache = stripos($filename, 'apache') !== false;
            $is_php = stripos($filename, 'php') !== false;
            $is_wordpress = stripos($filename, 'wordpress') !== false;
            
            $issues = [];
            
            // Analizza come Apache/PHP error log (il formato è simile)
            if ($is_apache || $is_php || $is_wordpress) {
                // Usa il metodo di analisi PHP errors che gestisce meglio i pattern WordPress
                $php_issues = self::analyze_php_errors_extended($log_file_path, $max_lines);
                $issues = array_merge($issues, $php_issues);
                
                // Aggiungi anche analisi Apache se sembra un log Apache
                if ($is_apache) {
                    $apache_issues = self::analyze_apache_errors_extended($log_file_path, $max_lines);
                    $issues = array_merge($issues, $apache_issues);
                }
            }
            
            // Conta errori e warning
            $total_errors = 0;
            $total_warnings = 0;
            foreach ($issues as $issue) {
                if ($issue['severity'] === 'error') {
                    $total_errors++;
                } else {
                    $total_warnings++;
                }
            }
            
            // Determina status complessivo
            if ($total_errors > 0) {
                $status = 'error';
                $message = sprintf('Trovati %d problema/i critico/i e %d warning nel file di log', $total_errors, $total_warnings);
            } elseif ($total_warnings > 0) {
                $status = 'warning';
                $message = sprintf('Trovati %d warning nel file di log', $total_warnings);
            } else {
                $status = 'success';
                $message = 'Nessun problema rilevato nel file di log';
            }
            
            return [
                'status' => $status,
                'message' => $message,
                'file' => $log_file_path,
                'total_errors' => $total_errors,
                'total_warnings' => $total_warnings,
                'issues' => $issues,
            ];
            
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'message' => 'Errore durante l\'analisi del file di log: ' . $e->getMessage(),
                'file' => $log_file_path,
                'issues' => [],
            ];
        } finally {
            // Ripristina error reporting
            error_reporting($old_error_reporting);
            ini_set('display_errors', $old_display_errors);
            ini_set('log_errors', $old_log_errors);
        }
    }
    
    /**
     * Versione estesa di analyze_php_errors che accetta un percorso file e max_lines
     */
    private static function analyze_php_errors_extended(string $log_path, int $max_lines = 5000): array
    {
        // Usa analyze_php_errors() che ha già tutta la logica per estrarre file, riga e stack trace
        // Passa 0 come cutoff_hours per analizzare tutto il file
        return self::analyze_php_errors($log_path, 0);
    }
    
    /**
     * Versione estesa di analyze_apache_errors che accetta max_lines
     */
    private static function analyze_apache_errors_extended(string $log_path, int $max_lines = 5000): array
    {
        $issues = [];
        $lines = LogFileReader::readTail($log_path, $max_lines);
        
        if (empty($lines)) {
            return $issues;
        }
        
        $error_counts = [];
        $cutoff_time = 0; // Analizza tutto
        
        $critical_patterns = [
            '/PHP Fatal error/i',
            '/PHP Parse error/i',
            '/PHP Warning/i',
            '/Premature end of script headers/i',
            '/Maximum execution time/i',
            '/AH01071/i', // Apache error codes
        ];
        
        foreach ($lines as $line) {
            $timestamp = TimestampParser::parseApache($line);
            if ($timestamp && $timestamp < $cutoff_time) {
                continue;
            }
            
            foreach ($critical_patterns as $pattern) {
                if (preg_match($pattern, $line)) {
                    $error_key = $pattern;
                    if (!isset($error_counts[$error_key])) {
                        $error_counts[$error_key] = ['count' => 0, 'examples' => []];
                    }
                    $error_counts[$error_key]['count']++;
                    // Raccogli più esempi (fino a 10) per avere varietà
                    if (count($error_counts[$error_key]['examples']) < 10) {
                        $truncated = LogUtility::truncateLine($line, 200);
                        // Evita duplicati esatti
                        if (!in_array($truncated, $error_counts[$error_key]['examples'])) {
                            $error_counts[$error_key]['examples'][] = $truncated;
                        }
                    }
                }
            }
        }
        
        foreach ($error_counts as $pattern => $data) {
            if ($data['count'] >= 1) {
                $clean_pattern = preg_replace('/_\w+$/', '', $pattern);
                $pattern_name = LogUtility::getPatternName($clean_pattern);
                
                $base_severity = $clean_pattern === '/PHP Fatal error/i' || $clean_pattern === '/PHP Parse error/i' ? 'error' : 'warning';
                
                $message = sprintf('%s: %d occorrenze', $pattern_name, $data['count']);
                
                $issues[] = [
                    'type' => 'Apache Error',
                    'severity' => $base_severity,
                    'message' => $message,
                    'count' => $data['count'],
                    'examples' => $data['examples'],
                ];
            }
        }
        
        return $issues;
    }
    
    /**
     * Analizza tutti i log e rileva problemi
     * SICURA: gestisce errori e limita risorse per evitare problemi
     */
    public static function analyze_logs(): array
    {
        // SICUREZZA: disabilita completamente il logging durante l'analisi
        $old_error_reporting = error_reporting(0);
        $old_display_errors = ini_get('display_errors');
        $old_log_errors = ini_get('log_errors');
        ini_set('display_errors', '0');
        ini_set('log_errors', '0'); // DISABILITA COMPLETAMENTE IL LOGGING
        
        try {
            // Limita memoria e tempo di esecuzione
            $old_memory_limit = ini_get('memory_limit');
            $old_max_execution_time = ini_get('max_execution_time');
            ini_set('memory_limit', '256M');
            set_time_limit(30); // Max 30 secondi
            
            $paths = self::get_log_paths();
            
            if (empty($paths) || empty($paths['base'])) {
                return [
                    'status' => 'warning',
                    'message' => 'Directory logs non trovata',
                    'issues' => [],
                    'details' => 'Impossibile trovare la directory logs. Verifica che il percorso sia corretto per Cloudways.',
                ];
            }
            
            $issues = [];
            $total_errors = 0;
            $total_warnings = 0;
        
        // Analizza Nginx error logs
        if (!empty($paths['nginx_error'])) {
            $nginx_issues = self::analyze_nginx_errors($paths['nginx_error']);
            $issues = array_merge($issues, $nginx_issues);
            foreach ($nginx_issues as $issue) {
                if ($issue['severity'] === 'error') {
                    $total_errors++;
                } else {
                    $total_warnings++;
                }
            }
        }
        
        // Analizza Nginx access logs (per errori 5xx)
        if (!empty($paths['nginx_access'])) {
            $nginx_access_issues = self::analyze_nginx_access($paths['nginx_access']);
            $issues = array_merge($issues, $nginx_access_issues);
            foreach ($nginx_access_issues as $issue) {
                if ($issue['severity'] === 'error') {
                    $total_errors++;
                } else {
                    $total_warnings++;
                }
            }
        }
        
        // Analizza Apache error logs
        if (!empty($paths['apache_error'])) {
            $apache_issues = self::analyze_apache_errors($paths['apache_error']);
            $issues = array_merge($issues, $apache_issues);
            foreach ($apache_issues as $issue) {
                if ($issue['severity'] === 'error') {
                    $total_errors++;
                } else {
                    $total_warnings++;
                }
            }
        }
        
        // Analizza Apache access logs (per errori 5xx)
        if (!empty($paths['apache_access'])) {
            $apache_access_issues = self::analyze_apache_access($paths['apache_access']);
            $issues = array_merge($issues, $apache_access_issues);
            foreach ($apache_access_issues as $issue) {
                if ($issue['severity'] === 'error') {
                    $total_errors++;
                } else {
                    $total_warnings++;
                }
            }
        }
        
        // Analizza PHP slow logs
        if (!empty($paths['php_slow'])) {
            $php_slow_issues = self::analyze_php_slow($paths['php_slow']);
            $issues = array_merge($issues, $php_slow_issues);
            foreach ($php_slow_issues as $issue) {
                if ($issue['severity'] === 'error') {
                    $total_errors++;
                } else {
                    $total_warnings++;
                }
            }
        }
        
        // Analizza PHP error logs
        $php_error_files = self::resolve_log_files($paths, 'php_error', 'php_error_glob');
        if (!empty($php_error_files)) {
            $php_error_issues = self::analyze_php_errors($php_error_files[0]);
            $issues = array_merge($issues, $php_error_issues);
            foreach ($php_error_issues as $issue) {
                if ($issue['severity'] === 'error') {
                    $total_errors++;
                } else {
                    $total_warnings++;
                }
            }
        }
        
        // Analizza WordPress cron logs
        if (!empty($paths['wp_cron'])) {
            $cron_issues = self::analyze_wp_cron($paths['wp_cron']);
            $issues = array_merge($issues, $cron_issues);
            foreach ($cron_issues as $issue) {
                if ($issue['severity'] === 'error') {
                    $total_errors++;
                } else {
                    $total_warnings++;
                }
            }
        }
        
        // Determina status complessivo
        if ($total_errors > 0) {
            $status = 'error';
            $message = sprintf('Trovati %d problema/i critico/i e %d warning nei log', $total_errors, $total_warnings);
        } elseif ($total_warnings > 0) {
            $status = 'warning';
            $message = sprintf('Trovati %d warning nei log', $total_warnings);
        } else {
            $status = 'success';
            $message = 'Nessun problema rilevato nei log (ultime 24 ore)';
        }
        
        // Prepara dettagli
        $details = "Directory logs: " . $paths['base'] . "\n\n";
        $details .= "File analizzati:\n";
        foreach ($paths as $key => $path) {
            if ($key !== 'base' && !empty($path)) {
                $details .= "- " . $key . ": " . basename($path) . "\n";
            }
        }
        
        if (!empty($issues)) {
            $details .= "\nProblemi rilevati:\n";
            foreach ($issues as $issue) {
                $severity_icon = $issue['severity'] === 'error' ? '❌' : '⚠️';
                $details .= sprintf(
                    "%s [%s] %s: %s\n",
                    $severity_icon,
                    strtoupper($issue['severity']),
                    $issue['type'],
                    $issue['message']
                );
                if (!empty($issue['count'])) {
                    $details .= "   Occorrenze: " . $issue['count'] . "\n";
                }
                // Mostra informazioni sul contesto se disponibili
                if (!empty($issue['contexts'])) {
                    $context_labels = [
                        'wp_cli' => 'WP-CLI',
                        'ajax' => 'AJAX',
                        'wp_cron' => 'WP-CRON',
                        'frontend' => 'Frontend',
                        'backend' => 'Backend',
                        'rest_api' => 'REST API',
                        'unknown' => 'Unknown',
                    ];
                    $contexts_labeled = array_map(function($ctx) use ($context_labels) {
                        return $context_labels[$ctx] ?? $ctx;
                    }, $issue['contexts']);
                    $details .= "   Contesto esecuzione: " . implode(', ', $contexts_labeled) . "\n";
                }
                if (!empty($issue['context']['details']['script'])) {
                    $details .= "   Script: " . $issue['context']['details']['script'] . "\n";
                }
                if (!empty($issue['examples'])) {
                    // Per errori PHP, mostra stack trace completo
                    if ($issue['type'] === 'PHP Error' && isset($issue['examples'][0]['stack_trace'])) {
                        $details .= "   Esempi:\n";
                        foreach (array_slice($issue['examples'], 0, 3) as $example) {
                            if (is_array($example)) {
                                $details .= "      Messaggio: " . ($example['message'] ?? 'N/A') . "\n";
                                if (!empty($example['file'])) {
                                    $details .= "      File: " . $example['file'];
                                    if (!empty($example['line'])) {
                                        $details .= ":" . $example['line'];
                                    }
                                    $details .= "\n";
                                }
                                if (!empty($example['context'])) {
                                    $context_labels = [
                                        'wp_cli' => 'WP-CLI',
                                        'ajax' => 'AJAX',
                                        'wp_cron' => 'WP-CRON',
                                        'frontend' => 'Frontend',
                                        'backend' => 'Backend',
                                        'rest_api' => 'REST API',
                                        'unknown' => 'Unknown',
                                    ];
                                    $context_label = $context_labels[$example['context']] ?? $example['context'];
                                    $details .= "      Contesto: " . $context_label . "\n";
                                }
                                if (!empty($example['stack_trace'])) {
                                    $details .= "      Stack Trace:\n";
                                    foreach (array_slice($example['stack_trace'], 0, 10) as $trace_line) {
                                        $details .= "         " . $trace_line . "\n";
                                    }
                                    if (count($example['stack_trace']) > 10) {
                                        $details .= "         ... (" . (count($example['stack_trace']) - 10) . " altre righe)\n";
                                    }
                                }
                                $details .= "\n";
                            } else {
                                // Fallback per formato vecchio (stringa)
                                $details .= "      - " . $example . "\n";
                            }
                        }
                        if (count($issue['examples']) > 3) {
                            $details .= "      (+" . (count($issue['examples']) - 3) . " altri esempi)\n";
                        }
                    } else {
                        // Formato standard per altri tipi di errori
                        // Mostra più esempi in base al numero di occorrenze
                        $example_count = 3; // Default
                        if (($issue['count'] ?? 0) > 100) {
                            $example_count = 5; // Molte occorrenze = più esempi
                        } elseif (($issue['count'] ?? 0) > 50) {
                            $example_count = 4;
                        }
                        // Rimuovi duplicati per mostrare varietà
                        $unique_examples = array_values(array_unique($issue['examples']));
                        $examples_to_show = array_slice($unique_examples, 0, $example_count);
                        
                        $details .= "   Esempi:\n";
                        foreach ($examples_to_show as $example_line) {
                            if (is_string($example_line)) {
                                $details .= "      - " . $example_line . "\n";
                            } else {
                                $details .= "      - " . print_r($example_line, true) . "\n";
                            }
                        }
                        if (count($unique_examples) > $example_count) {
                            $details .= "      (+"
                                . (count($unique_examples) - $example_count)
                                . " altri)\n";
                        }
                    }
                }
            }
        }
        
            return [
                'status' => $status,
                'message' => $message,
                'issues' => $issues,
                'total_errors' => $total_errors,
                'total_warnings' => $total_warnings,
                'details' => $details,
                'log_paths' => $paths,
            ];
            
        } catch (\Throwable $e) {
            // NON loggare l'errore - questo eviterebbe loop infiniti
            return [
                'status' => 'warning',
                'message' => 'Errore durante l\'analisi (parser disabilitato per sicurezza)',
                'issues' => [],
                'total_errors' => 0,
                'total_warnings' => 1,
                'details' => 'Il parser è stato disabilitato per evitare la generazione di log infiniti.',
                'log_paths' => [],
            ];
        } finally {
            // RIPRISTINA SEMPRE le impostazioni
            error_reporting($old_error_reporting ?? E_ALL);
            ini_set('display_errors', $old_display_errors ?? '1');
            ini_set('log_errors', $old_log_errors ?? '1');
            ini_set('memory_limit', $old_memory_limit ?? '128M');
            set_time_limit($old_max_execution_time ?? 30);
        }
    }
    
    /**
     * Analizza log errori Nginx
     */
    private static function analyze_nginx_errors(string $log_path): array
    {
        $issues = [];
        $lines = LogFileReader::readTail($log_path, 1000); // Ultime 1000 righe
        
        if (empty($lines)) {
            return $issues;
        }
        
        $error_counts = [];
        $recent_errors = [];
        $critical_errors = [];
        
        // Pattern per errori critici
        $critical_patterns = [
            '/upstream.*closed connection/i',
            '/connect.*failed/i',
            '/timeout/i',
            '/502 Bad Gateway/i',
            '/503 Service Unavailable/i',
            '/504 Gateway Timeout/i',
            '/500 Internal Server Error/i',
        ];
        
        // Analizza ultime 24 ore
        $cutoff_time = time() - (24 * 60 * 60);
        
        foreach ($lines as $line) {
            // Estrai timestamp (formato: 2025/11/08 04:13:03)
            $timestamp = TimestampParser::parseNginx($line);
            if ($timestamp && $timestamp < $cutoff_time) {
                continue; // Skip errori vecchi
            }
            
            // Conta errori per tipo
            foreach ($critical_patterns as $pattern) {
                if (preg_match($pattern, $line)) {
                    $error_key = $pattern;
                    if (!isset($error_counts[$error_key])) {
                        $error_counts[$error_key] = 0;
                        $critical_errors[$error_key] = [];
                    }
                    $error_counts[$error_key]++;
                    if (count($critical_errors[$error_key]) < 3) {
                        $critical_errors[$error_key][] = LogUtility::truncateLine($line, 150);
                    }
                }
            }
            
            // Raccogli altri errori recenti
            if (preg_match('/\[error\]/', $line)) {
                $recent_errors[] = LogUtility::truncateLine($line, 150);
                if (count($recent_errors) > 10) {
                    array_shift($recent_errors);
                }
            }
        }
        
        // Crea issue per errori critici frequenti
        foreach ($error_counts as $pattern => $count) {
            if ($count >= 5) { // Soglia: almeno 5 occorrenze
                $pattern_name = LogUtility::getPatternName($pattern);
                $issues[] = [
                    'type' => 'Nginx Error',
                    'severity' => 'error',
                    'message' => sprintf('%s: %d occorrenze nelle ultime 24 ore', $pattern_name, $count),
                    'count' => $count,
                    'examples' => array_slice($critical_errors[$pattern], 0, 5), // Mostra fino a 5 esempi
                ];
            }
        }
        
        // Warning se ci sono molti errori generici
        if (count($recent_errors) >= 20) {
            $issues[] = [
                'type' => 'Nginx Error',
                'severity' => 'warning',
                'message' => sprintf('Molti errori Nginx rilevati: %d nelle ultime 24 ore', count($recent_errors)),
                'count' => count($recent_errors),
                'examples' => array_slice($recent_errors, 0, 5), // Mostra fino a 5 esempi
            ];
        }
        
        return $issues;
    }
    
    /**
     * Analizza log access Nginx per errori 5xx
     */
    private static function analyze_nginx_access(string $log_path): array
    {
        $issues = [];
        $lines = LogFileReader::readTail($log_path, 5000); // Ultime 5000 righe (più righe perché access log è più verboso)
        
        if (empty($lines)) {
            return $issues;
        }
        
        $status_5xx = [];
        $cutoff_time = time() - (24 * 60 * 60);
        
        foreach ($lines as $line) {
            // Pattern tipico access log: IP - - [timestamp] "method path protocol" status size
            if (preg_match('/" (\d{3}) /', $line, $matches)) {
                $status = (int)$matches[1];
                
                if ($status >= 500 && $status < 600) {
                    // Estrai timestamp
                    $timestamp = TimestampParser::parseNginxAccess($line);
                    if ($timestamp && $timestamp < $cutoff_time) {
                        continue;
                    }
                    
                    $status_key = (string)$status;
                    if (!isset($status_5xx[$status_key])) {
                        $status_5xx[$status_key] = ['count' => 0, 'examples' => []];
                    }
                    $status_5xx[$status_key]['count']++;
                    
                    // Raccogli più esempi (fino a 8) per avere varietà
                    if (count($status_5xx[$status_key]['examples']) < 8) {
                        $truncated = LogUtility::truncateLine($line, 150);
                        // Evita duplicati esatti
                        if (!in_array($truncated, $status_5xx[$status_key]['examples'])) {
                            $status_5xx[$status_key]['examples'][] = $truncated;
                        }
                    }
                }
            }
        }
        
        // Crea issue per status 5xx
        foreach ($status_5xx as $status => $data) {
            if ($data['count'] >= 3) { // Soglia: almeno 3 occorrenze
                $issues[] = [
                    'type' => 'HTTP Error ' . $status,
                    'severity' => 'error',
                    'message' => sprintf('Errori HTTP %s: %d occorrenze nelle ultime 24 ore', $status, $data['count']),
                    'count' => $data['count'],
                    'examples' => $data['examples'],
                ];
            }
        }
        
        return $issues;
    }
    
    /**
     * Analizza log errori Apache
     */
    private static function analyze_apache_errors(string $log_path): array
    {
        $issues = [];
        $lines = self::read_log_tail($log_path, 1000);
        
        if (empty($lines)) {
            return $issues;
        }
        
        $error_counts = [];
        $cutoff_time = time() - (24 * 60 * 60);
        
        $critical_patterns = [
            '/PHP Fatal error/i',
            '/PHP Parse error/i',
            '/PHP Warning/i',
            '/Premature end of script headers/i',
            '/Maximum execution time/i',
        ];
        
        foreach ($lines as $line) {
            $timestamp = TimestampParser::parseApache($line);
            if ($timestamp && $timestamp < $cutoff_time) {
                continue;
            }
            
            foreach ($critical_patterns as $pattern) {
                if (preg_match($pattern, $line)) {
                    $error_key = $pattern;
                    if (!isset($error_counts[$error_key])) {
                        $error_counts[$error_key] = ['count' => 0, 'examples' => []];
                    }
                    $error_counts[$error_key]['count']++;
                    // Raccogli più esempi (fino a 8) per avere varietà
                    if (count($error_counts[$error_key]['examples']) < 8) {
                        $truncated = LogUtility::truncateLine($line, 150);
                        // Evita duplicati esatti
                        if (!in_array($truncated, $error_counts[$error_key]['examples'])) {
                            $error_counts[$error_key]['examples'][] = $truncated;
                        }
                    }
                }
            }
        }
        
        foreach ($error_counts as $pattern => $data) {
            if ($data['count'] >= 3) {
                // Estrai pattern pulito (rimuovi suffisso contesto)
                $clean_pattern = preg_replace('/_\w+$/', '', $pattern);
                $pattern_name = LogUtility::getPatternName($clean_pattern);
                
                // Determina severity base
                $base_severity = $clean_pattern === '/PHP Fatal error/i' || $clean_pattern === '/PHP Parse error/i' ? 'error' : 'warning';
                
                // Aggiungi informazioni sul contesto
                $context_info = '';
                if (!empty($data['contexts'])) {
                    $context_labels = [
                        'wp_cli' => 'WP-CLI',
                        'ajax' => 'AJAX',
                        'wp_cron' => 'WP-CRON',
                        'frontend' => 'Frontend',
                        'backend' => 'Backend',
                        'rest_api' => 'REST API',
                        'unknown' => 'Unknown',
                    ];
                    $contexts_labeled = array_map(function($ctx) use ($context_labels) {
                        return $context_labels[$ctx] ?? $ctx;
                    }, $data['contexts']);
                    $context_info = ' [' . implode(', ', $contexts_labeled) . ']';
                }
                
                $message = sprintf('%s: %d occorrenze%s', $pattern_name, $data['count'], $context_info);
                
                $issues[] = [
                    'type' => 'Apache Error',
                    'severity' => $base_severity,
                    'message' => $message,
                    'count' => $data['count'],
                    'examples' => $data['examples'],
                    'context' => $data['context'] ?? null,
                    'contexts' => $data['contexts'] ?? [],
                ];
            }
        }
        
        return $issues;
    }
    
    /**
     * Analizza log access Apache per errori 5xx
     */
    private static function analyze_apache_access(string $log_path): array
    {
        // Simile a analyze_nginx_access ma per Apache
        return self::analyze_nginx_access($log_path); // Stesso formato base
    }
    
    /**
     * Analizza PHP slow log
     */
    private static function analyze_php_slow(string $log_path): array
    {
        $issues = [];
        $lines = LogFileReader::readTail($log_path, 1000); // Leggi più righe per catturare entry complete
        
        if (empty($lines)) {
            return $issues;
        }
        
        $slow_requests = [];
        $cutoff_time = time() - (24 * 60 * 60);
        
        // PHP slow log formato: [08-Nov-2025 06:50:23] [pool gwvyrysadj] pid 1354065
        // script_filename = /home/.../index.php
        // [0x...] stack trace line
        // (le entry sono separate da righe vuote)
        $current_entry = null;
        $found_script = false;
        
        foreach ($lines as $line_num => $line) {
            $line = trim($line);
            
            // Riga vuota: fine entry corrente
            if (empty($line)) {
                if ($current_entry && $found_script && $current_entry['timestamp'] >= $cutoff_time) {
                    $slow_requests[] = $current_entry;
                }
                $current_entry = null;
                $found_script = false;
                continue;
            }
            
            // Nuova entry slow log - inizia con timestamp tra parentesi quadre
            // Pattern: [08-Nov-2025 06:50:23] [pool name] pid number
            if (preg_match('/^\[(\d{2}-\w{3}-\d{4} \d{2}:\d{2}:\d{2})\]\s+\[pool\s+(\w+)\]\s+pid\s+(\d+)/', $line, $matches)) {
                // Salva entry precedente se esiste
                if ($current_entry && $found_script && $current_entry['timestamp'] >= $cutoff_time) {
                    $slow_requests[] = $current_entry;
                }
                
                $timestamp_str = $matches[1];
                $timestamp = TimestampParser::parsePhpSlow($timestamp_str);
                
                if (!$timestamp || $timestamp < $cutoff_time) {
                    $current_entry = null;
                    $found_script = false;
                    continue;
                }
                
                $current_entry = [
                    'timestamp' => $timestamp,
                    'pool' => $matches[2],
                    'pid' => $matches[3],
                    'script' => '',
                    'script_full' => '',
                    'stack' => [],
                ];
                $found_script = false;
                continue;
            }
            
            // script_filename = /path/to/script
            if ($current_entry && preg_match('/^script_filename\s*=\s*(.+)$/', $line, $matches)) {
                $script = trim($matches[1]);
                $current_entry['script'] = basename($script);
                $current_entry['script_full'] = $script;
                $found_script = true;
                continue;
            }
            
            // Stack trace lines: [0x...] function() /path/to/file:line
            if ($current_entry && $found_script && preg_match('/^\[0x[\da-f]+\]\s+(.+)$/', $line, $matches)) {
                $stack_line = trim($matches[1]);
                // Prendi solo le prime righe dello stack (le più importanti)
                if (count($current_entry['stack']) < 5) {
                    $current_entry['stack'][] = $stack_line;
                }
                continue;
            }
        }
        
        // Aggiungi ultima entry se esiste (non c'è riga vuota finale)
        if ($current_entry && $found_script && $current_entry['timestamp'] >= $cutoff_time) {
            $slow_requests[] = $current_entry;
        }
        
        // Raggruppa per script
        $scripts = [];
        foreach ($slow_requests as $request) {
            $script = $request['script'] ?: 'unknown';
            if (!isset($scripts[$script])) {
                $scripts[$script] = ['count' => 0, 'examples' => []];
            }
            $scripts[$script]['count']++;
            // Raccogli più esempi per script diversi (fino a 8 per script per avere varietà)
            // Mostra varietà: diversi timestamp, eventuali informazioni dallo stack
            if (count($scripts[$script]['examples']) < 8 && !empty($request['script_full'])) {
                // Crea esempio più informativo
                $example_parts = [basename($request['script_full'])];
                
                // Aggiungi timestamp se disponibile
                if (!empty($request['timestamp'])) {
                    $example_parts[] = date('H:i:s', $request['timestamp']);
                }
                
                // Aggiungi prima riga dello stack se disponibile (può indicare il problema)
                if (!empty($request['stack']) && is_array($request['stack']) && count($request['stack']) > 0) {
                    $first_stack = $request['stack'][0];
                    // Estrai funzione/file dalla prima riga dello stack
                    if (preg_match('/([^\/]+\/[^:]+:\d+)/', $first_stack, $stack_matches)) {
                        $example_parts[] = $stack_matches[1];
                    }
                }
                
                $example = implode(' | ', $example_parts);
                
                // Evita duplicati esatti (ma permetti varietà con timestamp/stack diversi)
                if (!in_array($example, $scripts[$script]['examples'])) {
                    $scripts[$script]['examples'][] = $example;
                }
            }
        }
        
        // Crea issue per script con molte slow requests
        foreach ($scripts as $script => $data) {
            if ($data['count'] >= 5) {
                $issues[] = [
                    'type' => 'PHP Slow Request',
                    'severity' => 'warning',
                    'message' => sprintf('Script lento: %s (%d occorrenze nelle ultime 24 ore)', $script, $data['count']),
                    'count' => $data['count'],
                    'examples' => array_slice($data['examples'], 0, 5), // Mostra fino a 5 esempi per script
                ];
            }
        }
        
        // Warning generale se ci sono molte slow requests
        if (count($slow_requests) >= 10) {
            $issues[] = [
                'type' => 'PHP Slow Request',
                'severity' => 'warning',
                'message' => sprintf('Totale slow requests: %d nelle ultime 24 ore', count($slow_requests)),
                'count' => count($slow_requests),
                'examples' => [],
            ];
        }
        
        return $issues;
    }
    
    /**
     * Analizza PHP error log con estrazione stack trace completo e raggruppamento intelligente
     */
    private static function analyze_php_errors(string $log_path, int $cutoff_hours = 24): array
    {
        $issues = [];
        // Quando cutoff_hours=0, analizza tutto (passa un numero molto alto per leggere tutto)
        // read_log_tail() legge tutto il file quando lines > 10000
        $max_lines = $cutoff_hours == 0 ? 50000 : 5000;
        $lines = LogFileReader::readTail($log_path, $max_lines); // Leggi più righe per stack trace completi
        
        if (empty($lines)) {
            return $issues;
        }
        
        $error_groups = [];
        $cutoff_time = $cutoff_hours > 0 ? time() - ($cutoff_hours * 3600) : 0;
        
        $critical_patterns = [
            '/PHP Fatal error/i' => 'fatal',
            '/PHP Parse error/i' => 'parse',
            '/Uncaught Error/i' => 'error',
            '/Uncaught Exception/i' => 'exception',
            '/PHP Warning/i' => 'warning',
            '/WordPress database error/i' => 'database',
            '/Premature end of script headers/i' => 'headers',
            '/Maximum execution time/i' => 'timeout',
        ];
        
        $i = 0;
        while ($i < count($lines)) {
            $line = $lines[$i];
            $timestamp = TimestampParser::parseApache($line);
            
            // Salta righe vecchie
            if ($cutoff_time > 0 && $timestamp && $timestamp < $cutoff_time) {
                $i++;
                continue;
            }
            
            // Estrai contesto di esecuzione
            $execution_context = ContextExtractor::extract($line);
            
            // Verifica se l'errore dovrebbe essere ignorato
            $ignore_check = ErrorFilter::shouldIgnore($line, $execution_context);
            if ($ignore_check['ignore']) {
                $i++;
                continue;
            }
            
            // Verifica se ignorare per contesto
            if (ErrorFilter::shouldIgnoreByContext($execution_context)) {
                $i++;
                continue;
            }
            
            // Cerca pattern di errore
            $matched_pattern = null;
            $error_type = null;
            foreach ($critical_patterns as $pattern => $type) {
                if (preg_match($pattern, $line)) {
                    $matched_pattern = $pattern;
                    $error_type = $type;
                    break;
                }
            }
            
            if ($matched_pattern) {
                // Estrai informazioni errore (file, riga, messaggio)
                $error_info = ErrorInfoExtractor::extractPhpErrorInfo($line, $lines, $i);
                
                // Crea chiave di raggruppamento: tipo + file + riga (se disponibile)
                $group_key = $error_type;
                if (!empty($error_info['file'])) {
                    $group_key .= '|' . basename($error_info['file']);
                    if (!empty($error_info['line'])) {
                        $group_key .= ':' . $error_info['line'];
                    }
                } else {
                    // Se non abbiamo file, usa il messaggio (troncato)
                    $message_key = substr($error_info['message'], 0, 100);
                    $group_key .= '|' . md5($message_key);
                }
                
                // Inizializza gruppo se non esiste
                if (!isset($error_groups[$group_key])) {
                    $error_groups[$group_key] = [
                        'type' => $error_type,
                        'pattern' => $matched_pattern,
                        'count' => 0,
                        'examples' => [],
                        'stack_traces' => [],
                        'files' => [],
                        'lines' => [],
                        'contexts' => [],
                        'first_seen' => $timestamp ?: time(),
                        'last_seen' => $timestamp ?: time(),
                    ];
                }
                
                $error_groups[$group_key]['count']++;
                $error_groups[$group_key]['last_seen'] = max($error_groups[$group_key]['last_seen'], $timestamp ?: time());
                
                // Aggiungi contesto
                $context_key = $execution_context['context'];
                if (!in_array($context_key, $error_groups[$group_key]['contexts'])) {
                    $error_groups[$group_key]['contexts'][] = $context_key;
                }
                
                // Aggiungi file e riga
                if (!empty($error_info['file'])) {
                    $file_basename = basename($error_info['file']);
                    if (!in_array($file_basename, $error_groups[$group_key]['files'])) {
                        $error_groups[$group_key]['files'][] = $file_basename;
                    }
                    if (!empty($error_info['line']) && !in_array($error_info['line'], $error_groups[$group_key]['lines'])) {
                        $error_groups[$group_key]['lines'][] = $error_info['line'];
                    }
                }
                
                // Raccogli esempio completo con stack trace
                if (count($error_groups[$group_key]['examples']) < 5) {
                    $example = [
                        'message' => $error_info['message'],
                        'file' => $error_info['file'],
                        'line' => $error_info['line'],
                        'stack_trace' => $error_info['stack_trace'],
                        'context' => $context_key,
                        'timestamp' => $timestamp,
                    ];
                    $error_groups[$group_key]['examples'][] = $example;
                }
                
                // Salta righe dello stack trace (già processate in extract_php_error_info)
                if (!empty($error_info['stack_trace_lines'])) {
                    $i += $error_info['stack_trace_lines'];
                }
            }
            
            $i++;
        }
        
        // Converti gruppi in issues
        foreach ($error_groups as $group_key => $data) {
            // Soglia: fatal/parse/error sempre, warning/database se >= 3
            $threshold = in_array($data['type'], ['fatal', 'parse', 'error', 'exception']) ? 1 : 3;
            
            if ($data['count'] >= $threshold) {
                $pattern_name = LogUtility::getPatternName($data['pattern']);
                
                // Determina severity
                $severity = in_array($data['type'], ['fatal', 'parse', 'error', 'exception']) ? 'error' : 'warning';
                
                // Costruisci messaggio
                $message_parts = [$pattern_name . ': ' . $data['count'] . ' occorrenze'];
                
                // Aggiungi file/riga se disponibili
                if (!empty($data['files'])) {
                    $file_info = implode(', ', array_slice($data['files'], 0, 3));
                    if (count($data['files']) > 3) {
                        $file_info .= ' (+' . (count($data['files']) - 3) . ' altri)';
                    }
                    $message_parts[] = 'File: ' . $file_info;
                }
                
                if (!empty($data['lines'])) {
                    $lines_info = implode(', ', array_slice($data['lines'], 0, 5));
                    if (count($data['lines']) > 5) {
                        $lines_info .= ' (+' . (count($data['lines']) - 5) . ' altri)';
                    }
                    $message_parts[] = 'Righe: ' . $lines_info;
                }
                
                // Aggiungi contesto
                if (!empty($data['contexts'])) {
                    $context_labels = [
                        'wp_cli' => 'WP-CLI',
                        'ajax' => 'AJAX',
                        'wp_cron' => 'WP-CRON',
                        'frontend' => 'Frontend',
                        'backend' => 'Backend',
                        'rest_api' => 'REST API',
                        'unknown' => 'Unknown',
                    ];
                    $contexts_labeled = array_map(function($ctx) use ($context_labels) {
                        return $context_labels[$ctx] ?? $ctx;
                    }, $data['contexts']);
                    $message_parts[] = 'Contesto: ' . implode(', ', $contexts_labeled);
                }
                
                $message = implode(' | ', $message_parts);
                
                $issues[] = [
                    'type' => 'PHP Error',
                    'severity' => $severity,
                    'message' => $message,
                    'count' => $data['count'],
                    'examples' => $data['examples'],
                    'files' => $data['files'],
                    'lines' => $data['lines'],
                    'contexts' => $data['contexts'],
                    'first_seen' => $data['first_seen'],
                    'last_seen' => $data['last_seen'],
                    'error_type' => $data['type'],
                ];
            }
        }
        
        // Ordina per severity e count (errori prima, poi per count decrescente)
        usort($issues, function($a, $b) {
            if ($a['severity'] !== $b['severity']) {
                return $a['severity'] === 'error' ? -1 : 1;
            }
            return $b['count'] - $a['count'];
        });
        
        return $issues;
    }
    
    // Metodo rimosso: spostato in ErrorInfoExtractor::extractPhpErrorInfo()
    
    /**
     * Analizza WordPress cron log
     */
    private static function analyze_wp_cron(string $log_path): array
    {
        $issues = [];
        $lines = LogFileReader::readTail($log_path, 500);
        
        if (empty($lines)) {
            return $issues;
        }
        
        $failed_crons = [];
        $cutoff_time = time() - (24 * 60 * 60);
        
        foreach ($lines as $line) {
            // Cerca errori comuni nei cron
            if (preg_match('/(error|failed|timeout|fatal)/i', $line)) {
                $timestamp = TimestampParser::parseWpCron($line);
                if ($timestamp && $timestamp < $cutoff_time) {
                    continue;
                }
                
                $failed_crons[] = LogUtility::truncateLine($line, 150);
            }
        }
        
        if (count($failed_crons) >= 3) {
            $issues[] = [
                'type' => 'WordPress Cron',
                'severity' => 'warning',
                'message' => sprintf('Errori WordPress cron: %d occorrenze', count($failed_crons)),
                'count' => count($failed_crons),
                'examples' => array_slice($failed_crons, 0, 5), // Mostra fino a 5 esempi per cron errors
            ];
        }
        
        return $issues;
    }
    
    // Metodi rimossi: spostati in classi dedicate
    // - read_log_tail() -> LogFileReader::readTail()
    // - parse_nginx_timestamp() -> TimestampParser::parseNginx()
    // - parse_nginx_access_timestamp() -> TimestampParser::parseNginxAccess()
    // - parse_apache_timestamp() -> TimestampParser::parseApache()
    // - parse_php_error_timestamp() -> TimestampParser::parsePhpError()
    // - parse_php_slow_timestamp() -> TimestampParser::parsePhpSlow()
    // - parse_wp_cron_timestamp() -> TimestampParser::parseWpCron()
    // - get_server_timezone() -> TimezoneHelper::getServerTimezone()
    // - check_log_timestamp_warning() -> TimezoneHelper::checkTimestampWarning()
    // - truncate_line() -> LogUtility::truncateLine()
    // - get_pattern_name() -> LogUtility::getPatternName()
    
    /**
     * Tail degli ultimi errori grezzi per ogni log
     * 
     * Restituisce gli ultimi N errori significativi per ogni tipo di log,
     * filtrati per severità e timestamp (ultime X ore).
     * 
     * @param int $per_file Numero di righe per file (default: 30)
     * @param int $hours Numero di ore da analizzare (default: 24)
     * @return array{paths: array, tails: array} Paths dei log e tails filtrati
     */
    public static function recent_errors_tail(int $per_file = 30, int $hours = 24): array
    {
        return TailReader::recent_errors_tail($per_file, $hours);
    }
    
    /**
     * Estrae errori PHP in formato strutturato per API/UI avanzata
     * 
     * @param array $filters Filtri: severity, file, since, until, context, hours, limit, offset
     * @return array{total: int, errors: array, limit: int, offset: int} Errori strutturati
     */
    public static function get_php_errors_structured(array $filters = []): array
    {
        // Salva stato originale
        $old_error_reporting = error_reporting(0);
        $old_display_errors = ini_get('display_errors');
        $old_log_errors = ini_get('log_errors');
        ini_set('display_errors', '0');
        ini_set('log_errors', '0');
        
        $debug_info = [
            'filters' => $filters,
            'paths' => [],
            'hours' => $filters['hours'] ?? 0,
            'issues_before_filters' => 0,
            'issues_after_filters' => 0,
            'reason' => null,
        ];
        
        try {
            $paths = self::get_log_paths();
            $base = $paths['base'] ?? null;
            
            if (empty($base)) {
                return [
                    'total' => 0,
                    'errors' => [],
                    'limit' => $filters['limit'] ?? 1000,
                    'offset' => $filters['offset'] ?? 0,
                    'debug' => array_merge($debug_info, ['reason' => 'logs_directory_not_found']),
                ];
            }
            
            // FIX: Non fare early return se manca solo php_error.
            // Su Cloudways, gli errori PHP finiscono soprattutto in apache_error/nginx_error.
            // Cerca in tutti i tipi di log di errore.
            $php_error_files = LogSourceResolver::get_logs_by_type($base, 'php_error', true);
            $apache_error_files = LogSourceResolver::get_logs_by_type($base, 'apache_error', true);
            $nginx_error_files = LogSourceResolver::get_logs_by_type($base, 'nginx_error', true);
            
            // Combina tutti i file di errore (PHP error può essere in apache/nginx su Cloudways)
            $all_error_files = array_merge($php_error_files, $apache_error_files, $nginx_error_files);
            
            $debug_info['paths'] = [
                'base' => $base,
                'php_error_candidates' => $php_error_files,
                'apache_error_candidates' => $apache_error_files,
                'nginx_error_candidates' => $nginx_error_files,
                'all_error_candidates' => $all_error_files,
            ];
            
            if (empty($all_error_files)) {
                return [
                    'total' => 0,
                    'errors' => [],
                    'limit' => $filters['limit'] ?? 1000,
                    'offset' => $filters['offset'] ?? 0,
                    'debug' => array_merge($debug_info, ['reason' => 'no_error_files_found']),
                ];
            }
            
            // Usa il primo file disponibile (più recente)
            // analyze_php_errors() può analizzare anche log Apache/Nginx (contengono errori PHP)
            $selected_error_file = $all_error_files[0];
            $debug_info['paths']['selected_error_file'] = $selected_error_file;
            
            // Analizza errori PHP (anche da apache/nginx logs)
            $hours = isset($filters['hours']) ? (int)$filters['hours'] : 0;
            if ($hours < 0) {
                $hours = 0;
            } elseif ($hours > 720) {
                $hours = 720;
            }
            $debug_info['hours'] = $hours;
            
            // Determina tipo di file per usare il metodo di analisi corretto
            $filename = basename($selected_error_file);
            $is_apache = stripos($filename, 'apache') !== false;
            $is_nginx = stripos($filename, 'nginx') !== false;
            
            // analyze_php_errors() gestisce correttamente anche log Apache/Nginx
            // Quando hours=0, analizza tutto il file (read_log_tail() legge tutto quando lines > 10000)
            // Modifichiamo analyze_php_errors() per passare un numero alto di righe quando hours=0
            if ($hours == 0) {
                // Analisi completa: passa un numero molto alto per leggere tutto il file
                // analyze_php_errors() usa read_log_tail() che legge tutto quando lines > 10000
                $php_issues = self::analyze_php_errors($selected_error_file, 0);
            } else {
                // Analisi con limite temporale
                $php_issues = self::analyze_php_errors($selected_error_file, $hours);
            }
            $debug_info['issues_before_filters'] = count($php_issues);
            
            // Applica filtri
            $filtered_errors = [];
            $severity_filter = !empty($filters['severity']) ? explode(',', $filters['severity']) : [];
            $file_filter = $filters['file'] ?? null;
            $since = !empty($filters['since']) ? (int)$filters['since'] : null;
            $until = !empty($filters['until']) ? (int)$filters['until'] : null;
            $known_contexts = ['wp_cli', 'ajax', 'wp_cron', 'frontend', 'backend', 'rest_api'];
            $context_filter = !empty($filters['context']) ? explode(',', $filters['context']) : [];
            $context_filter = array_values(array_unique(array_filter(array_map('trim', $context_filter), function($value) {
                return $value !== '';
            })));
            
            $apply_context_filter = !empty($context_filter);
            if ($apply_context_filter) {
                $unknown_requested = in_array('unknown', $context_filter, true);
                $selected_known_contexts = array_values(array_intersect($known_contexts, $context_filter));
                
                // Se vengono selezionati tutti i contesti noti (senza unknown) consideriamo il filtro come "mostra tutti"
                if (!$unknown_requested && count($selected_known_contexts) === count($known_contexts)) {
                    $apply_context_filter = false;
                    $context_filter = [];
                } else {
                    $context_filter = array_merge(
                        $selected_known_contexts,
                        $unknown_requested ? ['unknown'] : []
                    );
                }
            }
            
            foreach ($php_issues as $issue) {
                // Filtro severity
                if (!empty($severity_filter)) {
                    $issue_severity = $issue['severity'] ?? 'warning';
                    if (!in_array($issue_severity, $severity_filter)) {
                        continue;
                    }
                }
                
                // Filtro file
                if ($file_filter && !empty($issue['files'])) {
                    $matched = false;
                    foreach ($issue['files'] as $file) {
                        if (stripos($file, $file_filter) !== false) {
                            $matched = true;
                            break;
                        }
                    }
                    if (!$matched) {
                        continue;
                    }
                }
                
                // Filtro data
                if ($since && (!empty($issue['first_seen']) && $issue['first_seen'] < $since)) {
                    continue;
                }
                if ($until && (!empty($issue['last_seen']) && $issue['last_seen'] > $until)) {
                    continue;
                }
                
                // Filtro contesto
                $issue_contexts = !empty($issue['contexts']) ? $issue['contexts'] : ['unknown'];
                
                if ($apply_context_filter) {
                    $matched = false;
                    foreach ($issue_contexts as $ctx) {
                        if (in_array($ctx, $context_filter, true)) {
                            $matched = true;
                            break;
                        }
                    }
                    if (!$matched) {
                        continue;
                    }
                }
                
                // Converti in formato strutturato per API
                $error_id = 'err_' . md5($issue['message'] . ($issue['files'][0] ?? '') . ($issue['lines'][0] ?? ''));
                
                $error_data = [
                    'id' => $error_id,
                    'timestamp' => $issue['last_seen'] ?? time(),
                    'severity' => $issue['severity'] ?? 'warning',
                    'error_type' => $issue['error_type'] ?? 'unknown',
                    'message' => $issue['message'] ?? '',
                    'file' => !empty($issue['files']) ? $issue['files'][0] : null,
                    'files' => $issue['files'] ?? [],
                    'line' => !empty($issue['lines']) ? (int)$issue['lines'][0] : null,
                    'lines' => array_map('intval', $issue['lines'] ?? []),
                    'count' => $issue['count'] ?? 0,
                    'contexts' => $issue['contexts'] ?? [],
                    'first_seen' => $issue['first_seen'] ?? time(),
                    'last_seen' => $issue['last_seen'] ?? time(),
                ];
                
                // Aggiungi stack trace e dettagli dal primo esempio
                if (!empty($issue['examples']) && is_array($issue['examples'][0])) {
                    $example = $issue['examples'][0];
                    $error_data['stack_trace'] = $example['stack_trace'] ?? [];
                    $error_data['context'] = $example['context'] ?? 'unknown';
                    if (!empty($example['file'])) {
                        $error_data['file'] = $example['file'];
                    }
                    if (!empty($example['line'])) {
                        $error_data['line'] = (int)$example['line'];
                    }
                }
                
                $filtered_errors[] = $error_data;
            }
            
            // Ordina per timestamp (più recenti prima)
            usort($filtered_errors, function($a, $b) {
                return ($b['timestamp'] ?? 0) - ($a['timestamp'] ?? 0);
            });
            
            // Applica limit e offset
            $total = count($filtered_errors);
            $limit = (int)($filters['limit'] ?? 1000);
            $offset = (int)($filters['offset'] ?? 0);
            $filtered_errors = array_slice($filtered_errors, $offset, $limit);
            
            $debug_info['issues_after_filters'] = $total;
            
            return [
                'total' => $total,
                'errors' => $filtered_errors,
                'limit' => $limit,
                'offset' => $offset,
                'debug' => $debug_info,
            ];
            
        } catch (\Throwable $e) {
            return [
                'total' => 0,
                'errors' => [],
                'limit' => $filters['limit'] ?? 1000,
                'offset' => $filters['offset'] ?? 0,
                'error' => $e->getMessage(),
                'debug' => array_merge($debug_info, [
                    'reason' => 'exception',
                    'exception' => $e->getMessage(),
                ]),
            ];
        } finally {
            error_reporting($old_error_reporting ?? E_ALL);
            ini_set('display_errors', $old_display_errors ?? '1');
            ini_set('log_errors', $old_log_errors ?? '1');
        }
    }
}

