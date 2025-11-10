<?php
namespace gik25microdata\ListOfPosts\Cache;

use gik25microdata\ListOfPosts\Types\LinkBase;
use Illuminate\Support\Collection;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sistema di cache per link e query WordPress
 * Usa WordPress Transients API per migliorare le performance
 */
class LinkCache
{
    private const CACHE_PREFIX = 'gik25_link_';
    private const CACHE_GROUP = 'gik25_links';
    private const DEFAULT_EXPIRATION = 3600; // 1 ora
    
    /**
     * Ottiene un link dalla cache
     * 
     * @param string $url URL del link
     * @return LinkBase|null Link in cache o null
     */
    public static function get(string $url): ?LinkBase
    {
        $cache_key = self::getCacheKey($url);
        $cached = get_transient($cache_key);
        
        if ($cached !== false && is_array($cached)) {
            return new LinkBase(
                $cached['title'] ?? '',
                $cached['url'] ?? '',
                $cached['comment'] ?? ''
            );
        }
        
        return null;
    }
    
    /**
     * Salva un link in cache
     * 
     * @param LinkBase $link Link da salvare
     * @param int $expiration Tempo di scadenza in secondi
     * @return bool True se salvato con successo
     */
    public static function set(LinkBase $link, int $expiration = self::DEFAULT_EXPIRATION): bool
    {
        $cache_key = self::getCacheKey($link->Url);
        $data = [
            'title' => $link->Title,
            'url' => $link->Url,
            'comment' => $link->Comment,
        ];
        
        return set_transient($cache_key, $data, $expiration);
    }
    
    /**
     * Ottiene dati del post dalla cache
     * 
     * @param int $post_id ID del post
     * @return array|null Dati del post o null
     */
    public static function getPostData(int $post_id): ?array
    {
        $cache_key = self::getCacheKey('post_' . $post_id);
        $cached = get_transient($cache_key);
        
        if ($cached !== false && is_array($cached)) {
            return $cached;
        }
        
        return null;
    }
    
    /**
     * Salva dati del post in cache
     * 
     * @param int $post_id ID del post
     * @param array $data Dati del post
     * @param int $expiration Tempo di scadenza
     * @return bool
     */
    public static function setPostData(int $post_id, array $data, int $expiration = self::DEFAULT_EXPIRATION): bool
    {
        $cache_key = self::getCacheKey('post_' . $post_id);
        return set_transient($cache_key, $data, $expiration);
    }
    
    /**
     * Ottiene una collezione di link dalla cache
     * 
     * @param string $cache_key Chiave di cache personalizzata
     * @return Collection|null Collezione di link o null
     */
    public static function getCollection(string $cache_key): ?Collection
    {
        $full_key = self::getCacheKey($cache_key);
        $cached = get_transient($full_key);
        
        if ($cached !== false && is_array($cached)) {
            $collection = new Collection();
            foreach ($cached as $linkData) {
                $collection->add(new LinkBase(
                    $linkData['title'] ?? '',
                    $linkData['url'] ?? '',
                    $linkData['comment'] ?? ''
                ));
            }
            return $collection;
        }
        
        return null;
    }
    
    /**
     * Salva una collezione di link in cache
     * 
     * @param string $cache_key Chiave di cache personalizzata
     * @param Collection $links Collezione di link
     * @param int $expiration Tempo di scadenza
     * @return bool
     */
    public static function setCollection(string $cache_key, Collection $links, int $expiration = self::DEFAULT_EXPIRATION): bool
    {
        $full_key = self::getCacheKey($cache_key);
        $data = [];
        
        foreach ($links as $link) {
            if ($link instanceof LinkBase) {
                $data[] = [
                    'title' => $link->Title,
                    'url' => $link->Url,
                    'comment' => $link->Comment,
                ];
            }
        }
        
        return set_transient($full_key, $data, $expiration);
    }
    
    /**
     * Invalida la cache per un URL specifico
     * 
     * @param string $url URL da invalidare
     * @return bool
     */
    public static function invalidate(string $url): bool
    {
        $cache_key = self::getCacheKey($url);
        return delete_transient($cache_key);
    }
    
    /**
     * Invalida tutta la cache dei link
     * 
     * @return int Numero di cache invalidate
     */
    public static function invalidateAll(): int
    {
        global $wpdb;
        $count = 0;
        
        // Elimina tutti i transient che iniziano con il nostro prefisso
        $pattern = '_transient_' . self::CACHE_PREFIX . '%';
        $transients = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
                $pattern
            )
        );
        
        foreach ($transients as $transient) {
            $key = str_replace('_transient_', '', $transient);
            if (delete_transient($key)) {
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Genera una chiave di cache
     * 
     * @param string $identifier Identificatore
     * @return string Chiave di cache
     */
    private static function getCacheKey(string $identifier): string
    {
        $hash = md5($identifier);
        return self::CACHE_PREFIX . substr($hash, 0, 16);
    }
    
    /**
     * Ottiene statistiche sulla cache
     * 
     * @return array Statistiche
     */
    public static function getStats(): array
    {
        global $wpdb;
        $pattern = '_transient_' . self::CACHE_PREFIX . '%';
        
        $total = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s",
                $pattern
            )
        );
        
        return [
            'total_cached_items' => (int)$total,
            'cache_prefix' => self::CACHE_PREFIX,
            'default_expiration' => self::DEFAULT_EXPIRATION,
        ];
    }
}
