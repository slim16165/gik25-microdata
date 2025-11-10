<?php
namespace gik25microdata\Logs\Support;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Helper per gestione timezone e timestamp
 */
class TimezoneHelper
{
    /**
     * Rileva timezone del server
     * 
     * @return array{timezone: string, offset: int, formatted: string} Informazioni timezone
     */
    public static function getServerTimezone(): array
    {
        // Prova a ottenere timezone da PHP
        $timezone = date_default_timezone_get();
        
        // Se non disponibile, prova da WordPress
        if (function_exists('wp_timezone_string')) {
            $wp_tz = wp_timezone_string();
            if (!empty($wp_tz)) {
                $timezone = $wp_tz;
            }
        }
        
        // Calcola offset in secondi
        try {
            $dt = new \DateTime('now', new \DateTimeZone($timezone));
            $offset = $dt->getOffset();
        } catch (\Exception $e) {
            $offset = 0;
        }
        
        // Formatta offset come +02:00
        $hours = (int)floor(abs($offset) / 3600);
        $minutes = (int)((abs($offset) % 3600) / 60);
        $sign = $offset >= 0 ? '+' : '-';
        $offset_formatted = sprintf('%s%02d:%02d', $sign, $hours, $minutes);
        
        return [
            'timezone' => $timezone,
            'offset' => $offset,
            'formatted' => $offset_formatted,
        ];
    }
    
    /**
     * Verifica se i log sono indietro/vecchi e restituisce avviso
     * 
     * @param int|null $reference_timestamp Timestamp ultimo errore o ultima modifica file log
     * @param int $cutoff_timestamp Timestamp di cutoff (ultime X ore)
     * @return array{is_stale: bool, message: string, last_error_age: int|null} Informazioni su stato log
     */
    public static function checkTimestampWarning(?int $reference_timestamp, int $cutoff_timestamp): array
    {
        $current_time = time();
        
        if ($reference_timestamp === null) {
            return [
                'is_stale' => false,
                'message' => 'Nessun timestamp disponibile',
                'last_error_age' => null,
            ];
        }
        
        // Calcola età ultimo errore (differenza tra ora e timestamp riferimento)
        $last_error_age = $current_time - $reference_timestamp;
        
        // Se ultimo errore è più vecchio di 1 ora, i log potrebbero essere indietro
        $one_hour = 3600;
        $is_stale = $last_error_age > $one_hour;
        
        $message = '';
        if ($is_stale) {
            $hours_ago = round($last_error_age / 3600, 1);
            if ($hours_ago < 24) {
                $message = sprintf('Ultimo errore rilevato ~%.1f ore fa. I log potrebbero essere indietro o non ci sono stati errori recenti.', $hours_ago);
            } else {
                $days_ago = round($hours_ago / 24, 1);
                $message = sprintf('Ultimo errore rilevato ~%.1f giorni fa. I log potrebbero essere vecchi o non ci sono stati errori recenti.', $days_ago);
            }
        } else {
            $minutes_ago = round($last_error_age / 60, 1);
            if ($minutes_ago > 5) {
                $message = sprintf('Ultimo errore rilevato ~%.0f minuti fa.', $minutes_ago);
            } else {
                $message = sprintf('Ultimo errore rilevato ~%.0f minuti fa (recente).', max(1, $minutes_ago));
            }
        }
        
        return [
            'is_stale' => $is_stale,
            'message' => $message,
            'last_error_age' => $last_error_age,
        ];
    }
}

