<?php
namespace gik25microdata\ListOfPosts\Enhancement;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sistema di miglioramento immagini
 * Gestisce lazy loading, fallback, ottimizzazione
 */
class ImageEnhancer
{
    /**
     * Genera HTML per immagine con lazy loading
     * 
     * @param string $src URL dell'immagine
     * @param string $alt Testo alternativo
     * @param array $attributes Attributi aggiuntivi
     * @return string HTML dell'immagine
     */
    public static function lazyImage(string $src, string $alt = '', array $attributes = []): string
    {
        $defaults = [
            'loading' => 'lazy',
            'decoding' => 'async',
            'width' => $attributes['width'] ?? 50,
            'height' => $attributes['height'] ?? 50,
            'class' => $attributes['class'] ?? '',
        ];
        
        $attrs = array_merge($defaults, $attributes);
        $attrs['src'] = esc_url($src);
        $attrs['alt'] = esc_attr($alt);
        
        // Rimuovi attributi vuoti
        $attrs = array_filter($attrs, function($value) {
            return $value !== '' && $value !== null;
        });
        
        $html = '<img';
        foreach ($attrs as $key => $value) {
            if ($key !== 'src' && $key !== 'alt') {
                $html .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
            } else {
                $html .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
            }
        }
        $html .= ' />';
        
        return $html;
    }
    
    /**
     * Ottiene URL immagine con fallback
     * 
     * @param int|null $post_id ID del post
     * @param string $size Dimensione immagine
     * @param string $fallback_url URL di fallback
     * @return string URL dell'immagine
     */
    public static function getImageWithFallback(?int $post_id, string $size = 'thumbnail', string $fallback_url = ''): string
    {
        if ($post_id) {
            $image_url = get_the_post_thumbnail_url($post_id, $size);
            if ($image_url) {
                return $image_url;
            }
        }
        
        // Usa fallback personalizzato se fornito
        if (!empty($fallback_url)) {
            return $fallback_url;
        }
        
        // Usa placeholder di default del plugin
        return plugins_url('gik25-microdata/assets/images/placeholder-200x200.png');
    }
    
    /**
     * Genera srcset per immagini responsive
     * 
     * @param int $post_id ID del post
     * @param array $sizes Array di dimensioni ['thumbnail', 'medium', 'large']
     * @return string Attributo srcset
     */
    public static function generateSrcset(int $post_id, array $sizes = ['thumbnail', 'medium', 'large']): string
    {
        $srcset = [];
        
        foreach ($sizes as $size) {
            $image_url = get_the_post_thumbnail_url($post_id, $size);
            if ($image_url) {
                $image_data = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), $size);
                if ($image_data) {
                    $srcset[] = esc_url($image_url) . ' ' . $image_data[1] . 'w';
                }
            }
        }
        
        return !empty($srcset) ? implode(', ', $srcset) : '';
    }
    
    /**
     * Ottimizza URL immagine (WebP se disponibile)
     * 
     * @param string $image_url URL originale
     * @return string URL ottimizzato
     */
    public static function optimizeImageUrl(string $image_url): string
    {
        // Se il server supporta WebP e l'immagine non è già WebP
        if (function_exists('imagewebp') && strpos($image_url, '.webp') === false) {
            // Prova a trovare versione WebP
            $webp_url = str_replace(['.jpg', '.jpeg', '.png'], '.webp', $image_url);
            
            // Verifica se esiste (può essere costoso, meglio usare cache)
            // Per ora restituiamo l'URL originale
            // In produzione si può implementare verifica con cache
        }
        
        return $image_url;
    }
    
    /**
     * Genera picture element con fallback
     * 
     * @param int $post_id ID del post
     * @param string $alt Testo alternativo
     * @param array $options Opzioni
     * @return string HTML picture element
     */
    public static function pictureElement(int $post_id, string $alt = '', array $options = []): string
    {
        $default_size = $options['size'] ?? 'thumbnail';
        $fallback_url = self::getImageWithFallback($post_id, $default_size);
        
        $html = '<picture>';
        
        // Source per WebP se disponibile
        $webp_url = self::optimizeImageUrl($fallback_url);
        if ($webp_url !== $fallback_url) {
            $html .= '<source srcset="' . esc_url($webp_url) . '" type="image/webp">';
        }
        
        // Immagine fallback
        $html .= self::lazyImage($fallback_url, $alt, $options);
        $html .= '</picture>';
        
        return $html;
    }
}
