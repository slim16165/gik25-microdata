<?php
namespace gik25microdata\HealthCheck;

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
    /**
     * Pattern di errori che possono essere ignorati o declassati
     * Questi sono errori noti che non sono critici per il funzionamento del sito
     */
    private static function get_ignorable_error_patterns(): array
    {
        return [
            // Action Scheduler - tabelle mancanti (spesso non critiche)
            '/Table.*actionscheduler.*doesn\'t exist/i',
            '/Table.*actionscheduler.*does not exist/i',
            
            // Altri errori di tabelle opzionali di plugin
            '/Table.*doesn\'t exist.*action_scheduler/i',
            '/Table.*does not exist.*action_scheduler/i',
            
            // Errori di plugin che cercano tabelle opzionali
            '/WordPress database error.*Table.*doesn\'t exist/i',
            
            // Errori specifici di Action Scheduler che sono non critici
            '/ActionScheduler.*Table.*doesn\'t exist/i',
            '/ActionScheduler.*Table.*does not exist/i',
        ];
    }
    
    /**
     * Verifica se un errore può essere ignorato o declassato
     * 
     * @param string $error_line La riga di errore da verificare
     * @param array $context Contesto di esecuzione (opzionale)
     * @return array{ignore: bool, downgrade: bool} ignore=true per ignorare completamente, downgrade=true per declassare a warning
     */
    private static function should_ignore_error(string $error_line, array $context = []): array
    {
        $patterns = self::get_ignorable_error_patterns();
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $error_line)) {
                // Errori di Action Scheduler per tabelle mancanti vengono IGNORATI completamente
                if (preg_match('/actionscheduler/i', $error_line)) {
                    return ['ignore' => true, 'downgrade' => false];
                }
                // Altri errori di tabelle opzionali mancanti vengono ignorati
                if (preg_match('/Table.*doesn\'t exist.*action_scheduler/i', $error_line)) {
                    return ['ignore' => true, 'downgrade' => false];
                }
            }
        }
        
        return ['ignore' => false, 'downgrade' => false];
    }
    
    /**
     * Estrae il contesto di esecuzione da una riga di log
     * 
     * @param string $line Riga di log
     * @return array{context: string, script: string, details: array} Contesto identificato
     */
    private static function extract_execution_context(string $line): array
    {
        $context = 'unknown';
        $script = '';
        $details = [];
        
        // Cerca pattern comuni per identificare il contesto
        // WP-CLI
        if (preg_match('/phar:\/\/.*\/wp\/php\/boot-phar\.php/i', $line) || 
            preg_match('/wp-cli\.php/i', $line) ||
            preg_match('/WP_CLI/i', $line)) {
            $context = 'wp_cli';
            $details['type'] = 'WP-CLI Command';
        }
        // AJAX
        elseif (preg_match('/admin-ajax\.php/i', $line)) {
            $context = 'ajax';
            if (preg_match('/admin-ajax\.php[^\s]*\s+(\w+)/', $line, $matches)) {
                $details['action'] = $matches[1] ?? 'unknown';
            }
            $details['type'] = 'AJAX Request';
        }
        // WP-CRON
        elseif (preg_match('/wp-cron\.php/i', $line) || 
                preg_match('/ActionScheduler/i', $line) ||
                preg_match('/action_scheduler/i', $line) ||
                preg_match('/do_action.*wp_scheduled/i', $line)) {
            $context = 'wp_cron';
            $details['type'] = 'WP-CRON / Action Scheduler';
        }
        // Frontend
        elseif (preg_match('/index\.php/i', $line) && !preg_match('/wp-admin|wp-includes/i', $line)) {
            $context = 'frontend';
            $details['type'] = 'Frontend Request';
        }
        // Backend/Admin
        elseif (preg_match('/wp-admin/i', $line)) {
            $context = 'backend';
            $details['type'] = 'Backend/Admin';
        }
        // REST API
        elseif (preg_match('/wp-json/i', $line) || preg_match('/rest_route/i', $line)) {
            $context = 'rest_api';
            $details['type'] = 'REST API';
        }
        
        // Estrai script filename se presente
        if (preg_match('/script_filename\s*=\s*(.+)/i', $line, $matches)) {
            $script = trim($matches[1]);
            $details['script'] = basename($script);
        } elseif (preg_match('/(\/[^\s]+\.php)/', $line, $matches)) {
            $script = $matches[1];
            $details['script'] = basename($script);
        }
        
        return [
            'context' => $context,
            'script' => $script,
            'details' => $details,
        ];
    }
    
    /**
     * Verifica se un errore dovrebbe essere ignorato in base al contesto di esecuzione
     * 
     * @param array $execution_context Contesto di esecuzione
     * @return bool true se dovrebbe essere ignorato
     */
    private static function should_ignore_by_context(array $execution_context): bool
    {
        // Ignora errori da WP-CRON/Action Scheduler se configurato
        // (puoi estendere questa logica per avere più controllo)
        if ($execution_context['context'] === 'wp_cron') {
            // Per ora non ignoriamo, ma potresti voler ignorare errori da cron se sono troppo frequenti
            return false;
        }
        
        return false;
    }
    /**
     * Percorsi tipici dei log su Cloudways
     */
    private static function get_log_paths(): array
    {
        $paths = [];
        
        // Su Cloudways, i log sono tipicamente nella directory logs/ al livello superiore a public_html
        // Es: /home/1340912.cloudwaysapps.com/gwvyrysadj/logs/
        $possible_base_paths = [
            ABSPATH . '../logs/',  // Tipico percorso Cloudways
            ABSPATH . '../../logs/',
            '/home/*/logs/',  // Pattern comune Cloudways
        ];
        
        // Aggiungi anche percorso relativo dalla directory del plugin (per sviluppo locale)
        $plugin_dir = dirname(dirname(dirname(__DIR__)));
        if (is_dir($plugin_dir . '/logs/')) {
            $possible_base_paths[] = $plugin_dir . '/logs/';
        }
        
        // Rimuovi duplicati
        $possible_base_paths = array_unique($possible_base_paths);
        
        // Prova a trovare la directory logs
        foreach ($possible_base_paths as $base_path) {
            // Espandi wildcards se presente
            if (strpos($base_path, '*') !== false) {
                // Prova pattern comuni Cloudways
                $glob_results = glob($base_path);
                if (!empty($glob_results)) {
                    foreach ($glob_results as $result) {
                        if (is_dir($result) && is_readable($result)) {
                            $paths['base'] = rtrim($result, '/\\') . '/';
                            break 2;
                        }
                    }
                }
                continue;
            }
            
            $normalized = rtrim(str_replace('\\', '/', $base_path), '/') . '/';
            if (is_dir($normalized) && is_readable($normalized)) {
                $paths['base'] = $normalized;
                break;
            }
        }
        
        // Se non trovata, prova percorsi alternativi
        if (empty($paths['base'])) {
            // Prova a cercare nella struttura tipica Cloudways
            $abs_path = rtrim(str_replace('\\', '/', ABSPATH), '/');
            $parts = explode('/', trim($abs_path, '/'));
            
            // Rimuovi 'public_html' se presente e aggiungi 'logs'
            if (($key = array_search('public_html', $parts)) !== false) {
                $parts[$key] = 'logs';
                $logs_path = '/' . implode('/', $parts) . '/';
                if (is_dir($logs_path) && is_readable($logs_path)) {
                    $paths['base'] = $logs_path;
                }
            }
        }
        
        // Se ancora non trovato, prova percorsi assoluti comuni con glob
        if (empty($paths['base'])) {
            $glob_patterns = [
                '/home/*/logs/',
                '/home/*/*/logs/',
                '/home/*/*/*/logs/',
            ];
            
            foreach ($glob_patterns as $pattern) {
                $glob_results = glob($pattern);
                if (!empty($glob_results)) {
                    foreach ($glob_results as $result) {
                        if (is_dir($result) && is_readable($result)) {
                            $paths['base'] = rtrim($result, '/\\') . '/';
                            break 2;
                        }
                    }
                }
            }
        }
        
        if (empty($paths['base'])) {
            return [];
        }
        
        $base = $paths['base'];
        
        // Pattern per i file di log Cloudways
        $log_patterns = [
            'nginx_error' => [
                'nginx*.error.log',
                'nginx-app.error.log',
            ],
            'nginx_access' => [
                'nginx*.access.log',
                'nginx-app.access.log',
            ],
            'apache_error' => [
                'apache*.error.log',
            ],
            'apache_access' => [
                'apache*.access.log',
            ],
            'php_slow' => [
                'php-app.slow.log',
            ],
            'php_error' => [
                'php-app.error.log',
            ],
            'php_access' => [
                'php-app.access.log',
            ],
            'wp_cron' => [
                'wp-cron.log',
            ],
        ];
        
        // Trova i file di log esistenti
        foreach ($log_patterns as $type => $patterns) {
            foreach ($patterns as $pattern) {
                $full_pattern = $base . $pattern;
                $matches = glob($full_pattern);
                if (!empty($matches)) {
                    // Prendi il file più recente (non rotato)
                    $non_rotated = array_filter($matches, function($file) {
                        $basename = basename($file);
                        return strpos($basename, '.1') === false && 
                               strpos($basename, '.2') === false &&
                               strpos($basename, '.gz') === false;
                    });
                    
                    if (!empty($non_rotated)) {
                        // Ordina per tempo di modifica (più recente prima)
                        usort($non_rotated, function($a, $b) {
                            $time_a = @filemtime($a) ?: 0;
                            $time_b = @filemtime($b) ?: 0;
                            return $time_b - $time_a;
                        });
                        $paths[$type] = reset($non_rotated);
                    } elseif (!empty($matches)) {
                        // Se solo file rotati, prendi il più recente
                        usort($matches, function($a, $b) {
                            $time_a = @filemtime($a) ?: 0;
                            $time_b = @filemtime($b) ?: 0;
                            return $time_b - $time_a;
                        });
                        $paths[$type] = reset($matches);
                    }
                    break;
                }
            }
        }
        
        // Aggiungi pattern glob per file ruotati (.gz)
        if (!empty($paths['base'])) {
            $base = $paths['base'];
            
            // Pattern glob per file ruotati (supporta sia *.log.*.gz che *.log.1.gz, *.log.2.gz, ecc.)
            $paths['php_error_glob'] = $base . '{php-app.error.log.*.gz,php-app.error.log.*}';
            $paths['nginx_err_glob'] = $base . '{nginx*.error.log.*.gz,nginx*.error.log.*}';
            $paths['nginx_acc_glob'] = $base . '{nginx*.access.log.*.gz,nginx*.access.log.*}';
            $paths['apache_err_glob'] = $base . '{apache*.error.log.*.gz,apache*.error.log.*}';
            $paths['apache_acc_glob'] = $base . '{apache*.access.log.*.gz,apache*.access.log.*}';
            $paths['php_acc_glob'] = $base . '{php-app.access.log.*.gz,php-app.access.log.*}';
            $paths['php_slow_glob'] = $base . '{php-app.slow.log.*.gz,php-app.slow.log.*}';
            $paths['wp_cron_glob'] = $base . '{wp-cron.log.*.gz,wp-cron.log.*}';
        }
        
        return $paths;
    }
    
    /**
     * Raccoglie file reali (esclude .gz di default) dai pattern e li ordina per mtime (desc)
     * 
     * @param array $globs Array di pattern glob (es. ['/path/*.log', '/path/*.log.*.gz'])
     * @param bool $include_gz Se true, include anche file .gz (default: false)
     * @return array Array di percorsi file ordinati per mtime (più recenti prima)
     */
    private static function collect_log_files(array $globs, bool $include_gz = false): array
    {
        $files = [];
        
        foreach ($globs as $g) {
            if (empty($g)) {
                continue;
            }
            
            // Supporta sia pattern glob che file singoli
            if (strpos($g, '*') !== false || strpos($g, '?') !== false || strpos($g, '{') !== false) {
                // Pattern glob (con o senza brace expansion)
                $glob_results = glob($g, GLOB_BRACE);
                if ($glob_results === false) {
                    // Se GLOB_BRACE fallisce, prova senza
                    $glob_results = glob($g);
                }
                
                foreach ($glob_results ?: [] as $f) {
                    if (is_readable($f) && is_file($f)) {
                        // Ignora directory
                        // Di default esclude file .gz (troppo pesanti)
                        if (!$include_gz && substr($f, -3) === '.gz') {
                            continue;
                        }
                        $files[$f] = @filemtime($f) ?: 0;
                    }
                }
            } else {
                // File singolo
                if (is_readable($g) && is_file($g)) {
                    // Di default esclude file .gz
                    if (!$include_gz && substr($g, -3) === '.gz') {
                        continue;
                    }
                    $files[$g] = @filemtime($g) ?: 0;
                }
            }
        }
        
        // Ordina per mtime (più recenti prima)
        arsort($files);
        
        return array_keys($files);
    }
    
    /**
     * Legge la coda (ultime ~K righe) da più file (plain + gz)
     * 
     * @param array $files Array di percorsi file (già ordinati per mtime)
     * @param int $max_lines Numero massimo di righe da restituire
     * @param callable $accept Callback per filtrare righe (return true per accettare)
     * @return array Array di righe (ultime N che matchano il filtro)
     */
    private static function tail_from_files(array $files, int $max_lines, callable $accept): array
    {
        $ring = [];
        
        foreach ($files as $file) {
            // Skip file .gz (non dovrebbero esserci se collect_log_files esclude .gz, ma controllo di sicurezza)
            if (substr($file, -3) === '.gz') {
                continue;
            }
            
            try {
                $fh = @fopen($file, 'rb');
                
                if (!$fh) {
                    continue;
                }
                
                $file_size = @filesize($file);
                
                // Per file grandi, leggi solo la coda (ultimi 2MB) - così leggiamo sempre gli errori più recenti
                // Anche se il file è gigante, leggiamo solo gli ultimi 2MB (dove ci sono gli errori più recenti)
                if ($file_size && $file_size > 2 * 1024 * 1024) {
                    @fseek($fh, -min(2 * 1024 * 1024, $file_size), SEEK_END);
                }
                
                while (($line = @fgets($fh)) !== false) {
                    $line = rtrim($line, "\r\n");
                    
                    if ($accept($line)) {
                        $ring[] = mb_substr($line, 0, 300);
                        
                        // Mantieni solo le ultime N*12 righe in memoria (per avere abbastanza materiale)
                        if (count($ring) > $max_lines * 12) {
                            array_splice($ring, 0, count($ring) - $max_lines * 12);
                        }
                    }
                }
                
                @fclose($fh);
                
                // Se abbiamo già abbastanza righe, fermati
                if (count($ring) >= $max_lines) {
                    break;
                }
                
            } catch (\Throwable $e) {
                // Silenzioso: continua con il prossimo file
                if (isset($fh) && $fh) {
                    @fclose($fh);
                }
                continue;
            }
        }
        
        // Ritorna solo le ultime N righe utili
        return array_slice($ring, -$max_lines);
    }
    
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
        $issues = [];
        $lines = self::read_log_tail($log_path, $max_lines);
        
        if (empty($lines)) {
            return $issues;
        }
        
        $error_counts = [];
        // Analizza tutto il file, non solo ultime 24 ore
        $cutoff_time = 0;
        
        $critical_patterns = [
            '/PHP Fatal error/i',
            '/PHP Parse error/i',
            '/PHP Warning/i',
            '/WordPress database error/i',
            '/Premature end of script headers/i',
            '/Maximum execution time/i',
            '/foreach\(\) argument must be of type array\|object/i',
            '/call_user_func_array\(\)/i',
            '/Uncaught Error/i',
            '/Uncaught Exception/i',
        ];
        
        foreach ($lines as $line) {
            $timestamp = self::parse_apache_timestamp($line);
            if ($timestamp && $timestamp < $cutoff_time) {
                continue;
            }
            
            // Estrai contesto di esecuzione
            $execution_context = self::extract_execution_context($line);
            
            // Verifica se l'errore dovrebbe essere ignorato
            $ignore_check = self::should_ignore_error($line, $execution_context);
            if ($ignore_check['ignore']) {
                continue;
            }
            
            foreach ($critical_patterns as $pattern) {
                if (preg_match($pattern, $line)) {
                    $error_key = $pattern;
                    $context_key = $execution_context['context'];
                    $error_key = $pattern . '_' . $context_key;
                    
                    if (!isset($error_counts[$error_key])) {
                        $error_counts[$error_key] = [
                            'count' => 0, 
                            'examples' => [], 
                            'context' => $execution_context,
                            'contexts' => []
                        ];
                    }
                    $error_counts[$error_key]['count']++;
                    
                    if (!in_array($context_key, $error_counts[$error_key]['contexts'])) {
                        $error_counts[$error_key]['contexts'][] = $context_key;
                    }
                    
                    // Raccogli più esempi (fino a 10) per avere varietà
                    if (count($error_counts[$error_key]['examples']) < 10) {
                        $truncated = self::truncate_line($line, 200);
                        // Evita duplicati esatti
                        if (!in_array($truncated, $error_counts[$error_key]['examples'])) {
                            $error_counts[$error_key]['examples'][] = $truncated;
                        }
                    }
                }
            }
        }
        
        foreach ($error_counts as $pattern => $data) {
            // Soglia più bassa per file specifico (1 occorrenza)
            $threshold = 1;
            
            if ($data['count'] >= $threshold) {
                $clean_pattern = preg_replace('/_\w+$/', '', $pattern);
                $pattern_name = self::get_pattern_name($clean_pattern);
                
                $base_severity = $clean_pattern === '/PHP Fatal error/i' || 
                                $clean_pattern === '/PHP Parse error/i' ||
                                $clean_pattern === '/Uncaught Error/i' ||
                                $clean_pattern === '/Uncaught Exception/i' ? 'error' : 'warning';
                
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
                    'type' => 'PHP/WordPress Error',
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
     * Versione estesa di analyze_apache_errors che accetta max_lines
     */
    private static function analyze_apache_errors_extended(string $log_path, int $max_lines = 5000): array
    {
        $issues = [];
        $lines = self::read_log_tail($log_path, $max_lines);
        
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
            $timestamp = self::parse_apache_timestamp($line);
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
                        $truncated = self::truncate_line($line, 200);
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
                $pattern_name = self::get_pattern_name($clean_pattern);
                
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
        if (!empty($paths['php_error'])) {
            $php_error_issues = self::analyze_php_errors($paths['php_error']);
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
        $lines = self::read_log_tail($log_path, 1000); // Ultime 1000 righe
        
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
            $timestamp = self::parse_nginx_timestamp($line);
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
                        $critical_errors[$error_key][] = self::truncate_line($line, 150);
                    }
                }
            }
            
            // Raccogli altri errori recenti
            if (preg_match('/\[error\]/', $line)) {
                $recent_errors[] = self::truncate_line($line, 150);
                if (count($recent_errors) > 10) {
                    array_shift($recent_errors);
                }
            }
        }
        
        // Crea issue per errori critici frequenti
        foreach ($error_counts as $pattern => $count) {
            if ($count >= 5) { // Soglia: almeno 5 occorrenze
                $pattern_name = self::get_pattern_name($pattern);
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
        $lines = self::read_log_tail($log_path, 5000); // Ultime 5000 righe (più righe perché access log è più verboso)
        
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
                    $timestamp = self::parse_nginx_access_timestamp($line);
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
                        $truncated = self::truncate_line($line, 150);
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
            $timestamp = self::parse_apache_timestamp($line);
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
                        $truncated = self::truncate_line($line, 150);
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
                $pattern_name = self::get_pattern_name($clean_pattern);
                
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
        $lines = self::read_log_tail($log_path, 1000); // Leggi più righe per catturare entry complete
        
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
                $timestamp = self::parse_php_slow_timestamp($timestamp_str);
                
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
    private static function analyze_php_errors(string $log_path): array
    {
        $issues = [];
        $lines = self::read_log_tail($log_path, 5000); // Leggi più righe per stack trace completi
        
        if (empty($lines)) {
            return $issues;
        }
        
        $error_groups = [];
        $cutoff_time = time() - (24 * 60 * 60);
        
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
            $timestamp = self::parse_apache_timestamp($line);
            
            // Salta righe vecchie
            if ($timestamp && $timestamp < $cutoff_time) {
                $i++;
                continue;
            }
            
            // Estrai contesto di esecuzione
            $execution_context = self::extract_execution_context($line);
            
            // Verifica se l'errore dovrebbe essere ignorato
            $ignore_check = self::should_ignore_error($line, $execution_context);
            if ($ignore_check['ignore']) {
                $i++;
                continue;
            }
            
            // Verifica se ignorare per contesto
            if (self::should_ignore_by_context($execution_context)) {
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
                $error_info = self::extract_php_error_info($line, $lines, $i);
                
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
                $pattern_name = self::get_pattern_name($data['pattern']);
                
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
    
    /**
     * Estrae informazioni dettagliate da un errore PHP (file, riga, messaggio, stack trace)
     */
    private static function extract_php_error_info(string $error_line, array $all_lines, int $current_index): array
    {
        $info = [
            'message' => trim($error_line),
            'file' => null,
            'line' => null,
            'stack_trace' => [],
            'stack_trace_lines' => 0,
        ];
        
        // Estrai file e riga dal messaggio di errore
        // Pattern: "in /path/to/file.php on line 123"
        if (preg_match('/in\s+([^\s]+\.php)\s+on\s+line\s+(\d+)/i', $error_line, $matches)) {
            $info['file'] = $matches[1];
            $info['line'] = (int)$matches[2];
        }
        // Pattern: "/path/to/file.php(123): ..."
        elseif (preg_match('/([^\s\(]+\.php)\((\d+)\)/', $error_line, $matches)) {
            $info['file'] = $matches[1];
            $info['line'] = (int)$matches[2];
        }
        
        // Estrai stack trace dalle righe successive
        $stack_trace = [];
        $i = $current_index + 1;
        $max_stack_lines = 20; // Limita stack trace a 20 righe
        $stack_depth = 0;
        
        while ($i < count($all_lines) && $stack_depth < $max_stack_lines) {
            $line = $all_lines[$i];
            
            // Stack trace tipicamente inizia con "#0" o "Stack trace:" o contiene "called in"
            if (preg_match('/^(#\d+|Stack trace:)/', $line) || 
                preg_match('/called in/i', $line) ||
                preg_match('/\s+in\s+[^\s]+\.php\s+on\s+line\s+\d+/i', $line) ||
                preg_match('/\[internal function\]:/i', $line)) {
                
                $stack_trace[] = trim($line);
                $stack_depth++;
                
                // Estrai file e riga anche dallo stack trace
                if (preg_match('/([^\s\(]+\.php)\((\d+)\)/', $line, $matches)) {
                    if (empty($info['file'])) {
                        $info['file'] = $matches[1];
                        $info['line'] = (int)$matches[2];
                    }
                }
            } else {
                // Se la riga non sembra parte dello stack trace, fermati
                // (ma controlla se è una riga vuota o un nuovo errore)
                $trimmed = trim($line);
                if (empty($trimmed)) {
                    $i++;
                    continue;
                }
                
                // Se inizia con un nuovo timestamp o pattern di errore, fermati
                if (self::parse_apache_timestamp($line) || 
                    preg_match('/^(PHP |WordPress |Uncaught )/i', $line)) {
                    break;
                }
                
                // Altrimenti potrebbe essere parte del messaggio o stack trace
                $stack_trace[] = $trimmed;
                $stack_depth++;
            }
            
            $i++;
        }
        
        $info['stack_trace'] = $stack_trace;
        $info['stack_trace_lines'] = $stack_depth;
        
        return $info;
    }
    
    /**
     * Analizza WordPress cron log
     */
    private static function analyze_wp_cron(string $log_path): array
    {
        $issues = [];
        $lines = self::read_log_tail($log_path, 500);
        
        if (empty($lines)) {
            return $issues;
        }
        
        $failed_crons = [];
        $cutoff_time = time() - (24 * 60 * 60);
        
        foreach ($lines as $line) {
            // Cerca errori comuni nei cron
            if (preg_match('/(error|failed|timeout|fatal)/i', $line)) {
                $timestamp = self::parse_wp_cron_timestamp($line);
                if ($timestamp && $timestamp < $cutoff_time) {
                    continue;
                }
                
                $failed_crons[] = self::truncate_line($line, 150);
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
    
    /**
     * Legge le ultime N righe di un file di log (efficiente per file grandi)
     * SICURA: gestisce errori e limita dimensioni per evitare problemi
     */
    private static function read_log_tail(string $file_path, int $lines = 100): array
    {
        // SICUREZZA: disabilita error reporting durante la lettura
        $old_error_reporting = error_reporting(0);
        $old_display_errors = ini_get('display_errors');
        ini_set('display_errors', '0');
        
        try {
            if (!file_exists($file_path) || !is_readable($file_path)) {
                return [];
            }
            
            // LIMITE: non leggere file più grandi di 100MB
            $file_size = @filesize($file_path);
            if ($file_size === false) {
                return [];
            }
            
            $max_file_size = 100 * 1024 * 1024; // 100MB
            if ($file_size > $max_file_size) {
                // File troppo grande, salta
                return [];
            }
            
            // Per file molto grandi, leggi solo la coda (max 5MB)
            $chunk_size = min(5 * 1024 * 1024, $file_size); // Max 5MB per chunk
            
            $handle = @fopen($file_path, 'r');
            if (!$handle) {
                return [];
            }
            
            // Vai alla fine del file
            @fseek($handle, -min($chunk_size, $file_size), SEEK_END);
            
            // Leggi l'ultimo chunk
            $content = @fread($handle, $chunk_size);
            @fclose($handle);
            
            if ($content === false) {
                return [];
            }
            
            // Se il file è piccolo, leggi tutto (ma con limite)
            if ($file_size <= 1024 * 1024) { // Solo se < 1MB
                $content = @file_get_contents($file_path);
                if ($content === false) {
                    return [];
                }
            }
            
            $all_lines = explode("\n", $content);
            $all_lines = array_filter($all_lines, function($line) {
                return trim($line) !== '';
            });
            
            // Ritorna le ultime N righe (limite massimo 1000)
            $max_lines = min($lines, 1000);
            return array_slice($all_lines, -$max_lines);
            
        } catch (\Throwable $e) {
            // Silenzioso: non loggare errori durante la lettura dei log
            return [];
        } finally {
            // Ripristina error reporting
            error_reporting($old_error_reporting);
            ini_set('display_errors', $old_display_errors);
        }
    }
    
    /**
     * Estrae timestamp da riga log Nginx error
     */
    private static function parse_nginx_timestamp(string $line): ?int
    {
        // Formato: 2025/11/08 04:13:03
        if (preg_match('/(\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}:\d{2})/', $line, $matches)) {
            $date_str = str_replace('/', '-', $matches[1]);
            $timestamp = strtotime($date_str);
            return $timestamp ?: null;
        }
        return null;
    }
    
    /**
     * Estrae timestamp da riga log Nginx access
     */
    private static function parse_nginx_access_timestamp(string $line): ?int
    {
        // Formato access log: [08/Nov/2025:04:13:03 +0000]
        if (preg_match('/\[(\d{2}\/\w{3}\/\d{4}:\d{2}:\d{2}:\d{2})/', $line, $matches)) {
            $date_str = str_replace('/', ' ', $matches[1]);
            $date_str = str_replace(':', ' ', $date_str);
            $timestamp = strtotime($date_str);
            return $timestamp ?: null;
        }
        return null;
    }
    
    /**
     * Estrae timestamp da riga log Apache
     */
    private static function parse_apache_timestamp(string $line): ?int
    {
        // Formato simile a Nginx access
        return self::parse_nginx_access_timestamp($line);
    }
    
    /**
     * Estrae timestamp da riga PHP slow log
     */
    private static function parse_php_slow_timestamp(string $date_str): ?int
    {
        // Formato: 08-Nov-2025 06:50:23
        // Prova prima il formato standard
        $timestamp = strtotime($date_str);
        if ($timestamp !== false) {
            return $timestamp;
        }
        
        // Prova a convertire il formato mese inglese
        // 08-Nov-2025 -> 08-11-2025
        $months = [
            'Jan' => '01', 'Feb' => '02', 'Mar' => '03', 'Apr' => '04',
            'May' => '05', 'Jun' => '06', 'Jul' => '07', 'Aug' => '08',
            'Sep' => '09', 'Oct' => '10', 'Nov' => '11', 'Dec' => '12',
        ];
        
        foreach ($months as $en => $num) {
            if (strpos($date_str, $en) !== false) {
                $date_str_numeric = str_replace($en, $num, $date_str);
                $timestamp = strtotime($date_str_numeric);
                if ($timestamp !== false) {
                    return $timestamp;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Estrae timestamp da riga WordPress cron log
     */
    private static function parse_wp_cron_timestamp(string $line): ?int
    {
        // Prova vari formati
        $formats = [
            'Y-m-d H:i:s',
            'd/m/Y H:i:s',
            'Y/m/d H:i:s',
        ];
        
        foreach ($formats as $format) {
            if (preg_match('/(\d{4}[-\/]\d{2}[-\/]\d{2} \d{2}:\d{2}:\d{2})/', $line, $matches)) {
                $timestamp = strtotime($matches[1]);
                if ($timestamp) {
                    return $timestamp;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Tronca una riga a una lunghezza massima
     */
    private static function truncate_line(string $line, int $max_length = 150): string
    {
        $line = trim($line);
        if (strlen($line) <= $max_length) {
            return $line;
        }
        return substr($line, 0, $max_length - 3) . '...';
    }
    
    /**
     * Ottiene nome leggibile per pattern
     */
    private static function get_pattern_name(string $pattern): string
    {
        $names = [
            '/upstream.*closed connection/i' => 'Upstream connection chiusa',
            '/connect.*failed/i' => 'Connessione fallita',
            '/timeout/i' => 'Timeout',
            '/502 Bad Gateway/i' => '502 Bad Gateway',
            '/503 Service Unavailable/i' => '503 Service Unavailable',
            '/504 Gateway Timeout/i' => '504 Gateway Timeout',
            '/500 Internal Server Error/i' => '500 Internal Server Error',
            '/PHP Fatal error/i' => 'PHP Fatal Error',
            '/PHP Parse error/i' => 'PHP Parse Error',
            '/PHP Warning/i' => 'PHP Warning',
            '/WordPress database error/i' => 'WordPress Database Error',
            '/Premature end of script headers/i' => 'Premature end of script headers',
            '/Maximum execution time/i' => 'Maximum execution time exceeded',
            '/foreach\(\) argument must be of type array\|object/i' => 'Foreach Type Error',
            '/call_user_func_array\(\)/i' => 'Callback Function Error',
            '/Uncaught Error/i' => 'Uncaught PHP Error',
            '/Uncaught Exception/i' => 'Uncaught PHP Exception',
            '/AH01071/i' => 'Apache Error AH01071',
        ];
        
        // Normalizza pattern per matching (rimuovi flag regex)
        $normalized = preg_replace('/\/[imsxADSUXu]*$/', '', $pattern);
        
        return $names[$pattern] ?? $names[$normalized] ?? $pattern;
    }
    
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
        // NON loggare nulla durante la lettura
        $old_error_reporting = error_reporting(0);
        $old_display_errors  = ini_get('display_errors');
        $old_log_errors      = ini_get('log_errors');
        ini_set('display_errors', '0');
        ini_set('log_errors', '0');
        
        try {
            $paths = self::get_log_paths();
            
            if (empty($paths['base'])) {
                return ['paths' => [], 'tails' => []];
            }
            
            $tails = [];
            $cutoff = time() - ($hours * 3600);
            
            // ACCESS 5xx: unifica nginx, apache e php access log (esclude .gz di default)
            $accFiles = self::collect_log_files([
                $paths['nginx_access'] ?? '',
                $paths['nginx_acc_glob'] ?? '',
                $paths['apache_access'] ?? '',
                $paths['apache_acc_glob'] ?? '',
                $paths['php_access'] ?? '',
                $paths['php_acc_glob'] ?? '',
            ], false); // false = non include file .gz
            
            $tails['access_5xx'] = [
                'file'    => 'nginx/apache/php access (rotati)',
                'entries' => self::tail_from_files($accFiles, $per_file, function(string $line) use ($cutoff): bool {
                    // Pattern Cloudways: "GET /index.php" 500 ... (pattern più robusto)
                    // Supporta sia "/" 500 " che " " 500 "
                    if (preg_match('/"\s+(\d{3})\s+/', $line, $m) || preg_match('/"\s(\d{3})\s/', $line, $m)) {
                        $status = (int)$m[1];
                        if ($status >= 500) {
                            // Verifica timestamp se presente (nginx/apache access log)
                            $ts = self::parse_nginx_access_timestamp($line);
                            if ($ts && $ts < $cutoff) {
                                return false;
                            }
                            return true;
                        }
                    }
                    return false;
                }),
            ];
            
            // NGINX error (esclude .gz di default)
            $nginxErrFiles = self::collect_log_files([
                $paths['nginx_error'] ?? '',
                $paths['nginx_err_glob'] ?? '',
            ], false);
            
            $tails['nginx_error'] = [
                'file'    => 'nginx-app.error.log*',
                'entries' => self::tail_from_files($nginxErrFiles, $per_file, function(string $line) use ($cutoff): bool {
                    // 2025/11/08 04:13:03
                    $ts = self::parse_nginx_timestamp($line);
                    if ($ts && $ts < $cutoff) {
                        return false;
                    }
                    return stripos($line, '[error]') !== false || stripos($line, '[crit]') !== false;
                }),
            ];
            
            // APACHE error (esclude .gz di default)
            $apacheErrFiles = self::collect_log_files([
                $paths['apache_error'] ?? '',
                $paths['apache_err_glob'] ?? '',
            ], false);
            
            $tails['apache_error'] = [
                'file'    => 'apache*.error.log*',
                'entries' => self::tail_from_files($apacheErrFiles, $per_file, function(string $line) use ($cutoff): bool {
                    $ts = self::parse_apache_timestamp($line);
                    if ($ts && $ts < $cutoff) {
                        return false;
                    }
                    return stripos($line, '[error]') !== false
                        || stripos($line, 'PHP Fatal') !== false
                        || stripos($line, 'Uncaught') !== false;
                }),
            ];
            
            // PHP error (CORRETTO: solo php-app.error.log, esclude .gz di default)
            $phpErrFiles = self::collect_log_files([
                $paths['php_error'] ?? '',
                $paths['php_error_glob'] ?? '',
            ], false);
            
            $tails['php_error'] = [
                'file'    => 'php-app.error.log*',
                'entries' => self::tail_from_files($phpErrFiles, $per_file, function(string $line): bool {
                    // i log PHP spesso non hanno timestamp standard: filtro solo per severità
                    return (bool) preg_match('/PHP (Fatal|Parse|Warning|Notice|Deprecated)|Uncaught (Exception|Error)/i', $line);
                }),
            ];
            
            // PHP slow (esclude .gz di default)
            $slowFiles = self::collect_log_files([
                $paths['php_slow'] ?? '',
                $paths['php_slow_glob'] ?? '',
            ], false);
            
            $tails['php_slow'] = [
                'file'    => 'php-app.slow.log*',
                'entries' => self::tail_from_files($slowFiles, $per_file, function(string $line): bool {
                    // Mostra tutte le righe non vuote (i blocchi slow hanno righe "script_filename" e stack)
                    return trim($line) !== '';
                }),
            ];
            
            // WP cron (esclude .gz di default)
            $cronFiles = self::collect_log_files([
                $paths['wp_cron'] ?? '',
                $paths['wp_cron_glob'] ?? '',
            ], false);
            
            $tails['wp_cron'] = [
                'file'    => 'wp-cron.log*',
                'entries' => self::tail_from_files($cronFiles, $per_file, function(string $line): bool {
                    return stripos($line, 'WordPress database error') !== false
                        || stripos($line, 'error') !== false
                        || stripos($line, 'warn') !== false
                        || stripos($line, 'Executed the cron event') !== false;
                }),
            ];
            
            // Rimuovo chiavi vuote
            foreach ($tails as $k => $v) {
                if (empty($v['entries'])) {
                    unset($tails[$k]);
                }
            }
            
            return ['paths' => $paths, 'tails' => $tails];
            
        } catch (\Throwable $e) {
            return ['paths' => [], 'tails' => []];
        } finally {
            error_reporting($old_error_reporting ?? E_ALL);
            ini_set('display_errors', $old_display_errors ?? '1');
            ini_set('log_errors', $old_log_errors ?? '1');
        }
    }
}

