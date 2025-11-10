<?php
namespace gik25microdata\ListOfPosts\Template;

use gik25microdata\ListOfPosts\Types\LinkBase;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sistema di template personalizzabili per rendering link
 * Permette di definire template custom per diversi stili
 */
class TemplateManager
{
    private static array $templates = [];
    
    /**
     * Registra un template personalizzato
     * 
     * @param string $name Nome del template
     * @param callable $renderer Funzione di rendering
     * @return bool True se registrato con successo
     */
    public static function register(string $name, callable $renderer): bool
    {
        self::$templates[$name] = $renderer;
        return true;
    }
    
    /**
     * Rimuove un template
     * 
     * @param string $name Nome del template
     * @return bool True se rimosso
     */
    public static function unregister(string $name): bool
    {
        if (isset(self::$templates[$name])) {
            unset(self::$templates[$name]);
            return true;
        }
        return false;
    }
    
    /**
     * Renderizza usando un template
     * 
     * @param string $name Nome del template
     * @param LinkBase $link Link da renderizzare
     * @param array $options Opzioni aggiuntive
     * @return string HTML renderizzato
     */
    public static function render(string $name, LinkBase $link, array $options = []): string
    {
        if (!isset(self::$templates[$name])) {
            // Fallback al template standard
            return self::renderDefault($link, $options);
        }
        
        $renderer = self::$templates[$name];
        return call_user_func($renderer, $link, $options);
    }
    
    /**
     * Renderizza template di default
     * 
     * @param LinkBase $link Link da renderizzare
     * @param array $options Opzioni
     * @return string HTML
     */
    private static function renderDefault(LinkBase $link, array $options = []): string
    {
        $withImage = $options['withImage'] ?? true;
        $url = esc_url($link->Url);
        $title = esc_html($link->Title);
        
        if ($withImage) {
            return "<li><a href=\"{$url}\"><img src=\"\" alt=\"{$title}\" />{$title}</a></li>";
        }
        
        return "<li><a href=\"{$url}\">{$title}</a></li>";
    }
    
    /**
     * Ottiene lista di template registrati
     * 
     * @return array Nomi dei template
     */
    public static function getRegistered(): array
    {
        return array_keys(self::$templates);
    }
    
    /**
     * Verifica se un template esiste
     * 
     * @param string $name Nome del template
     * @return bool True se esiste
     */
    public static function exists(string $name): bool
    {
        return isset(self::$templates[$name]);
    }
    
    /**
     * Carica template da file
     * 
     * @param string $name Nome del template
     * @param string $file_path Percorso al file template
     * @return bool True se caricato
     */
    public static function loadFromFile(string $name, string $file_path): bool
    {
        if (!file_exists($file_path)) {
            return false;
        }
        
        // Carica il file e registra la funzione
        $renderer = include $file_path;
        if (is_callable($renderer)) {
            return self::register($name, $renderer);
        }
        
        return false;
    }
}
