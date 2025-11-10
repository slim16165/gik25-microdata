<?php
namespace gik25microdata\Logs\Analysis;

use gik25microdata\Logs\Analysis\Contract\LogParserInterface;
use gik25microdata\Logs\Domain\LogRecord;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registry per gestire parser multipli e selezionare quello corretto
 */
final class ParserRegistry
{
    /**
     * @var LogParserInterface[]
     */
    private array $parsers = [];
    
    /**
     * @param LogParserInterface[] $parsers
     */
    public function __construct(array $parsers = [])
    {
        foreach ($parsers as $parser) {
            $this->register($parser);
        }
    }
    
    /**
     * Registra un parser
     * 
     * @param LogParserInterface $parser
     */
    public function register(LogParserInterface $parser): void
    {
        $this->parsers[] = $parser;
    }
    
    /**
     * Tenta di parsare una riga usando il parser appropriato per il tipo
     * 
     * @param string $type Tipo di log
     * @param string $line Riga di log
     * @return LogRecord|null Record parsato o null
     */
    public function parse(string $type, string $line): ?LogRecord
    {
        foreach ($this->parsers as $parser) {
            if ($parser->supports($type)) {
                $record = $parser->tryParse($line);
                if ($record !== null) {
                    return $record;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Crea registry con tutti i parser predefiniti
     * 
     * @return self
     */
    public static function createDefault(): self
    {
        return new self([
            new Parser\NginxErrorParser(),
            new Parser\NginxAccessParser(),
            new Parser\ApacheErrorParser(),
            new Parser\ApacheAccessParser(),
            new Parser\PhpErrorParser(),
            new Parser\PhpSlowParser(),
            new Parser\PhpFpmErrorParser(),
            new Parser\WpCronParser(),
        ]);
    }
}

