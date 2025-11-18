<?php
namespace gik25microdata\Logs\Analysis\Parser;

use gik25microdata\Logs\Analysis\Contract\LogParserInterface;
use gik25microdata\Logs\Domain\LogRecord;
use gik25microdata\Logs\Support\TimestampParser;
use gik25microdata\Logs\Support\ContextExtractor;
use gik25microdata\Logs\Support\PhpErrorPatterns;
use gik25microdata\Logs\Filter\ErrorFilter;
use gik25microdata\Logs\Analysis\ErrorInfoExtractor;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Parser per log errori PHP
 */
class PhpErrorParser implements LogParserInterface
{
    public function supports(string $type): bool
    {
        return $type === 'php_error' || $type === 'php_fpm_error';
    }
    
    public function tryParse(string $line): ?LogRecord
    {
        $timestamp = TimestampParser::parseApache($line);
        
        // Estrai contesto di esecuzione
        $execution_context = ContextExtractor::extract($line);
        
        // Verifica se l'errore dovrebbe essere ignorato
        $ignore_check = ErrorFilter::shouldIgnore($line, $execution_context);
        if ($ignore_check['ignore']) {
            return null;
        }
        
        // Verifica se ignorare per contesto
        if (ErrorFilter::shouldIgnoreByContext($execution_context)) {
            return null;
        }
        
        // Cerca pattern di errore
        $matched_pattern = null;
        $error_type = null;
        foreach (PhpErrorPatterns::getCriticalPatterns() as $pattern => $type) {
            if (preg_match($pattern, $line)) {
                $matched_pattern = $pattern;
                $error_type = $type;
                break;
            }
        }
        
        if (!$matched_pattern) {
            return null;
        }
        
        // Estrai informazioni errore (file, riga, messaggio)
        // Nota: per stack trace completo serve l'array di tutte le righe, qui estraiamo solo info base
        $error_info = ErrorInfoExtractor::extractPhpErrorInfo($line, [$line], 0);
        
        // Determina severity
        $severity = PhpErrorPatterns::getSeverity($error_type);
        
        return new LogRecord(
            timestamp: $timestamp,
            severity: $severity,
            context: $execution_context,
            source: 'php_error',
            message: $error_info['message'],
            type: 'php_error',
            file: $error_info['file'],
            line: $error_info['line'],
            stackTrace: $error_info['stack_trace'],
            details: [
                'error_type' => $error_type,
                'pattern' => $matched_pattern,
                'raw_line' => $line
            ]
        );
    }
}

