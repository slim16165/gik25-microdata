<?php
namespace gik25microdata\Logs\Analysis;

use gik25microdata\Logs\Resolver\LogSourceResolver;
use gik25microdata\Logs\Viewer\LogFormatter;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Responsabile di leggere il tail dei log e restituire la struttura attesa.
 * NON cambia il formato del risultato rispetto a CloudwaysLogParser::recent_errors_tail().
 */
final class TailReader
{
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
            // Usa LogSourceResolver per discovery unificata
            $base = LogSourceResolver::find_logs_directory();
            
            if (empty($base)) {
                return ['paths' => [], 'tails' => []];
            }
            
            // Per compatibilità, mantieni $paths
            $paths = ['base' => $base];
            
            $tails = [];
            $cutoff = time() - ($hours * 3600);
            
            // Rileva timezone server una sola volta (usato per tutti i log)
            $server_timezone = self::get_server_timezone();
            
            // ACCESS 5xx: unifica nginx, apache e php access log (esclude .gz di default)
            // USA LogSourceResolver per discovery unificata
            $accFiles = LogSourceResolver::get_logs_by_types($base, ['nginx_access', 'apache_access', 'php_access'], false);
            
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
            // USA LogSourceResolver per discovery unificata
            $nginxErrFiles = LogSourceResolver::get_logs_by_type($base, 'nginx_error', false);
            
            $tails['nginx_error'] = [
                'file'    => 'nginx*.error.log*',
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
            // USA LogSourceResolver per discovery unificata
            $apacheErrFiles = LogSourceResolver::get_logs_by_type($base, 'apache_error', false);
            
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
            
            // PHP error (CORRETTO: usa LogSourceResolver, esclude .gz di default)
            // FIX: Cerca anche in apache_error/nginx_error (su Cloudways gli errori PHP finiscono lì)
            $phpErrFiles = LogSourceResolver::get_logs_by_types($base, ['php_error', 'apache_error', 'nginx_error'], false);
            
            // Ottieni timestamp ultima modifica file
            $log_file_mtime = !empty($phpErrFiles) ? @filemtime(reset($phpErrFiles)) : null;
            
            // Estrai ultimo timestamp dai log PHP per verificare se sono indietro
            $last_php_error_timestamp = null;
            $php_entries = self::tail_from_files($phpErrFiles, $per_file * 2, function(string $line) use (&$last_php_error_timestamp): bool {
                // Estrai timestamp se presente (prima di filtrare)
                $ts = self::parse_php_error_timestamp($line);
                if ($ts === null) {
                    $ts = self::parse_apache_timestamp($line);
                }
                if ($ts === null) {
                    $ts = self::parse_nginx_timestamp($line);
                }
                if ($ts !== null && ($last_php_error_timestamp === null || $ts > $last_php_error_timestamp)) {
                    $last_php_error_timestamp = $ts;
                }
                
                // Filtra solo errori critici e importanti
                return (bool) preg_match('/PHP (Fatal|Parse|Warning|Notice|Deprecated)|Uncaught (Exception|Error)/i', $line);
            });
            
            // Limita a per_file righe
            $php_entries = array_slice($php_entries, 0, $per_file);
            
            // Usa timestamp estratto dai log se disponibile, altrimenti filemtime
            $reference_timestamp = $last_php_error_timestamp ?? $log_file_mtime;
            
            $tails['php_error'] = [
                'file'    => 'php/apache/nginx error.log*',
                'entries' => $php_entries,
                'timezone' => $server_timezone,
                'last_modified' => $log_file_mtime,
                'last_error_timestamp' => $last_php_error_timestamp,
                'timestamp_warning' => self::check_log_timestamp_warning($reference_timestamp, $cutoff),
            ];
            
            // PHP slow (esclude .gz di default)
            // USA LogSourceResolver per discovery unificata
            $slowFiles = LogSourceResolver::get_logs_by_type($base, 'php_slow', false);
            
            $tails['php_slow'] = [
                'file'    => 'php-app.slow.log*',
                'entries' => self::tail_from_files($slowFiles, $per_file, function(string $line): bool {
                    // Mostra tutte le righe non vuote (i blocchi slow hanno righe "script_filename" e stack)
                    return trim($line) !== '';
                }),
            ];
            
            // WP cron (esclude .gz di default)
            // USA LogSourceResolver per discovery unificata
            $cronFiles = LogSourceResolver::get_logs_by_type($base, 'wp_cron', false);
            
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
            
            // Aggiungi informazioni timezone e timestamp warning al risultato
            return [
                'paths' => $paths, 
                'tails' => $tails,
                'timezone' => $server_timezone,
            ];
            
        } catch (\Throwable $e) {
            return ['paths' => [], 'tails' => []];
        } finally {
            error_reporting($old_error_reporting ?? E_ALL);
            ini_set('display_errors', $old_display_errors ?? '1');
            ini_set('log_errors', $old_log_errors ?? '1');
        }
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
                        // Non troncare le righe - mantieni intero contenuto (max 5000 caratteri per sicurezza)
                        $ring[] = mb_strlen($line) > 5000 ? mb_substr($line, 0, 5000) . '... [troncato]' : $line;
                        
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
        // Prova prima formato Apache error log: [Sun Nov 09 12:36:55.838882 2025]
        $php_timestamp = self::parse_php_error_timestamp($line);
        if ($php_timestamp !== null) {
            return $php_timestamp;
        }
        
        // Formato simile a Nginx access
        return self::parse_nginx_access_timestamp($line);
    }

    /**
     * Estrae timestamp da riga log PHP error (formato Apache error log)
     * Formato: [Sun Nov 09 12:36:55.838882 2025] [proxy_fcgi:error] ...
     * 
     * @param string $line Riga di log
     * @return int|null Timestamp Unix o null se non trovato
     */
    private static function parse_php_error_timestamp(string $line): ?int
    {
        // Pattern: [Sun Nov 09 12:36:55.838882 2025]
        // Esempio: [Sun Nov 09 12:36:55.838882 2025] [proxy_fcgi:error] [pid 1688106:tid 1688194] [client 65.109.35.209:0] AH01071: Got error 'PHP message: ...
        if (preg_match('/\[(\w{3})\s+(\w{3})\s+(\d{1,2})\s+(\d{2}):(\d{2}):(\d{2})\.\d+\s+(\d{4})\]/', $line, $matches)) {
            $day_name = $matches[1];   // Sun
            $month_name = $matches[2]; // Nov
            $day = $matches[3];        // 09
            $hour = $matches[4];       // 12
            $minute = $matches[5];     // 36
            $second = $matches[6];     // 55
            $year = $matches[7];       // 2025
            
            // Converti nome mese in numero
            $months = [
                'Jan' => '01', 'Feb' => '02', 'Mar' => '03', 'Apr' => '04',
                'May' => '05', 'Jun' => '06', 'Jul' => '07', 'Aug' => '08',
                'Sep' => '09', 'Oct' => '10', 'Nov' => '11', 'Dec' => '12',
            ];
            
            if (!isset($months[$month_name])) {
                return null;
            }
            
            $month = $months[$month_name];
            
            // Crea stringa data nel formato standard
            $date_str = sprintf('%s-%s-%02d %s:%s:%s', $year, $month, $day, $hour, $minute, $second);
            
            $timestamp = strtotime($date_str);
            return $timestamp ?: null;
        }
        return null;
    }

    /**
     * Rileva timezone del server
     * 
     * @return array{timezone: string, offset: int, formatted: string} Informazioni timezone
     */
    private static function get_server_timezone(): array
    {
        // Prova a ottenere timezone da PHP
        $timezone = date_default_timezone_get();
        
        // Se non disponibile, prova da WordPress
        if (function_exists('wp_timezone_string')) {
            $wp_tz = wp_timezone_string();
            if (!empty($wp_tz)) {
                $timezone = $wp_tz;
            }
        }
        
        // Calcola offset in secondi
        try {
            $dt = new \DateTime('now', new \DateTimeZone($timezone));
            $offset = $dt->getOffset();
        } catch (\Exception $e) {
            $offset = 0;
        }
        
        // Formatta offset come +02:00
        $hours = (int)floor(abs($offset) / 3600);
        $minutes = (int)((abs($offset) % 3600) / 60);
        $sign = $offset >= 0 ? '+' : '-';
        $offset_formatted = sprintf('%s%02d:%02d', $sign, $hours, $minutes);
        
        return [
            'timezone' => $timezone,
            'offset' => $offset,
            'formatted' => $offset_formatted,
        ];
    }

    /**
     * Verifica se i log sono indietro/vecchi e restituisce avviso
     * 
     * @param int|null $reference_timestamp Timestamp ultimo errore o ultima modifica file log
     * @param int $cutoff_timestamp Timestamp di cutoff (ultime X ore)
     * @return array{is_stale: bool, message: string, last_error_age: int|null} Informazioni su stato log
     */
    private static function check_log_timestamp_warning(?int $reference_timestamp, int $cutoff_timestamp): array
    {
        $current_time = time();
        
        if ($reference_timestamp === null) {
            return [
                'is_stale' => false,
                'message' => 'Nessun timestamp disponibile',
                'last_error_age' => null,
            ];
        }
        
        // Calcola età ultimo errore (differenza tra ora e timestamp riferimento)
        $last_error_age = $current_time - $reference_timestamp;
        
        // Se ultimo errore è più vecchio di 1 ora, i log potrebbero essere indietro
        $one_hour = 3600;
        $is_stale = $last_error_age > $one_hour;
        
        $message = '';
        if ($is_stale) {
            $hours_ago = round($last_error_age / 3600, 1);
            if ($hours_ago < 24) {
                $message = sprintf('Ultimo errore rilevato ~%.1f ore fa. I log potrebbero essere indietro o non ci sono stati errori recenti.', $hours_ago);
            } else {
                $days_ago = round($hours_ago / 24, 1);
                $message = sprintf('Ultimo errore rilevato ~%.1f giorni fa. I log potrebbero essere vecchi o non ci sono stati errori recenti.', $days_ago);
            }
        } else {
            $minutes_ago = round($last_error_age / 60, 1);
            if ($minutes_ago > 5) {
                $message = sprintf('Ultimo errore rilevato ~%.0f minuti fa.', $minutes_ago);
            } else {
                $message = sprintf('Ultimo errore rilevato ~%.0f minuti fa (recente).', max(1, $minutes_ago));
            }
        }
        
        return [
            'is_stale' => $is_stale,
            'message' => $message,
            'last_error_age' => $last_error_age,
        ];
    }
}
