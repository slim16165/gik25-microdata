<?php
namespace gik25microdata\SiteSpecific;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registry centralizzato per gestire configurazioni e funzionalità site-specific
 * Astrarre la logica duplicata e rendere più facile aggiungere nuovi siti
 */
class SiteSpecificRegistry
{
    /**
     * @var array<string, SiteConfig> Configurazioni per dominio
     */
    private static array $configs = [];
    
    /**
     * @var string|null Dominio corrente rilevato
     */
    private static ?string $currentDomain = null;
    
    /**
     * Registra una configurazione per un dominio
     * 
     * @param string $domain Dominio (es. 'www.totaldesign.it')
     * @param SiteConfig $config Configurazione del sito
     * @return void
     */
    public static function register(string $domain, SiteConfig $config): void
    {
        self::$configs[$domain] = $config;
    }
    
    /**
     * Ottiene la configurazione per il dominio corrente
     * 
     * @return SiteConfig|null Configurazione o null se non trovata
     */
    public static function getCurrentConfig(): ?SiteConfig
    {
        $domain = self::getCurrentDomain();
        return self::$configs[$domain] ?? null;
    }
    
    /**
     * Ottiene la configurazione per un dominio specifico
     * 
     * @param string $domain
     * @return SiteConfig|null
     */
    public static function getConfig(string $domain): ?SiteConfig
    {
        return self::$configs[$domain] ?? null;
    }
    
    /**
     * Verifica se un dominio è registrato
     * 
     * @param string $domain
     * @return bool
     */
    public static function isRegistered(string $domain): bool
    {
        return isset(self::$configs[$domain]);
    }
    
    /**
     * Rileva il dominio corrente
     * 
     * @return string|null Dominio o null se non rilevabile
     */
    public static function getCurrentDomain(): ?string
    {
        if (self::$currentDomain !== null) {
            return self::$currentDomain;
        }
        
        if (!isset($_SERVER['HTTP_HOST'])) {
            return null;
        }
        
        self::$currentDomain = $_SERVER['HTTP_HOST'];
        return self::$currentDomain;
    }
    
    /**
     * Ottiene tutti i domini registrati
     * 
     * @return array<string> Array di domini
     */
    public static function getRegisteredDomains(): array
    {
        return array_keys(self::$configs);
    }
    
    /**
     * Carica il file specifico per il dominio corrente se esiste
     * 
     * @param string $pluginDir Directory del plugin
     * @return void
     */
    public static function loadCurrentSiteFile(string $pluginDir): void
    {
        $domain = self::getCurrentDomain();
        if (!$domain) {
            return;
        }
        
        $config = self::getConfig($domain);
        if (!$config) {
            return;
        }
        
        $filePath = $pluginDir . '/include/site_specific/' . $config->getSpecificFile();
        if (file_exists($filePath)) {
            require_once $filePath;
        }
    }
}
