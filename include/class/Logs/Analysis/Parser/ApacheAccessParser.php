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
 * Parser per log access Apache (stesso formato di Nginx access)
 */
final class ApacheAccessParser implements LogParserInterface
{
    public function supports(string $type): bool
    {
        return $type === 'apache_access';
    }
    
    public function tryParse(string $line): ?LogRecord
    {
        // Pattern tipico access log: IP - - [timestamp] "method path protocol" status size
        if (!preg_match('/" (\d{3}) /', $line, $matches)) {
            return null;
        }
        
        $status = (int)$matches[1];
        
        // Interessa solo status 5xx
        if ($status < 500 || $status >= 600) {
            return null;
        }
        
        $timestamp = TimestampParser::parseNginxAccess($line); // Stesso formato timestamp
        
        return new LogRecord(
            timestamp: $timestamp,
            severity: 'error',
            context: 'apache',
            source: 'apache_access',
            message: sprintf('HTTP %d: %s', $status, LogUtility::truncateLine($line, 200)),
            type: 'apache_access',
            details: [
                'status' => $status,
                'raw_line' => $line
            ]
        );
    }
}

