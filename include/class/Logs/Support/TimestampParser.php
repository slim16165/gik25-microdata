<?php
namespace gik25microdata\Logs\Support;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Parser per timestamp da vari formati di log
 */
class TimestampParser
{
    /**
     * Estrae timestamp da riga log Nginx error
     * Formato: 2025/11/08 04:13:03
     * 
     * @param string $line Riga di log
     * @return int|null Timestamp Unix o null se non trovato
     */
    public static function parseNginx(string $line): ?int
    {
        if (preg_match('/(\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}:\d{2})/', $line, $matches)) {
            $date_str = str_replace('/', '-', $matches[1]);
            $timestamp = strtotime($date_str);
            return $timestamp ?: null;
        }
        return null;
    }
    
    /**
     * Estrae timestamp da riga log Nginx access
     * Formato: [08/Nov/2025:04:13:03 +0000]
     * 
     * @param string $line Riga di log
     * @return int|null Timestamp Unix o null se non trovato
     */
    public static function parseNginxAccess(string $line): ?int
    {
        if (preg_match('/\[(\d{2}\/\w{3}\/\d{4}:\d{2}:\d{2}:\d{2})/', $line, $matches)) {
            $date_str = str_replace('/', ' ', $matches[1]);
            $date_str = str_replace(':', ' ', $date_str);
            $timestamp = strtotime($date_str);
            return $timestamp ?: null;
        }
        return null;
    }
    
    /**
     * Estrae timestamp da riga log Apache
     * Prova prima formato Apache error log, poi formato access
     * 
     * @param string $line Riga di log
     * @return int|null Timestamp Unix o null se non trovato
     */
    public static function parseApache(string $line): ?int
    {
        // Prova prima formato Apache error log: [Sun Nov 09 12:36:55.838882 2025]
        $php_timestamp = self::parsePhpError($line);
        if ($php_timestamp !== null) {
            return $php_timestamp;
        }
        
        // Formato simile a Nginx access
        return self::parseNginxAccess($line);
    }
    
    /**
     * Estrae timestamp da riga log PHP error (formato Apache error log)
     * Formato: [Sun Nov 09 12:36:55.838882 2025] [proxy_fcgi:error] ...
     * 
     * @param string $line Riga di log
     * @return int|null Timestamp Unix o null se non trovato
     */
    public static function parsePhpError(string $line): ?int
    {
        // Pattern: [Sun Nov 09 12:36:55.838882 2025]
        if (preg_match('/\[(\w{3})\s+(\w{3})\s+(\d{1,2})\s+(\d{2}):(\d{2}):(\d{2})\.\d+\s+(\d{4})\]/', $line, $matches)) {
            $day_name = $matches[1];   // Sun
            $month_name = $matches[2]; // Nov
            $day = $matches[3];        // 09
            $hour = $matches[4];       // 12
            $minute = $matches[5];     // 36
            $second = $matches[6];     // 55
            $year = $matches[7];       // 2025
            
            // Converti nome mese in numero
            $months = [
                'Jan' => '01', 'Feb' => '02', 'Mar' => '03', 'Apr' => '04',
                'May' => '05', 'Jun' => '06', 'Jul' => '07', 'Aug' => '08',
                'Sep' => '09', 'Oct' => '10', 'Nov' => '11', 'Dec' => '12',
            ];
            
            if (!isset($months[$month_name])) {
                return null;
            }
            
            $month = $months[$month_name];
            
            // Crea stringa data nel formato standard
            $date_str = sprintf('%s-%s-%02d %s:%s:%s', $year, $month, $day, $hour, $minute, $second);
            
            // Converte in timestamp Unix (considera timezone server)
            $timestamp = strtotime($date_str);
            
            if ($timestamp === false) {
                return null;
            }
            
            return $timestamp;
        }
        
        return null;
    }
    
    /**
     * Estrae timestamp da riga PHP slow log
     * Formato: 08-Nov-2025 06:50:23
     * 
     * @param string $date_str Stringa data
     * @return int|null Timestamp Unix o null se non trovato
     */
    public static function parsePhpSlow(string $date_str): ?int
    {
        // Prova prima il formato standard
        $timestamp = strtotime($date_str);
        if ($timestamp !== false) {
            return $timestamp;
        }
        
        // Prova a convertire il formato mese inglese
        // 08-Nov-2025 -> 08-11-2025
        $months = [
            'Jan' => '01', 'Feb' => '02', 'Mar' => '03', 'Apr' => '04',
            'May' => '05', 'Jun' => '06', 'Jul' => '07', 'Aug' => '08',
            'Sep' => '09', 'Oct' => '10', 'Nov' => '11', 'Dec' => '12',
        ];
        
        foreach ($months as $en => $num) {
            if (strpos($date_str, $en) !== false) {
                $date_str_numeric = str_replace($en, $num, $date_str);
                $timestamp = strtotime($date_str_numeric);
                if ($timestamp !== false) {
                    return $timestamp;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Estrae timestamp da riga WordPress cron log
     * Prova vari formati comuni
     * 
     * @param string $line Riga di log
     * @return int|null Timestamp Unix o null se non trovato
     */
    public static function parseWpCron(string $line): ?int
    {
        // Prova vari formati
        $formats = [
            'Y-m-d H:i:s',
            'd/m/Y H:i:s',
            'Y/m/d H:i:s',
        ];
        
        foreach ($formats as $format) {
            if (preg_match('/(\d{4}[-\/]\d{2}[-\/]\d{2} \d{2}:\d{2}:\d{2})/', $line, $matches)) {
                $timestamp = strtotime($matches[1]);
                if ($timestamp) {
                    return $timestamp;
                }
            }
        }
        
        return null;
    }
}

