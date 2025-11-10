<?php
namespace gik25microdata\Logs\Reader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Lettura efficiente di file di log
 */
class LogFileReader
{
    /**
     * Legge le ultime N righe di un file di log (efficiente per file grandi)
     * SICURA: gestisce errori e limita dimensioni per evitare problemi
     * 
     * @param string $file_path Percorso al file
     * @param int $lines Numero di righe da leggere (default: 100)
     * @return array Array di righe
     */
    public static function readTail(string $file_path, int $lines = 100): array
    {
        // SICUREZZA: disabilita error reporting durante la lettura
        $old_error_reporting = error_reporting(0);
        $old_display_errors = ini_get('display_errors');
        ini_set('display_errors', '0');
        
        try {
            if (!file_exists($file_path) || !is_readable($file_path)) {
                return [];
            }
            
            // LIMITE: non leggere file più grandi di 100MB
            $file_size = @filesize($file_path);
            if ($file_size === false) {
                return [];
            }
            
            $max_file_size = 100 * 1024 * 1024; // 100MB
            if ($file_size > $max_file_size) {
                // File troppo grande, salta
                return [];
            }
            
            // Se $lines è 0 o molto grande (>10000), leggi tutto il file (analisi completa)
            // Altrimenti leggi solo la coda per performance
            $read_full_file = ($lines <= 0 || $lines > 10000);
            
            if ($read_full_file) {
                // Analisi completa: leggi tutto il file (fino a 100MB)
                $content = @file_get_contents($file_path);
                if ($content === false) {
                    return [];
                }
            } else {
                // Leggi solo la coda (ultimi 5MB per file grandi)
                $chunk_size = min(5 * 1024 * 1024, $file_size); // Max 5MB per chunk
                
                $handle = @fopen($file_path, 'r');
                if (!$handle) {
                    return [];
                }
                
                // Vai alla fine del file
                @fseek($handle, -min($chunk_size, $file_size), SEEK_END);
                
                // Leggi l'ultimo chunk
                $content = @fread($handle, $chunk_size);
                @fclose($handle);
                
                if ($content === false) {
                    return [];
                }
                
                // Se il file è piccolo, leggi tutto (ma con limite)
                if ($file_size <= 1024 * 1024) { // Solo se < 1MB
                    $content = @file_get_contents($file_path);
                    if ($content === false) {
                        return [];
                    }
                }
            }
            
            $all_lines = explode("\n", $content);
            $all_lines = array_filter($all_lines, function($line) {
                return trim($line) !== '';
            });
            
            // Ritorna le ultime N righe
            // Se $lines è 0 o molto grande, rimuovi il limite (per analisi completa)
            if ($lines <= 0 || $lines > 10000) {
                // Analisi completa: ritorna tutte le righe
                return array_values($all_lines);
            }
            
            // Limita a $lines righe (massimo 10000 per performance)
            $max_lines = min($lines, 10000);
            return array_slice($all_lines, -$max_lines);
            
        } catch (\Throwable $e) {
            // Silenzioso: non loggare errori durante la lettura dei log
            return [];
        } finally {
            // Ripristina error reporting
            error_reporting($old_error_reporting);
            ini_set('display_errors', $old_display_errors);
        }
    }
    
    /**
     * Legge la coda (ultime ~K righe) da più file (plain + gz)
     * 
     * @param array $files Array di percorsi file (già ordinati per mtime)
     * @param int $max_lines Numero massimo di righe da restituire
     * @param callable $accept Callback per filtrare righe (return true per accettare)
     * @return array Array di righe (ultime N che matchano il filtro)
     */
    public static function tailFromFiles(array $files, int $max_lines, callable $accept): array
    {
        $ring = [];
        
        foreach ($files as $file) {
            // Skip file .gz (non dovrebbero esserci se collect_log_files esclude .gz, ma controllo di sicurezza)
            if (substr($file, -3) === '.gz') {
                continue;
            }
            
            try {
                $fh = @fopen($file, 'rb');
                
                if (!$fh) {
                    continue;
                }
                
                $file_size = @filesize($file);
                
                // Per file grandi, leggi solo la coda (ultimi 2MB) - così leggiamo sempre gli errori più recenti
                // Anche se il file è gigante, leggiamo solo gli ultimi 2MB (dove ci sono gli errori più recenti)
                if ($file_size && $file_size > 2 * 1024 * 1024) {
                    @fseek($fh, -min(2 * 1024 * 1024, $file_size), SEEK_END);
                }
                
                while (($line = @fgets($fh)) !== false) {
                    $line = rtrim($line, "\r\n");
                    
                    if ($accept($line)) {
                        // Non troncare le righe - mantieni intero contenuto (max 5000 caratteri per sicurezza)
                        $ring[] = mb_strlen($line) > 5000 ? mb_substr($line, 0, 5000) . '... [troncato]' : $line;
                        
                        // Mantieni solo le ultime N*12 righe in memoria (per avere abbastanza materiale)
                        if (count($ring) > $max_lines * 12) {
                            array_splice($ring, 0, count($ring) - $max_lines * 12);
                        }
                    }
                }
                
                @fclose($fh);
                
                // Se abbiamo già abbastanza righe, fermati
                if (count($ring) >= $max_lines) {
                    break;
                }
                
            } catch (\Throwable $e) {
                // Silenzioso: continua con il prossimo file
                if (isset($fh) && $fh) {
                    @fclose($fh);
                }
                continue;
            }
        }
        
        // Ritorna solo le ultime N righe utili
        return array_slice($ring, -$max_lines);
    }
}

