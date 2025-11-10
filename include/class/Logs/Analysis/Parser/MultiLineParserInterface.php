<?php
namespace gik25microdata\Logs\Analysis\Parser;

use gik25microdata\Logs\Domain\LogRecord;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Interfaccia per parser che gestiscono entry multi-linea
 */
interface MultiLineParserInterface
{
    /**
     * Verifica se questo parser supporta il tipo di log specificato
     * 
     * @param string $type Tipo di log
     * @return bool
     */
    public function supports(string $type): bool;
    
    /**
     * Parsa entry multi-linea
     * 
     * @param array $lines Array di righe del log
     * @param int $startIndex Indice di partenza
     * @return array{record: LogRecord|null, consumedLines: int} Record parsato e numero di righe consumate
     */
    public function parseMultiLine(array $lines, int $startIndex): array;
}

