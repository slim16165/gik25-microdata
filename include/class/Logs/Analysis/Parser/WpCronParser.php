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
 * Parser per log WordPress cron
 */
final class WpCronParser implements LogParserInterface
{
    public function supports(string $type): bool
    {
        return $type === 'wp_cron';
    }
    
    public function tryParse(string $line): ?LogRecord
    {
        // Cerca errori comuni nei cron
        if (!preg_match('/(error|failed|timeout|fatal)/i', $line)) {
            return null;
        }
        
        $timestamp = TimestampParser::parseWpCron($line);
        
        return new LogRecord(
            timestamp: $timestamp,
            severity: 'warning',
            context: 'wp_cron',
            source: 'wp_cron',
            message: LogUtility::truncateLine($line, 200),
            type: 'wp_cron',
            details: ['raw_line' => $line]
        );
    }
}

