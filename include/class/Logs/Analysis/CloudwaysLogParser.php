<?php
namespace gik25microdata\Logs\Analysis;

use gik25microdata\Logs\Resolver\LogSourceResolver;
use gik25microdata\Logs\Analysis\LogAnalyzer;
use gik25microdata\Logs\Analysis\TailReader;

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
    // - get_log_paths() -> LogSourceResolver::find_logs_directory() + get_logs_by_type()
    // - collect_log_files() -> LogSourceResolver::get_logs_by_type()
    // - resolve_log_files() -> LogSourceResolver::get_logs_by_type()
    // - read_log_tail() -> LogFileReader::readTail()
    
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
                $analyzer = new LogAnalyzer();
                
                // Analizza PHP errors
                $php_issues = $analyzer->analyze($log_file_path, 'php_error', $max_lines, 0); // cutoff_hours=0 per analizzare tutto
                $issues = array_merge($issues, $php_issues);
                
                // Aggiungi anche analisi Apache se sembra un log Apache
                if ($is_apache) {
                    $apache_issues = $analyzer->analyze($log_file_path, 'apache_error', $max_lines, 0);
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
    
    // Metodo rimosso: usa LogAnalyzer::analyze($log_path, 'php_error', $max_lines, 0)
    
    // Metodo analyze_apache_errors_extended rimosso: usa LogAnalyzer::analyze($log_path, 'apache_error', ...)
    
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
            $base = LogSourceResolver::find_logs_directory();
            if (!$base) {
                return ['status'=>'warning','message'=>'Directory logs non trovata','issues'=>[],'total_errors'=>0,'total_warnings'=>0,'details'=>'','log_paths'=>[]];
            }
            
            $types = ['nginx_error','nginx_access','apache_error','apache_access','php_slow','php_error','wp_cron'];
            $limits = ['nginx_access'=>5000,'apache_access'=>5000,'php_error'=>5000];
            $issues = []; 
            $err = 0; 
            $warn = 0; 
            $an = new LogAnalyzer();
            
            foreach ($types as $t) {
                $files = LogSourceResolver::get_logs_by_type($base, $t, false);
                if (!$files) continue;
                
                $max = $limits[$t] ?? 1000;
                $res = $an->analyze($files[0], $t, $max, 24);
                $issues = array_merge($issues, $res);
                
                foreach ($res as $i) { 
                    ($i['severity']==='error') ? $err++ : $warn++; 
                }
            }
            
            return [
                'status' => $err ? 'error' : ($warn ? 'warning' : 'success'),
                'message'=> $err ? "Trovati $err errori e $warn warning" : ($warn ? "Trovati $warn warning" : "Nessun problema (24h)"),
                'issues' => $issues,
                'total_errors' => $err,
                'total_warnings' => $warn,
                'details' => '',
                'log_paths' => ['base'=>$base],
            ];
        } finally {
            error_reporting($old_error_reporting ?? E_ALL);
            ini_set('display_errors', $old_display_errors ?? '1');
            ini_set('log_errors', $old_log_errors ?? '1');
        }
    }
    
    // Metodo analyze_nginx_errors rimosso: usa LogAnalyzer::analyze($log_path, 'nginx_error', ...)
    // Metodo analyze_nginx_access rimosso: usa LogAnalyzer::analyze($log_path, 'nginx_access', ...)
    
    // Metodo analyze_apache_errors_legacy rimosso: usa LogAnalyzer::analyze($log_path, 'apache_error', ...)
    // Metodo analyze_apache_access rimosso: usa LogAnalyzer::analyze($log_path, 'apache_access', ...)
    
    // Metodo analyze_php_slow rimosso: usa LogAnalyzer::analyze($log_path, 'php_slow', ...)
    // Logica spostata in PhpSlowMultiLineParser e LogAggregator::groupByPhpSlow()
    
    // Metodo analyze_php_errors rimosso: usa LogAnalyzer::analyze($log_path, 'php_error', ...)
    // Logica spostata in PhpErrorMultiLineParser e LogAggregator::groupByPhpError()
    
    // Metodo rimosso: spostato in ErrorInfoExtractor::extractPhpErrorInfo()
    
    // Metodo analyze_wp_cron rimosso: usa LogAnalyzer::analyze($log_path, 'wp_cron', ...)
    
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
            $base = LogSourceResolver::find_logs_directory();
            
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
            // LogAnalyzer può analizzare anche log Apache/Nginx (contengono errori PHP)
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
            
            // Usa LogAnalyzer per analizzare errori PHP
            $analyzer = new LogAnalyzer();
            $max_lines = $hours === 0 ? 50000 : 5000; // Se hours=0, analizza tutto (molte righe)
            $php_issues = $analyzer->analyze($selected_error_file, 'php_error', $max_lines, $hours);
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

