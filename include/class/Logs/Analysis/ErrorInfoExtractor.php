<?php
namespace gik25microdata\Logs\Analysis;

use gik25microdata\Logs\Support\TimestampParser;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Estrae informazioni dettagliate da errori PHP
 */
class ErrorInfoExtractor
{
    /**
     * Estrae informazioni dettagliate da un errore PHP (file, riga, messaggio, stack trace)
     * 
     * @param string $error_line Riga di errore
     * @param array $all_lines Tutte le righe del log
     * @param int $current_index Indice della riga corrente
     * @return array{message: string, file: string|null, line: int|null, stack_trace: array, stack_trace_lines: int}
     */
    public static function extractPhpErrorInfo(string $error_line, array $all_lines, int $current_index): array
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
                if (TimestampParser::parseApache($line) || 
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
}

