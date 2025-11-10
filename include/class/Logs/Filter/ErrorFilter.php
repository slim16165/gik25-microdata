<?php
namespace gik25microdata\Logs\Filter;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Filtro per errori ignorabili o declassabili
 */
class ErrorFilter
{
    /**
     * Pattern di errori che possono essere ignorati o declassati
     * Questi sono errori noti che non sono critici per il funzionamento del sito
     * 
     * @return array Array di pattern regex
     */
    public static function getIgnorablePatterns(): array
    {
        return [
            // Action Scheduler - tabelle mancanti (spesso non critiche)
            '/Table.*actionscheduler.*doesn\'t exist/i',
            '/Table.*actionscheduler.*does not exist/i',
            
            // Altri errori di tabelle opzionali di plugin
            '/Table.*doesn\'t exist.*action_scheduler/i',
            '/Table.*does not exist.*action_scheduler/i',
            
            // Errori di plugin che cercano tabelle opzionali
            '/WordPress database error.*Table.*doesn\'t exist/i',
            
            // Errori specifici di Action Scheduler che sono non critici
            '/ActionScheduler.*Table.*doesn\'t exist/i',
            '/ActionScheduler.*Table.*does not exist/i',
        ];
    }
    
    /**
     * Verifica se un errore può essere ignorato o declassato
     * 
     * @param string $error_line La riga di errore da verificare
     * @param array $context Contesto di esecuzione (opzionale)
     * @return array{ignore: bool, downgrade: bool} ignore=true per ignorare completamente, downgrade=true per declassare a warning
     */
    public static function shouldIgnore(string $error_line, array $context = []): array
    {
        $patterns = self::getIgnorablePatterns();
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $error_line)) {
                // Errori di Action Scheduler per tabelle mancanti vengono IGNORATI completamente
                if (preg_match('/actionscheduler/i', $error_line)) {
                    return ['ignore' => true, 'downgrade' => false];
                }
                // Altri errori di tabelle opzionali mancanti vengono ignorati
                if (preg_match('/Table.*doesn\'t exist.*action_scheduler/i', $error_line)) {
                    return ['ignore' => true, 'downgrade' => false];
                }
            }
        }
        
        return ['ignore' => false, 'downgrade' => false];
    }
    
    /**
     * Verifica se un errore dovrebbe essere ignorato in base al contesto di esecuzione
     * 
     * @param array $execution_context Contesto di esecuzione
     * @return bool true se dovrebbe essere ignorato
     */
    public static function shouldIgnoreByContext(array $execution_context): bool
    {
        // Ignora errori da WP-CRON/Action Scheduler se configurato
        // (puoi estendere questa logica per avere più controllo)
        if ($execution_context['context'] === 'wp_cron') {
            // Per ora non ignoriamo, ma potresti voler ignorare errori da cron se sono troppo frequenti
            return false;
        }
        
        return false;
    }
}

