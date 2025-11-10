<?php
namespace gik25microdata\SiteSpecific;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Sistema di configurazione dichiarativa per liste di link
 * 
 * Permette di definire liste di link in modo dichiarativo e riutilizzabile,
 * separando i dati dalla logica di rendering.
 * 
 * @package gik25microdata\SiteSpecific
 */
class LinkListConfig
{
    /**
     * Carica configurazione link da array strutturato
     * 
     * @param array $config Configurazione con struttura:
     *   [
     *     'title' => 'Titolo lista',
     *     'type' => 'thumbnail|carousel|simple',
     *     'links' => [
     *       ['url' => '', 'title' => '', 'comment' => '']
     *     ],
     *     'options' => ['columns' => 1, 'list_class' => '...']
     *   ]
     * @return string HTML renderizzato
     */
    public static function renderFromConfig(array $config): string
    {
        $type = $config['type'] ?? 'thumbnail';
        $links = $config['links'] ?? [];
        $options = $config['options'] ?? [];
        
        if (!empty($config['title'])) {
            $options['title'] = $config['title'];
        }
        
        $handler = new class extends SiteSpecificHandler {
            protected static function getSiteName(): string { return 'generic'; }
            public static function registerShortcodes(): void {}
        };
        
        switch ($type) {
            case 'carousel':
                return $handler::createCarouselList($links, $options);
                
            case 'simple':
                return $handler::createSimpleList($links, $options);
                
            case 'thumbnail':
            default:
                return $handler::createThumbnailList($links, $options);
        }
    }
    
    /**
     * Carica configurazione da file JSON
     * 
     * @param string $configPath Percorso al file JSON
     * @return array|null Configurazione o null se errore
     */
    public static function loadFromFile(string $configPath): ?array
    {
        if (!file_exists($configPath) || !is_readable($configPath)) {
            return null;
        }
        
        $content = file_get_contents($configPath);
        $config = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }
        
        return $config;
    }
    
    /**
     * Valida struttura configurazione
     * 
     * @param array $config Configurazione da validare
     * @return bool True se valida
     */
    public static function validateConfig(array $config): bool
    {
        if (empty($config['links']) || !is_array($config['links'])) {
            return false;
        }
        
        foreach ($config['links'] as $link) {
            if (empty($link['url']) || empty($link['title'])) {
                return false;
            }
        }
        
        return true;
    }
}
