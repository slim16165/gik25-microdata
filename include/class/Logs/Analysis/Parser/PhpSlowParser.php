<?php
namespace gik25microdata\Logs\Analysis\Parser;

use gik25microdata\Logs\Analysis\Contract\LogParserInterface;
use gik25microdata\Logs\Domain\LogRecord;
use gik25microdata\Logs\Support\TimestampParser;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Parser per PHP slow log (multi-linea, gestito separatamente)
 * 
 * Nota: Questo parser è usato principalmente per validazione.
 * Il parsing completo dei slow log richiede gestione multi-linea
 * che viene gestita in CloudwaysLogParser::analyze_php_slow()
 */
final class PhpSlowParser implements LogParserInterface
{
    public function supports(string $type): bool
    {
        return $type === 'php_slow';
    }
    
    public function tryParse(string $line): ?LogRecord
    {
        // Pattern: [08-Nov-2025 06:50:23] [pool name] pid number
        if (!preg_match('/^\[(\d{2}-\w{3}-\d{4} \d{2}:\d{2}:\d{2})\]\s+\[pool\s+(\w+)\]\s+pid\s+(\d+)/', $line, $matches)) {
            return null;
        }
        
        $timestamp_str = $matches[1];
        $timestamp = TimestampParser::parsePhpSlow($timestamp_str);
        
        return new LogRecord(
            timestamp: $timestamp,
            severity: 'warning',
            context: 'php',
            source: 'php_slow',
            message: sprintf('Slow request: pool=%s pid=%s', $matches[2], $matches[3]),
            type: 'php_slow',
            details: [
                'pool' => $matches[2],
                'pid' => $matches[3],
                'raw_line' => $line
            ]
        );
    }
}

