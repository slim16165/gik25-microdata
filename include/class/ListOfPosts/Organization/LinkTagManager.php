<?php
namespace gik25microdata\ListOfPosts\Organization;

use gik25microdata\ListOfPosts\Types\LinkBase;
use Illuminate\Support\Collection;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sistema di tag/categorie per organizzare link
 */
class LinkTagManager
{
    private static array $tags = [];
    
    /**
     * Aggiunge un tag a un link
     * 
     * @param LinkBase $link Link da taggare
     * @param string|array $tags Tag o array di tag
     * @return bool True se aggiunto
     */
    public static function addTag(LinkBase $link, $tags): bool
    {
        $url = $link->Url;
        
        if (!isset(self::$tags[$url])) {
            self::$tags[$url] = [];
        }
        
        $tagsArray = is_array($tags) ? $tags : [$tags];
        
        foreach ($tagsArray as $tag) {
            $tag = sanitize_text_field($tag);
            if (!empty($tag) && !in_array($tag, self::$tags[$url], true)) {
                self::$tags[$url][] = $tag;
            }
        }
        
        // Salva in opzione WordPress
        self::saveTags();
        
        return true;
    }
    
    /**
     * Rimuove un tag da un link
     * 
     * @param LinkBase $link Link
     * @param string $tag Tag da rimuovere
     * @return bool True se rimosso
     */
    public static function removeTag(LinkBase $link, string $tag): bool
    {
        $url = $link->Url;
        
        if (isset(self::$tags[$url])) {
            $key = array_search($tag, self::$tags[$url], true);
            if ($key !== false) {
                unset(self::$tags[$url][$key]);
                self::$tags[$url] = array_values(self::$tags[$url]);
                self::saveTags();
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Ottiene i tag di un link
     * 
     * @param LinkBase|string $link Link o URL
     * @return array Array di tag
     */
    public static function getTags($link): array
    {
        $url = $link instanceof LinkBase ? $link->Url : $link;
        return self::$tags[$url] ?? [];
    }
    
    /**
     * Filtra link per tag
     * 
     * @param Collection $links Collezione di link
     * @param string|array $tags Tag da filtrare
     * @return Collection Link filtrati
     */
    public static function filterByTags(Collection $links, $tags): Collection
    {
        $tagsArray = is_array($tags) ? $tags : [$tags];
        
        return $links->filter(function($link) use ($tagsArray) {
            if (!($link instanceof LinkBase)) {
                return false;
            }
            
            $linkTags = self::getTags($link);
            return !empty(array_intersect($tagsArray, $linkTags));
        });
    }
    
    /**
     * Ottiene tutti i tag disponibili
     * 
     * @return array Array di tag
     */
    public static function getAllTags(): array
    {
        $allTags = [];
        foreach (self::$tags as $urlTags) {
            $allTags = array_merge($allTags, $urlTags);
        }
        return array_unique($allTags);
    }
    
    /**
     * Salva i tag in opzione WordPress
     */
    private static function saveTags(): void
    {
        update_option('gik25_link_tags', self::$tags, false);
    }
    
    /**
     * Carica i tag da opzione WordPress
     */
    public static function loadTags(): void
    {
        $saved = get_option('gik25_link_tags', []);
        if (is_array($saved)) {
            self::$tags = $saved;
        }
    }
    
    /**
     * Pulisce i tag non più utilizzati
     * 
     * @param Collection $activeLinks Link attualmente utilizzati
     * @return int Numero di tag rimossi
     */
    public static function cleanupTags(Collection $activeLinks): int
    {
        $activeUrls = $activeLinks->map(function($link) {
            return $link instanceof LinkBase ? $link->Url : $link;
        })->toArray();
        
        $removed = 0;
        foreach (self::$tags as $url => $tags) {
            if (!in_array($url, $activeUrls, true)) {
                unset(self::$tags[$url]);
                $removed++;
            }
        }
        
        if ($removed > 0) {
            self::saveTags();
        }
        
        return $removed;
    }
}

// Carica i tag all'avvio
add_action('init', [LinkTagManager::class, 'loadTags'], 1);
