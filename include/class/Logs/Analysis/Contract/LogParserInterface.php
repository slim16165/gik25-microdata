<?php
namespace gik25microdata\Logs\Analysis\Contract;

use gik25microdata\Logs\Domain\LogRecord;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Interfaccia per parser di log specifici per formato
 */
interface LogParserInterface
{
    /**
     * Verifica se questo parser supporta il tipo di log specificato
     * 
     * @param string $type Tipo di log (es. 'nginx_error', 'apache_error', 'php_error')
     * @return bool
     */
    public function supports(string $type): bool;
    
    /**
     * Tenta di parsare una riga di log
     * 
     * @param string $line Riga di log
     * @return LogRecord|null Record parsato o null se la riga non è valida per questo parser
     */
    public function tryParse(string $line): ?LogRecord;
}

