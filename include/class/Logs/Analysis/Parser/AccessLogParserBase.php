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
 * Classe base per parser di access log (Apache e Nginx condividono lo stesso formato)
 */
abstract class AccessLogParserBase implements LogParserInterface
{
    /**
     * Restituisce il tipo di log supportato
     */
    abstract protected function getLogType(): string;
    
    /**
     * Restituisce il contesto (es. 'apache' o 'nginx')
     */
    abstract protected function getContext(): string;
    
    public function supports(string $type): bool
    {
        return $type === $this->getLogType();
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
        
        $timestamp = TimestampParser::parseNginxAccess($line); // Stesso formato timestamp per Apache e Nginx
        
        return new LogRecord(
            timestamp: $timestamp,
            severity: 'error',
            context: $this->getContext(),
            source: $this->getLogType(),
            message: sprintf('HTTP %d: %s', $status, LogUtility::truncateLine($line, 200)),
            type: $this->getLogType(),
            details: [
                'status' => $status,
                'raw_line' => $line
            ]
        );
    }
}

