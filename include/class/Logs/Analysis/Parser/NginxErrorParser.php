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
 * Parser per log errori Nginx
 */
final class NginxErrorParser implements LogParserInterface
{
    private const CRITICAL_PATTERNS = [
        '/upstream.*closed connection/i' => 'error',
        '/connect.*failed/i' => 'error',
        '/timeout/i' => 'warning',
        '/502 Bad Gateway/i' => 'error',
        '/503 Service Unavailable/i' => 'error',
        '/504 Gateway Timeout/i' => 'error',
        '/500 Internal Server Error/i' => 'error',
    ];
    
    public function supports(string $type): bool
    {
        return $type === 'nginx_error';
    }
    
    public function tryParse(string $line): ?LogRecord
    {
        // Estrai timestamp
        $timestamp = TimestampParser::parseNginx($line);
        
        // Verifica se è una riga di errore
        if (!preg_match('/\[error\]/', $line)) {
            // Controlla pattern critici
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
        } else {
            $severity = 'error';
        }
        
        return new LogRecord(
            timestamp: $timestamp,
            severity: $severity,
            context: 'nginx',
            source: 'nginx_error',
            message: LogUtility::truncateLine($line, 500),
            type: 'nginx_error',
            details: ['raw_line' => $line]
        );
    }
}

