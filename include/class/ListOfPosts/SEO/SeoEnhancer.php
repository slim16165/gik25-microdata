<?php
namespace gik25microdata\ListOfPosts\SEO;

use gik25microdata\ListOfPosts\Types\LinkBase;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Integrazione SEO (meta tags, schema.org)
 */
class SeoEnhancer
{
    /**
     * Aggiunge meta tags SEO a un link
     * 
     * @param LinkBase $link Link
     * @param array $options Opzioni
     * @return string HTML con meta tags
     */
    public static function addMetaTags(LinkBase $link, array $options = []): string
    {
        $html = '';
        
        // Meta description se disponibile
        if (!empty($link->Comment)) {
            $html .= '<meta name="description" content="' . esc_attr(wp_trim_words($link->Comment, 20)) . '">';
        }
        
        // Open Graph
        $html .= '<meta property="og:url" content="' . esc_url($link->Url) . '">';
        $html .= '<meta property="og:title" content="' . esc_attr($link->Title) . '">';
        
        // Twitter Card
        $html .= '<meta name="twitter:card" content="summary">';
        $html .= '<meta name="twitter:title" content="' . esc_attr($link->Title) . '">';
        
        return $html;
    }
    
    /**
     * Genera schema.org markup per lista di link
     * 
     * @param array $links Array di link
     * @return string JSON-LD
     */
    public static function generateSchemaMarkup(array $links): string
    {
        $items = [];
        
        foreach ($links as $link) {
            $url = $link['target_url'] ?? $link['url'] ?? '';
            $title = $link['nome'] ?? $link['title'] ?? '';
            
            if (!empty($url) && !empty($title)) {
                $items[] = [
                    '@type' => 'ListItem',
                    'position' => count($items) + 1,
                    'name' => $title,
                    'url' => $url,
                ];
            }
        }
        
        if (empty($items)) {
            return '';
        }
        
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'itemListElement' => $items,
        ];
        
        return '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>';
    }
    
    /**
     * Aggiunge attributi SEO a un link HTML
     * 
     * @param string $html HTML del link
     * @param LinkBase $link Link
     * @param array $options Opzioni
     * @return string HTML con attributi SEO
     */
    public static function enhanceLinkHtml(string $html, LinkBase $link, array $options = []): string
    {
        $isExternal = !UrlValidator::isWordPressUrl($link->Url);
        $nofollow = $options['nofollow'] ?? $isExternal;
        $sponsored = $options['sponsored'] ?? false;
        
        // Aggiungi rel="nofollow" se necessario
        if ($nofollow && strpos($html, 'rel=') === false) {
            $html = str_replace('<a ', '<a rel="nofollow" ', $html);
        } elseif ($nofollow) {
            $html = preg_replace('/rel="([^"]*)"/', 'rel="$1 nofollow"', $html);
        }
        
        // Aggiungi rel="sponsored" se necessario
        if ($sponsored) {
            if (strpos($html, 'rel=') === false) {
                $html = str_replace('<a ', '<a rel="sponsored" ', $html);
            } else {
                $html = preg_replace('/rel="([^"]*)"/', 'rel="$1 sponsored"', $html);
            }
        }
        
        // Aggiungi title attribute per accessibilità
        if (strpos($html, 'title=') === false) {
            $html = str_replace('<a ', '<a title="' . esc_attr($link->Title) . '" ', $html);
        }
        
        return $html;
    }
}

// Aggiungi namespace mancante
use gik25microdata\ListOfPosts\Validation\UrlValidator;
