<?php
namespace gik25microdata\Logs\Analysis;

use gik25microdata\Logs\Analysis\ParserRegistry;
use gik25microdata\Logs\Analysis\Parser\MultiLineParserInterface;
use gik25microdata\Logs\Analysis\Parser\PhpErrorMultiLineParser;
use gik25microdata\Logs\Analysis\Parser\PhpSlowMultiLineParser;
use gik25microdata\Logs\Reader\LogFileReader;
use gik25microdata\Logs\Domain\LogRecord;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Analizza log usando parser e aggregatori
 */
final class LogAnalyzer
{
    private ParserRegistry $registry;
    
    /**
     * @var MultiLineParserInterface[]
     */
    private array $multiLineParsers = [];
    
    public function __construct(?ParserRegistry $registry = null)
    {
        $this->registry = $registry ?? ParserRegistry::createDefault();
        
        // Registra parser multi-linea
        $this->multiLineParsers = [
            new PhpErrorMultiLineParser(),
            new PhpSlowMultiLineParser(),
        ];
    }
    
    /**
     * Analizza un file di log e restituisce issues
     * 
     * @param string $logPath Percorso al file di log
     * @param string $logType Tipo di log (nginx_error, apache_error, php_error, etc.)
     * @param int $maxLines Numero massimo di righe da leggere
     * @param int $cutoffHours Numero di ore da analizzare (0 = tutto)
     * @return array Array di issues
     */
    public function analyze(string $logPath, string $logType, int $maxLines = 1000, int $cutoffHours = 24): array
    {
        $lines = LogFileReader::readTail($logPath, $maxLines);
        
        if (empty($lines)) {
            return [];
        }
        
        $cutoffTime = $cutoffHours > 0 ? time() - ($cutoffHours * 3600) : 0;
        $records = [];
        
        // Verifica se serve parsing multi-linea
        $multiLineParser = $this->getMultiLineParser($logType);
        
        if ($multiLineParser !== null) {
            // Parsing multi-linea
            $i = 0;
            while ($i < count($lines)) {
                $result = $multiLineParser->parseMultiLine($lines, $i);
                
                if ($result['record'] !== null) {
                    // Filtra per timestamp se necessario
                    if ($cutoffTime === 0 || !$result['record']->timestamp || $result['record']->timestamp >= $cutoffTime) {
                        $records[] = $result['record'];
                    }
                }
                
                $i += max(1, $result['consumedLines']);
            }
        } else {
            // Parsing single-line
            foreach ($lines as $line) {
                $record = $this->registry->parse($logType, $line);
                
                if ($record === null) {
                    continue;
                }
                
                // Filtra per timestamp se necessario
                if ($cutoffTime > 0 && $record->timestamp && $record->timestamp < $cutoffTime) {
                    continue;
                }
                
                $records[] = $record;
            }
        }
        
        // Aggrega in issues
        return $this->aggregateRecords($records, $logType);
    }
    
    /**
     * Ottiene parser multi-linea per il tipo di log
     * 
     * @param string $logType
     * @return MultiLineParserInterface|null
     */
    private function getMultiLineParser(string $logType): ?MultiLineParserInterface
    {
        foreach ($this->multiLineParsers as $parser) {
            if ($parser->supports($logType)) {
                return $parser;
            }
        }
        return null;
    }
    
    /**
     * Aggrega LogRecord in issues
     * 
     * @param LogRecord[] $records
     * @param string $logType
     * @return array
     */
    private function aggregateRecords(array $records, string $logType): array
    {
        if (empty($records)) {
            return [];
        }
        
        // Per access log, raggruppa per status HTTP
        if ($logType === 'nginx_access' || $logType === 'apache_access') {
            return LogAggregator::groupByHttpStatus($records, 3);
        }
        
        // Per slow log, usa raggruppamento avanzato
        if ($logType === 'php_slow') {
            return LogAggregator::groupByPhpSlow($records);
        }
        
        // Per PHP errors, usa raggruppamento avanzato
        if ($logType === 'php_error' || $logType === 'php_fpm_error') {
            return LogAggregator::groupByPhpError($records);
        }
        
        // Per altri log, raggruppa per tipo di errore
        $issueType = match($logType) {
            'nginx_error' => 'Nginx Error',
            'apache_error' => 'Apache Error',
            'wp_cron' => 'WordPress Cron',
            default => 'Log Error'
        };
        
        return LogAggregator::groupByErrorType($records, 3, $issueType);
    }
}

