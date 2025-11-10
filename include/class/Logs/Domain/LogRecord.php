<?php
namespace gik25microdata\Logs\Domain;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * DTO per rappresentare un record di log normalizzato
 */
final class LogRecord
{
    public function __construct(
        public readonly ?int $timestamp = null,
        public readonly string $severity = 'info',
        public readonly string $context = 'unknown',
        public readonly ?string $source = null,
        public readonly string $message = '',
        public readonly string $type = 'unknown',
        public readonly ?string $file = null,
        public readonly ?int $line = null,
        public readonly array $stackTrace = [],
        public readonly array $details = []
    ) {}
    
    /**
     * Converte in array per compatibilità con codice esistente
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'timestamp' => $this->timestamp,
            'severity' => $this->severity,
            'context' => $this->context,
            'source' => $this->source,
            'message' => $this->message,
            'type' => $this->type,
            'file' => $this->file,
            'line' => $this->line,
            'stack_trace' => $this->stackTrace,
            'details' => $this->details,
        ];
    }
}

