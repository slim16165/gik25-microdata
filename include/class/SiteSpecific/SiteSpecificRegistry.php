<?php
namespace gik25microdata\SiteSpecific;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use gik25microdata\Utility\ServerHelper;

/**
 * Registry centralizzato per la gestione della configurazione dei siti specifici
 * 
 * Astrae la logica di rilevamento e caricamento dei file specifici per sito,
 * rendendola più generale e riutilizzabile.
 */
class SiteSpecificRegistry
{
    /**
     * Mapping domini -> file specifici
     * 
     * @var array<string, string>
     */
    private static array $domainMapping = [
        'www.nonsolodiete.it' => 'nonsolodiete_specific.php',
        'www.superinformati.com' => 'superinformati_specific.php',
        'www.totaldesign.it' => 'totaldesign_specific.php',
        'www.chiecosa.it' => 'chiecosa_specific.php',
        'www.prestitinforma.it' => 'prestitinforma_specific.php',
    ];
    
    /**
     * Cache del sito corrente rilevato
     * 
     * @var string|null
     */
    private static ?string $currentSite = null;
    
    /**
     * Cache del file specifico caricato
     * 
     * @var string|null
     */
    private static ?string $loadedFile = null;
    
    /**
     * Rileva il sito corrente basandosi sul dominio
     * 
     * @return string|null Nome del file specifico o null se non trovato
     */
    public static function detectCurrentSite(): ?string
    {
        if (self::$currentSite !== null) {
            return self::$currentSite;
        }
        
        if (!isset($_SERVER['HTTP_HOST'])) {
            return null;
        }
        
        $domain = $_SERVER['HTTP_HOST'];
        
        // Rimuove porta se presente
        if (strpos($domain, ':') !== false) {
            $domain = explode(':', $domain)[0];
        }
        
        self::$currentSite = self::$domainMapping[$domain] ?? null;
        
        return self::$currentSite;
    }
    
    /**
     * Carica il file specifico per il sito corrente
     * 
     * @param string $pluginDir Directory del plugin
     * @return bool True se il file è stato caricato con successo
     */
    public static function loadSiteSpecificFile(string $pluginDir): bool
    {
        $specificFile = self::detectCurrentSite();
        
        if ($specificFile === null) {
            return false;
        }
        
        // Evita di caricare lo stesso file più volte
        if (self::$loadedFile === $specificFile) {
            return true;
        }
        
        $filePath = $pluginDir . '/include/site_specific/' . $specificFile;
        
        if (!file_exists($filePath)) {
            if (function_exists('error_log')) {
                error_log("[Revious Microdata] File specifico per dominio non trovato: {$specificFile}");
            }
            return false;
        }
        
        require_once $filePath;
        self::$loadedFile = $specificFile;
        
        return true;
    }
    
    /**
     * Registra un nuovo mapping dominio -> file
     * 
     * @param string $domain Dominio (es. 'www.example.com')
     * @param string $file Nome del file (es. 'example_specific.php')
     * @return void
     */
    public static function registerDomain(string $domain, string $file): void
    {
        self::$domainMapping[$domain] = $file;
        // Reset cache per forzare nuovo rilevamento
        self::$currentSite = null;
    }
    
    /**
     * Ottiene tutti i domini registrati
     * 
     * @return array<string> Array di domini
     */
    public static function getRegisteredDomains(): array
    {
        return array_keys(self::$domainMapping);
    }
    
    /**
     * Verifica se un dominio è registrato
     * 
     * @param string $domain Dominio da verificare
     * @return bool True se registrato
     */
    public static function isDomainRegistered(string $domain): bool
    {
        return isset(self::$domainMapping[$domain]);
    }
    
    /**
     * Ottiene il nome del file specifico per un dominio
     * 
     * @param string $domain Dominio
     * @return string|null Nome del file o null se non trovato
     */
    public static function getFileForDomain(string $domain): ?string
    {
        return self::$domainMapping[$domain] ?? null;
    }
    
    /**
     * Resetta la cache (utile per testing)
     * 
     * @return void
     */
    public static function resetCache(): void
    {
        self::$currentSite = null;
        self::$loadedFile = null;
    }
}
