<?php
namespace gik25microdata\Image;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Image Optimization System
 * 
 * Lazy loading, WebP conversion, compression, responsive images
 */
class ImageOptimizer
{
    /**
     * Inizializza image optimizer
     */
    public static function init(): void
    {
        add_filter('wp_get_attachment_image_attributes', [self::class, 'addLazyLoading'], 10, 3);
        add_filter('the_content', [self::class, 'addLazyLoadingToContent'], 99);
        add_filter('wp_calculate_image_srcset', [self::class, 'addWebPSrcset'], 10, 5);
    }
    
    /**
     * Aggiunge lazy loading alle immagini
     */
    public static function addLazyLoading(array $attr, $attachment, $size): array
    {
        // WordPress 5.5+ ha lazy loading nativo
        if (function_exists('wp_lazy_loading_enabled')) {
            if (wp_lazy_loading_enabled('img', 'wp_get_attachment_image')) {
                $attr['loading'] = 'lazy';
            }
        } else {
            // Fallback per versioni precedenti
            $attr['loading'] = 'lazy';
            $attr['data-src'] = $attr['src'] ?? '';
            $attr['src'] = 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 1 1\'%3E%3C/svg%3E';
        }
        
        // Aggiungi decoding async
        $attr['decoding'] = 'async';
        
        return $attr;
    }
    
    /**
     * Aggiunge lazy loading al contenuto
     */
    public static function addLazyLoadingToContent(string $content): string
    {
        if (empty($content)) {
            return $content;
        }
        
        // Pattern per trovare immagini
        $pattern = '/<img([^>]+?)>/i';
        
        $content = preg_replace_callback($pattern, function($matches) {
            $img_tag = $matches[0];
            
            // Se già ha loading, non modificare
            if (strpos($img_tag, 'loading=') !== false) {
                return $img_tag;
            }
            
            // Aggiungi loading lazy
            $img_tag = str_replace('<img', '<img loading="lazy" decoding="async"', $img_tag);
            
            return $img_tag;
        }, $content);
        
        return $content;
    }
    
    /**
     * Aggiunge WebP a srcset
     */
    public static function addWebPSrcset($sources, $size_array, $image_src, $image_meta, $attachment_id): array
    {
        if (!function_exists('imagewebp')) {
            return $sources;
        }
        
        // Genera versioni WebP se supportate
        foreach ($sources as $width => $source) {
            $webp_url = self::convertToWebP($source['url']);
            if ($webp_url) {
                $sources[$width]['url'] = $webp_url;
                $sources[$width]['descriptor'] = 'w';
            }
        }
        
        return $sources;
    }
    
    /**
     * Converte immagine a WebP
     */
    private static function convertToWebP(string $image_url): ?string
    {
        $upload_dir = wp_upload_dir();
        $image_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $image_url);
        
        if (!file_exists($image_path)) {
            return null;
        }
        
        $webp_path = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $image_path);
        
        // Se WebP esiste già, restituisci URL
        if (file_exists($webp_path)) {
            return str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $webp_path);
        }
        
        // Prova conversione
        $image_info = getimagesize($image_path);
        if (!$image_info) {
            return null;
        }
        
        $image = null;
        switch ($image_info[2]) {
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($image_path);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($image_path);
                break;
            default:
                return null;
        }
        
        if (!$image) {
            return null;
        }
        
        // Crea WebP
        if (function_exists('imagewebp')) {
            $quality = 85; // Qualità WebP
            if (imagewebp($image, $webp_path, $quality)) {
                imagedestroy($image);
                return str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $webp_path);
            }
        }
        
        if ($image) {
            imagedestroy($image);
        }
        
        return null;
    }
    
    /**
     * Comprime immagine
     */
    public static function compressImage(int $attachment_id, int $quality = 85): bool
    {
        $file_path = get_attached_file($attachment_id);
        
        if (!file_exists($file_path)) {
            return false;
        }
        
        $image_info = getimagesize($file_path);
        if (!$image_info) {
            return false;
        }
        
        $image = null;
        switch ($image_info[2]) {
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($file_path);
                if ($image) {
                    imagejpeg($image, $file_path, $quality);
                    imagedestroy($image);
                    return true;
                }
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($file_path);
                if ($image) {
                    // PNG compression (0-9, 9 = max compression)
                    imagepng($image, $file_path, 9 - round($quality / 10));
                    imagedestroy($image);
                    return true;
                }
                break;
        }
        
        return false;
    }
}
