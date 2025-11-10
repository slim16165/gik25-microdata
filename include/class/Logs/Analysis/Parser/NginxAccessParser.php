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
 * Parser per log access Nginx
 */
final class NginxAccessParser implements LogParserInterface
{
    public function supports(string $type): bool
    {
        return $type === 'nginx_access';
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
        
        $timestamp = TimestampParser::parseNginxAccess($line);
        
        return new LogRecord(
            timestamp: $timestamp,
            severity: 'error',
            context: 'nginx',
            source: 'nginx_access',
            message: sprintf('HTTP %d: %s', $status, LogUtility::truncateLine($line, 200)),
            type: 'nginx_access',
            details: [
                'status' => $status,
                'raw_line' => $line
            ]
        );
    }
}

