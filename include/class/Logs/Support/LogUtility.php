<?php
namespace gik25microdata\Logs\Support;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Utility generiche per gestione log
 */
class LogUtility
{
    /**
     * Pattern regex per identificare errori PHP critici (cerca in qualsiasi punto della riga)
     */
    public const PHP_ERROR_PATTERN = '/PHP (Fatal|Parse|Warning|Notice|Deprecated)|Uncaught (Exception|Error)/i';
    
    /**
     * Pattern regex per identificare errori PHP con formato esteso (inizio riga)
     */
    public const PHP_ERROR_PATTERN_START = '~^(PHP (?:Fatal error|Parse error|Warning|Notice|Deprecated))~';
    
    /**
     * Tronca una riga a una lunghezza massima
     * 
     * @param string $line Riga da troncare
     * @param int $max_length Lunghezza massima (default: 1000)
     * @return string Riga troncata
     */
    public static function truncateLine(string $line, int $max_length = 1000): string
    {
        $line = trim($line);
        if (strlen($line) <= $max_length) {
            return $line;
        }
        return substr($line, 0, $max_length - 3) . '...';
    }
    
    /**
     * Ottiene nome leggibile per pattern regex
     * 
     * @param string $pattern Pattern regex
     * @return string Nome leggibile del pattern
     */
    public static function getPatternName(string $pattern): string
    {
        $names = [
            '/upstream.*closed connection/i' => 'Upstream connection chiusa',
            '/connect.*failed/i' => 'Connessione fallita',
            '/timeout/i' => 'Timeout',
            '/502 Bad Gateway/i' => '502 Bad Gateway',
            '/503 Service Unavailable/i' => '503 Service Unavailable',
            '/504 Gateway Timeout/i' => '504 Gateway Timeout',
            '/500 Internal Server Error/i' => '500 Internal Server Error',
            '/PHP Fatal error/i' => 'PHP Fatal Error',
            '/PHP Parse error/i' => 'PHP Parse Error',
            '/PHP Warning/i' => 'PHP Warning',
            '/WordPress database error/i' => 'WordPress Database Error',
            '/Premature end of script headers/i' => 'Premature end of script headers',
            '/Maximum execution time/i' => 'Maximum execution time exceeded',
            '/foreach\(\) argument must be of type array\|object/i' => 'Foreach Type Error',
            '/call_user_func_array\(\)/i' => 'Callback Function Error',
            '/Uncaught Error/i' => 'Uncaught PHP Error',
            '/Uncaught Exception/i' => 'Uncaught PHP Exception',
            '/AH01071/i' => 'Apache Error AH01071',
        ];
        
        // Normalizza pattern per matching (rimuovi flag regex)
        $normalized = preg_replace('/\/[imsxADSUXu]*$/', '', $pattern);
        
        return $names[$pattern] ?? $names[$normalized] ?? $pattern;
    }
}

