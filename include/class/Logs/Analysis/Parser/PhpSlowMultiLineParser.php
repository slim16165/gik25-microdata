<?php
namespace gik25microdata\Logs\Analysis\Parser;

use gik25microdata\Logs\Analysis\Parser\MultiLineParserInterface;
use gik25microdata\Logs\Domain\LogRecord;
use gik25microdata\Logs\Support\TimestampParser;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Parser per PHP slow log con gestione multi-linea (entry separate da righe vuote)
 */
final class PhpSlowMultiLineParser implements MultiLineParserInterface
{
    public function supports(string $type): bool
    {
        return $type === 'php_slow';
    }
    
    public function parseMultiLine(array $lines, int $startIndex): array
    {
        if ($startIndex >= count($lines)) {
            return ['record' => null, 'consumedLines' => 0];
        }
        
        $line = trim($lines[$startIndex]);
        
        // Pattern: [08-Nov-2025 06:50:23] [pool name] pid number
        if (!preg_match('/^\[(\d{2}-\w{3}-\d{4} \d{2}:\d{2}:\d{2})\]\s+\[pool\s+(\w+)\]\s+pid\s+(\d+)/', $line, $matches)) {
            return ['record' => null, 'consumedLines' => 1];
        }
        
        $timestamp_str = $matches[1];
        $timestamp = TimestampParser::parsePhpSlow($timestamp_str);
        $pool = $matches[2];
        $pid = $matches[3];
        
        $script = '';
        $script_full = '';
        $stack = [];
        $consumedLines = 1;
        $found_script = false;
        
        // Leggi righe successive fino a riga vuota o fine array
        $i = $startIndex + 1;
        while ($i < count($lines)) {
            $next_line = trim($lines[$i]);
            
            // Riga vuota: fine entry
            if (empty($next_line)) {
                $consumedLines++;
                break;
            }
            
            // script_filename = /path/to/script
            if (preg_match('/^script_filename\s*=\s*(.+)$/', $next_line, $script_matches)) {
                $script_full = trim($script_matches[1]);
                $script = basename($script_full);
                $found_script = true;
                $consumedLines++;
                $i++;
                continue;
            }
            
            // Stack trace lines: [0x...] function() /path/to/file:line
            if ($found_script && preg_match('/^\[0x[\da-f]+\]\s+(.+)$/', $next_line, $stack_matches)) {
                $stack_line = trim($stack_matches[1]);
                // Prendi solo le prime 5 righe dello stack
                if (count($stack) < 5) {
                    $stack[] = $stack_line;
                }
                $consumedLines++;
                $i++;
                continue;
            }
            
            // Se non matcha nessun pattern noto, potrebbe essere fine entry o nuova entry
            // Controlla se inizia con nuovo timestamp
            if (preg_match('/^\[\d{2}-\w{3}-\d{4} \d{2}:\d{2}:\d{2}\]/', $next_line)) {
                // Nuova entry, fermati qui
                break;
            }
            
            // Altrimenti continua (potrebbe essere parte dello stack trace)
            $consumedLines++;
            $i++;
        }
        
        // Se non abbiamo trovato lo script, salta questa entry
        if (!$found_script) {
            return ['record' => null, 'consumedLines' => $consumedLines];
        }
        
        $record = new LogRecord(
            timestamp: $timestamp,
            severity: 'warning',
            context: 'php',
            source: 'php_slow',
            message: sprintf('Slow request: %s (pool=%s pid=%s)', $script, $pool, $pid),
            type: 'php_slow',
            file: $script_full,
            stackTrace: $stack,
            details: [
                'pool' => $pool,
                'pid' => $pid,
                'script' => $script,
                'script_full' => $script_full,
            ]
        );
        
        return ['record' => $record, 'consumedLines' => $consumedLines];
    }
}

