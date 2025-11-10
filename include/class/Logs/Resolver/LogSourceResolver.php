<?php
namespace gik25microdata\Logs\Resolver;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Risolutore unificato per la discovery dei file di log su Cloudways
 * 
 * Evita duplicazioni di code-path e gestisce tutti i pattern necessari.
 * Questa classe centralizza TUTTA la discovery dei log in un unico punto.
 */
final class LogSourceResolver
{
    /**
     * Catalogo completo dei pattern di log Cloudways
     * 
     * IMPORTANTE: Questi pattern devono includere TUTTI i file di log
     * che possono contenere errori PHP, incluso apache_wordpress-*.error.log*
     * e nginx_wordpress-*.error.log* dove finiscono gli errori PHP su Cloudways.
     * 
     * @return array<string,array{pattern:string,type:string}>
     */
    public static function catalog(): array
    {
        // EVITA GLOB_BRACE: non è portabile. Usa pattern espliciti.
        return [
            // ERROR - dove finiscono gli errori PHP su Cloudways (PRIORITÀ ALTA)
            ['pattern' => 'apache_wordpress-*.error.log*', 'type' => 'apache_error'],
            ['pattern' => 'nginx_wordpress-*.error.log*',  'type' => 'nginx_error'],
            ['pattern' => 'apache_*.error.log*',           'type' => 'apache_error'],
            ['pattern' => 'nginx_*.error.log*',            'type' => 'nginx_error'],
            ['pattern' => 'nginx-app.error.log*',          'type' => 'nginx_error'],
            ['pattern' => 'php-app.error.log*',            'type' => 'php_error'],
            ['pattern' => 'php*-app.error.log*',           'type' => 'php_error'],
            ['pattern' => 'php*.error.log*',               'type' => 'php_error'],
            ['pattern' => 'php*-fpm.error.log*',           'type' => 'php_fpm_error'],

            // SLOW/ACCESS/CRON/STATUS
            ['pattern' => 'php-app.slow.log*',             'type' => 'php_slow'],
            ['pattern' => 'php*-app.slow.log*',            'type' => 'php_slow'],
            ['pattern' => 'php-app.access.log*',           'type' => 'php_access'],
            ['pattern' => 'php*-app.access.log*',          'type' => 'php_access'],
            ['pattern' => 'apache_wordpress-*.access.log*','type' => 'apache_access'],
            ['pattern' => 'apache_*.access.log*',          'type' => 'apache_access'],
            ['pattern' => 'nginx_wordpress-*.access.log*', 'type' => 'nginx_access'],
            ['pattern' => 'nginx_*.access.log*',           'type' => 'nginx_access'],
            ['pattern' => 'nginx-app.access.log*',         'type' => 'nginx_access'],
            ['pattern' => 'wp-cron.log*',                  'type' => 'wp_cron'],
            ['pattern' => 'nginx-app.status.log*',         'type' => 'nginx_status'],
        ];
    }

    /**
     * Scopre tutti i file di log disponibili
     * 
     * @param string $baseDir Directory base dei log (es. /home/.../logs/)
     * @param bool $include_gz Se includere anche file .gz (default: true per discovery completa)
     * @return array<int,array{path:string,type:string,mtime:int,source:string}>
     */
    public static function discover(string $baseDir, bool $include_gz = true): array
    {
        // Normalizza il base path (una sola volta)
        $base = rtrim(realpath($baseDir) ?: $baseDir, '/') . '/';
        
        if (!is_dir($base) || !is_readable($base)) {
            return [];
        }
        
        $out = [];
        
        foreach (self::catalog() as $entry) {
            $pattern = $base . $entry['pattern'];
            // NON usare GLOB_BRACE: non è portabile su tutti i sistemi
            $matches = glob($pattern, GLOB_NOSORT) ?: [];
            
            foreach ($matches as $file) {
                // Verifica che sia un file leggibile
                if (!is_file($file) || !is_readable($file)) {
                    continue;
                }
                
                // Gestione .gz: includi solo se richiesto
                if (!$include_gz && str_ends_with($file, '.gz')) {
                    continue;
                }
                
                $out[] = [
                    'path'   => $file,
                    'type'   => $entry['type'],
                    'mtime'  => @filemtime($file) ?: 0,
                    'source' => $entry['pattern'],
                ];
            }
        }
        
        // Dedup: rimuovi duplicati (stesso file trovato da pattern diversi)
        // Tieni il file più recente tra i duplicati
        usort($out, fn($a, $b) => $b['mtime'] <=> $a['mtime']);
        
        $seen = [];
        $uniq = [];
        
        foreach ($out as $row) {
            // Chiave unica: tipo + nome base del file (senza estensioni rotazione)
            // Rimuovi estensioni di rotazione: .1, .2, .gz, .1.gz, .2.gz, ecc.
            $base_name = preg_replace('/\.(\d+\.gz|gz|\d+)$/i', '', basename($row['path']));
            $key = strtolower($row['type'] . ':' . $base_name);
            
            if (isset($seen[$key])) {
                continue; // Già visto, skip
            }
            
            $seen[$key] = true;
            $uniq[] = $row;
        }
        
        return $uniq;
    }
    
    /**
     * Ottiene i file di log per un tipo specifico
     * 
     * @param string $baseDir Directory base dei log
     * @param string $type Tipo di log (es. 'apache_error', 'php_error', 'nginx_error')
     * @param bool $include_gz Se includere file .gz
     * @return array<string> Array di percorsi file ordinati per mtime (più recenti prima)
     */
    public static function get_logs_by_type(string $baseDir, string $type, bool $include_gz = true): array
    {
        $all = self::discover($baseDir, $include_gz);
        
        $filtered = array_filter($all, fn($row) => $row['type'] === $type);
        
        // Ordina per mtime (più recenti prima)
        usort($filtered, fn($a, $b) => $b['mtime'] <=> $a['mtime']);
        
        return array_column($filtered, 'path');
    }
    
    /**
     * Ottiene i file di log per più tipi (es. tutti gli errori)
     * 
     * @param string $baseDir Directory base dei log
     * @param array<string> $types Array di tipi di log
     * @param bool $include_gz Se includere file .gz
     * @return array<string> Array di percorsi file ordinati per mtime (più recenti prima)
     */
    public static function get_logs_by_types(string $baseDir, array $types, bool $include_gz = true): array
    {
        $all = self::discover($baseDir, $include_gz);
        
        $filtered = array_filter($all, fn($row) => in_array($row['type'], $types, true));
        
        // Ordina per mtime (più recenti prima)
        usort($filtered, fn($a, $b) => $b['mtime'] <=> $a['mtime']);
        
        return array_column($filtered, 'path');
    }
    
    /**
     * Trova la directory logs/ su Cloudways
     * 
     * @return string|null Percorso directory logs o null se non trovata
     */
    public static function find_logs_directory(): ?string
    {
        $possible_paths = [
            ABSPATH . '../logs/',
            ABSPATH . '../../logs/',
        ];
        
        // Prova a cercare nella struttura tipica Cloudways
        $abs_path = rtrim(str_replace('\\', '/', ABSPATH), '/');
        $parts = explode('/', trim($abs_path, '/'));
        
        if (($key = array_search('public_html', $parts)) !== false) {
            $parts[$key] = 'logs';
            $possible_paths[] = '/' . implode('/', $parts) . '/';
        }
        
        // Prova pattern comuni Cloudways con glob
        $glob_patterns = [
            '/home/*/logs/',
            '/home/*/*/logs/',
            '/home/*/*/*/logs/',
        ];
        
        foreach ($glob_patterns as $pattern) {
            $matches = glob($pattern);
            if (!empty($matches)) {
                foreach ($matches as $match) {
                    if (is_dir($match) && is_readable($match)) {
                        $possible_paths[] = rtrim($match, '/') . '/';
                    }
                }
            }
        }
        
        // Rimuovi duplicati
        $possible_paths = array_unique($possible_paths);
        
        // Prova i percorsi in ordine
        foreach ($possible_paths as $path) {
            $normalized = rtrim(str_replace('\\', '/', $path), '/') . '/';
            $resolved = realpath($normalized);
            
            if ($resolved && is_dir($resolved) && is_readable($resolved)) {
                return $resolved . '/';
            }
        }
        
        return null;
    }
    
    /**
     * Seleziona il file di errore più recente dai candidati
     * 
     * @param array<string,array<int,string>> $candidates Array di candidati per tipo
     * @return string|null Percorso al file selezionato o null
     */
    public static function selectErrorFile(array $candidates): ?string
    {
        foreach (['apache_error','nginx_error','php_error','php_fpm_error'] as $k) {
            if (!empty($candidates[$k])) {
                usort($candidates[$k], function ($a, $b) {
                    $ma = @filemtime($a) ?: 0;
                    $mb = @filemtime($b) ?: 0;
                    return $mb <=> $ma;
                });
                return $candidates[$k][0];
            }
        }
        return null;
    }
}

