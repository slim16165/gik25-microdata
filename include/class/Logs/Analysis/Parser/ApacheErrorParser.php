<?php
namespace gik25microdata\Logs\Analysis\Parser;

use gik25microdata\Logs\Analysis\Contract\LogParserInterface;
use gik25microdata\Logs\Domain\LogRecord;
use gik25microdata\Logs\Support\TimestampParser;
use gik25microdata\Logs\Support\LogUtility;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Parser per log errori Apache
 */
final class ApacheErrorParser implements LogParserInterface
{
    private const CRITICAL_PATTERNS = [
        '/PHP Fatal error/i' => 'error',
        '/PHP Parse error/i' => 'error',
        '/PHP Warning/i' => 'warning',
        '/Premature end of script headers/i' => 'error',
        '/Maximum execution time/i' => 'warning',
    ];
    
    public function supports(string $type): bool
    {
        return $type === 'apache_error';
    }
    
    public function tryParse(string $line): ?LogRecord
    {
        $timestamp = TimestampParser::parseApache($line);
        
        // Cerca pattern critici
        $matched = false;
        $severity = 'info';
        foreach (self::CRITICAL_PATTERNS as $pattern => $sev) {
            if (preg_match($pattern, $line)) {
                $matched = true;
                $severity = $sev;
                break;
            }
        }
        
        if (!$matched) {
            return null;
        }
        
        return new LogRecord(
            timestamp: $timestamp,
            severity: $severity,
            context: 'apache',
            source: 'apache_error',
            message: LogUtility::truncateLine($line, 500),
            type: 'apache_error',
            details: ['raw_line' => $line]
        );
    }
}

