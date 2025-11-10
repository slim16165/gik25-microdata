<?php
namespace gik25microdata\Logs\Analysis;

use gik25microdata\Logs\Domain\LogRecord;
use gik25microdata\Logs\Support\LogUtility;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Aggrega LogRecord in "issues" per compatibilità con output esistente
 */
final class LogAggregator
{
    /**
     * Raggruppa LogRecord per tipo di errore e crea issues
     * 
     * @param LogRecord[] $records
     * @param int $minCount Soglia minima per creare issue
     * @param string $issueType Tipo di issue (es. 'Nginx Error', 'PHP Error')
     * @return array Array di issues
     */
    public static function groupByErrorType(array $records, int $minCount = 3, string $issueType = 'Log Error'): array
    {
        $groups = [];
        
        foreach ($records as $record) {
            $key = self::getGroupKey($record);
            
            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'type' => $issueType,
                    'severity' => $record->severity,
                    'count' => 0,
                    'examples' => [],
                    'first_seen' => $record->timestamp ?? time(),
                    'last_seen' => $record->timestamp ?? time(),
                ];
            }
            
            $groups[$key]['count']++;
            $groups[$key]['last_seen'] = max($groups[$key]['last_seen'], $record->timestamp ?? time());
            
            // Raccogli esempi (fino a 5)
            if (count($groups[$key]['examples']) < 5) {
                $example = self::formatExample($record);
                if (!in_array($example, $groups[$key]['examples'])) {
                    $groups[$key]['examples'][] = $example;
                }
            }
        }
        
        // Converti in issues
        $issues = [];
        foreach ($groups as $group) {
            if ($group['count'] >= $minCount) {
                $issues[] = [
                    'type' => $group['type'],
                    'severity' => $group['severity'],
                    'message' => self::formatMessage($group),
                    'count' => $group['count'],
                    'examples' => $group['examples'],
                ];
            }
        }
        
        return $issues;
    }
    
    /**
     * Raggruppa per script/file
     * 
     * @param LogRecord[] $records
     * @param int $minCount
     * @param string $issueType
     * @return array
     */
    public static function groupByScript(array $records, int $minCount = 5, string $issueType = 'Script Error'): array
    {
        $groups = [];
        
        foreach ($records as $record) {
            $script = $record->file ? basename($record->file) : 'unknown';
            $key = $script;
            
            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'script' => $script,
                    'count' => 0,
                    'examples' => [],
                ];
            }
            
            $groups[$key]['count']++;
            
            if (count($groups[$key]['examples']) < 8) {
                $example = self::formatExample($record);
                if (!in_array($example, $groups[$key]['examples'])) {
                    $groups[$key]['examples'][] = $example;
                }
            }
        }
        
        $issues = [];
        foreach ($groups as $group) {
            if ($group['count'] >= $minCount) {
                $issues[] = [
                    'type' => $issueType,
                    'severity' => 'warning',
                    'message' => sprintf('Script: %s (%d occorrenze)', $group['script'], $group['count']),
                    'count' => $group['count'],
                    'examples' => array_slice($group['examples'], 0, 5),
                ];
            }
        }
        
        return $issues;
    }
    
    /**
     * Raggruppa per status HTTP
     * 
     * @param LogRecord[] $records
     * @param int $minCount
     * @return array
     */
    public static function groupByHttpStatus(array $records, int $minCount = 3): array
    {
        $groups = [];
        
        foreach ($records as $record) {
            $status = $record->details['status'] ?? null;
            if (!$status) {
                continue;
            }
            
            $key = (string)$status;
            
            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'status' => $status,
                    'count' => 0,
                    'examples' => [],
                ];
            }
            
            $groups[$key]['count']++;
            
            if (count($groups[$key]['examples']) < 8) {
                $example = LogUtility::truncateLine($record->message, 150);
                if (!in_array($example, $groups[$key]['examples'])) {
                    $groups[$key]['examples'][] = $example;
                }
            }
        }
        
        $issues = [];
        foreach ($groups as $group) {
            if ($group['count'] >= $minCount) {
                $issues[] = [
                    'type' => 'HTTP Error ' . $group['status'],
                    'severity' => 'error',
                    'message' => sprintf('Errori HTTP %s: %d occorrenze', $group['status'], $group['count']),
                    'count' => $group['count'],
                    'examples' => $group['examples'],
                ];
            }
        }
        
        return $issues;
    }
    
    /**
     * Raggruppa PHP errors con raggruppamento avanzato (file, line, contexts, stack traces)
     * 
     * @param LogRecord[] $records
     * @return array
     */
    public static function groupByPhpError(array $records): array
    {
        $error_groups = [];
        
        foreach ($records as $record) {
            $error_type = $record->details['error_type'] ?? 'unknown';
            $pattern = $record->details['pattern'] ?? '';
            
            // Crea chiave di raggruppamento: tipo + file + riga (se disponibile)
            $group_key = $error_type;
            if (!empty($record->file)) {
                $group_key .= '|' . basename($record->file);
                if (!empty($record->line)) {
                    $group_key .= ':' . $record->line;
                }
            } else {
                // Se non abbiamo file, usa il messaggio (troncato)
                $message_key = substr($record->message, 0, 100);
                $group_key .= '|' . md5($message_key);
            }
            
            // Inizializza gruppo se non esiste
            if (!isset($error_groups[$group_key])) {
                $error_groups[$group_key] = [
                    'type' => $error_type,
                    'pattern' => $pattern,
                    'count' => 0,
                    'examples' => [],
                    'stack_traces' => [],
                    'files' => [],
                    'lines' => [],
                    'contexts' => [],
                    'first_seen' => $record->timestamp ?? time(),
                    'last_seen' => $record->timestamp ?? time(),
                ];
            }
            
            $error_groups[$group_key]['count']++;
            $error_groups[$group_key]['last_seen'] = max($error_groups[$group_key]['last_seen'], $record->timestamp ?? time());
            
            // Aggiungi contesto
            $context_key = $record->context ?? 'unknown';
            if (!in_array($context_key, $error_groups[$group_key]['contexts'])) {
                $error_groups[$group_key]['contexts'][] = $context_key;
            }
            
            // Aggiungi file e riga
            if (!empty($record->file)) {
                $file_basename = basename($record->file);
                if (!in_array($file_basename, $error_groups[$group_key]['files'])) {
                    $error_groups[$group_key]['files'][] = $file_basename;
                }
                if (!empty($record->line) && !in_array($record->line, $error_groups[$group_key]['lines'])) {
                    $error_groups[$group_key]['lines'][] = $record->line;
                }
            }
            
            // Raccogli esempio completo con stack trace
            if (count($error_groups[$group_key]['examples']) < 5) {
                $example = [
                    'message' => $record->message,
                    'file' => $record->file,
                    'line' => $record->line,
                    'stack_trace' => $record->stackTrace,
                    'context' => $context_key,
                    'timestamp' => $record->timestamp,
                ];
                $error_groups[$group_key]['examples'][] = $example;
            }
        }
        
        // Converti gruppi in issues
        $issues = [];
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
    
    /**
     * Raggruppa PHP slow requests per script con dettagli
     * 
     * @param LogRecord[] $records
     * @return array
     */
    public static function groupByPhpSlow(array $records): array
    {
        $scripts = [];
        
        foreach ($records as $record) {
            $script = $record->file ? basename($record->file) : 'unknown';
            
            if (!isset($scripts[$script])) {
                $scripts[$script] = [
                    'script' => $script,
                    'count' => 0,
                    'examples' => [],
                ];
            }
            
            $scripts[$script]['count']++;
            
            // Raccogli più esempi per script diversi (fino a 8 per script per avere varietà)
            if (count($scripts[$script]['examples']) < 8 && !empty($record->file)) {
                // Crea esempio più informativo
                $example_parts = [basename($record->file)];
                
                // Aggiungi timestamp se disponibile
                if (!empty($record->timestamp)) {
                    $example_parts[] = date('H:i:s', $record->timestamp);
                }
                
                // Aggiungi prima riga dello stack se disponibile
                if (!empty($record->stackTrace) && is_array($record->stackTrace) && count($record->stackTrace) > 0) {
                    $first_stack = $record->stackTrace[0];
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
        
        $issues = [];
        foreach ($scripts as $script => $data) {
            if ($data['count'] >= 5) {
                $issues[] = [
                    'type' => 'PHP Slow Request',
                    'severity' => 'warning',
                    'message' => sprintf('Script lento: %s (%d occorrenze nelle ultime 24 ore)', $script, $data['count']),
                    'count' => $data['count'],
                    'examples' => array_slice($data['examples'], 0, 5),
                ];
            }
        }
        
        // Warning generale se ci sono molte slow requests
        if (count($records) >= 10) {
            $issues[] = [
                'type' => 'PHP Slow Request',
                'severity' => 'warning',
                'message' => sprintf('Totale slow requests: %d nelle ultime 24 ore', count($records)),
                'count' => count($records),
                'examples' => [],
            ];
        }
        
        return $issues;
    }
    
    private static function getGroupKey(LogRecord $record): string
    {
        $errorType = $record->details['error_type'] ?? 'unknown';
        $file = $record->file ? basename($record->file) : null;
        $line = $record->line;
        
        if ($file && $line) {
            return $errorType . '|' . $file . ':' . $line;
        } elseif ($file) {
            return $errorType . '|' . $file;
        } else {
            return $errorType . '|' . md5(substr($record->message, 0, 100));
        }
    }
    
    private static function formatExample(LogRecord $record): string
    {
        if ($record->file && $record->line) {
            return sprintf('%s (%s:%d)', LogUtility::truncateLine($record->message, 100), basename($record->file), $record->line);
        }
        return LogUtility::truncateLine($record->message, 150);
    }
    
    private static function formatMessage(array $group): string
    {
        $errorType = $group['error_type'] ?? 'error';
        $count = $group['count'];
        return sprintf('%s: %d occorrenze', ucfirst($errorType), $count);
    }
}

