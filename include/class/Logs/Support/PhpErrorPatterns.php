<?php
namespace gik25microdata\Logs\Support;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Pattern condivisi per identificare errori PHP
 */
final class PhpErrorPatterns
{
    /**
     * Pattern critici per errori PHP con tipo associato
     * 
     * @return array<string, string> Array di pattern => tipo errore
     */
    public static function getCriticalPatterns(): array
    {
        return [
            '/PHP Fatal error/i' => 'fatal',
            '/PHP Parse error/i' => 'parse',
            '/Uncaught Error/i' => 'error',
            '/Uncaught Exception/i' => 'exception',
            '/PHP Warning/i' => 'warning',
            '/WordPress database error/i' => 'database',
            '/Premature end of script headers/i' => 'headers',
            '/Maximum execution time/i' => 'timeout',
        ];
    }
    
    /**
     * Determina severity in base al tipo di errore
     * 
     * @param string $errorType Tipo di errore (fatal, parse, error, etc.)
     * @return string Severity (error, warning, info)
     */
    public static function getSeverity(string $errorType): string
    {
        return match($errorType) {
            'fatal', 'parse', 'error', 'exception', 'database', 'headers' => 'error',
            'warning', 'timeout' => 'warning',
            default => 'info'
        };
    }
}

