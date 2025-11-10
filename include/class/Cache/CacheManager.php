<?php
namespace gik25microdata\Cache;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sistema Cache Avanzato Multi-Livello
 * 
 * Gestisce cache intelligente con invalidazione automatica
 * Supporta: transients, object cache, HTML cache
 */
class CacheManager
{
    const CACHE_GROUP = 'revious_microdata';
    const CACHE_EXPIRY = 3600; // 1 ora default
    
    /**
     * Ottiene valore dalla cache
     */
    public static function get(string $key, $default = null)
    {
        $cache_key = self::getCacheKey($key);
        
        // Prova object cache prima
        $value = wp_cache_get($cache_key, self::CACHE_GROUP);
        if ($value !== false) {
            return $value;
        }
        
        // Fallback a transient
        $value = get_transient($cache_key);
        if ($value !== false) {
            // Ripopola object cache
            wp_cache_set($cache_key, $value, self::CACHE_GROUP, self::CACHE_EXPIRY);
            return $value;
        }
        
        return $default;
    }
    
    /**
     * Salva valore in cache
     */
    public static function set(string $key, $value, int $expiry = self::CACHE_EXPIRY): bool
    {
        $cache_key = self::getCacheKey($key);
        
        // Salva in object cache
        wp_cache_set($cache_key, $value, self::CACHE_GROUP, $expiry);
        
        // Salva anche in transient per persistenza
        return set_transient($cache_key, $value, $expiry);
    }
    
    /**
     * Elimina valore dalla cache
     */
    public static function delete(string $key): bool
    {
        $cache_key = self::getCacheKey($key);
        
        wp_cache_delete($cache_key, self::CACHE_GROUP);
        return delete_transient($cache_key);
    }
    
    /**
     * Pulisce tutta la cache del plugin
     */
    public static function flush(): bool
    {
        global $wpdb;
        
        // Pulisci transients
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
            '_transient_' . self::CACHE_GROUP . '_%',
            '_transient_timeout_' . self::CACHE_GROUP . '_%'
        ));
        
        // Pulisci object cache
        wp_cache_flush_group(self::CACHE_GROUP);
        
        return true;
    }
    
    /**
     * Cache HTML per shortcode
     */
    public static function getHtmlCache(string $shortcode, array $atts = []): ?string
    {
        $key = 'html_' . md5($shortcode . serialize($atts));
        return self::get($key);
    }
    
    /**
     * Salva HTML in cache
     */
    public static function setHtmlCache(string $shortcode, array $atts, string $html, int $expiry = self::CACHE_EXPIRY): bool
    {
        $key = 'html_' . md5($shortcode . serialize($atts));
        return self::set($key, $html, $expiry);
    }
    
    /**
     * Invalidazione automatica su update post
     */
    public static function invalidateOnPostUpdate(int $post_id): void
    {
        // Invalida cache HTML di tutti gli shortcode
        self::delete('html_*');
        
        // Invalida cache specifica del post
        self::delete('post_' . $post_id);
        
        // Invalida cache query correlate
        self::delete('query_*');
    }
    
    /**
     * Genera cache key univoca
     */
    private static function getCacheKey(string $key): string
    {
        return self::CACHE_GROUP . '_' . md5($key);
    }
    
    /**
     * Statistiche cache
     */
    public static function getStats(): array
    {
        global $wpdb;
        
        $transients = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s",
            '_transient_' . self::CACHE_GROUP . '_%'
        ));
        
        return [
            'transients_count' => (int)$transients,
            'cache_group' => self::CACHE_GROUP,
            'object_cache_enabled' => wp_using_ext_object_cache(),
        ];
    }
}

// Hook per invalidazione automatica
add_action('save_post', [CacheManager::class, 'invalidateOnPostUpdate']);
add_action('delete_post', [CacheManager::class, 'invalidateOnPostUpdate']);
