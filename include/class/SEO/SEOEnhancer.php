<?php
namespace gik25microdata\SEO;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * SEO Enhancements Avanzati
 * 
 * Auto-generazione meta, schema markup, rich snippets
 */
class SEOEnhancer
{
    /**
     * Inizializza SEO enhancer
     */
    public static function init(): void
    {
        add_action('wp_head', [self::class, 'addMetaTags'], 1);
        add_action('wp_head', [self::class, 'addSchemaMarkup'], 2);
        add_filter('wpseo_metadesc', [self::class, 'enhanceYoastDescription'], 10, 1);
        add_filter('rank_math/frontend/description', [self::class, 'enhanceRankMathDescription'], 10, 1);
    }
    
    /**
     * Aggiunge meta tags avanzati
     */
    public static function addMetaTags(): void
    {
        if (!is_singular()) {
            return;
        }
        
        global $post;
        
        // Open Graph
        echo '<meta property="og:title" content="' . esc_attr(self::getTitle()) . '" />' . "\n";
        echo '<meta property="og:description" content="' . esc_attr(self::getDescription()) . '" />' . "\n";
        echo '<meta property="og:type" content="article" />' . "\n";
        echo '<meta property="og:url" content="' . esc_url(get_permalink()) . '" />' . "\n";
        
        if (has_post_thumbnail($post->ID)) {
            $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'large');
            if ($image) {
                echo '<meta property="og:image" content="' . esc_url($image[0]) . '" />' . "\n";
            }
        }
        
        // Twitter Card
        echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr(self::getTitle()) . '" />' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr(self::getDescription()) . '" />' . "\n";
    }
    
    /**
     * Aggiunge schema.org markup
     */
    public static function addSchemaMarkup(): void
    {
        if (!is_singular()) {
            return;
        }
        
        global $post;
        
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => get_the_title(),
            'description' => self::getDescription(),
            'author' => [
                '@type' => 'Person',
                'name' => get_the_author(),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => get_bloginfo('name'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => get_site_icon_url(),
                ],
            ],
            'datePublished' => get_the_date('c'),
            'dateModified' => get_the_modified_date('c'),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => get_permalink(),
            ],
        ];
        
        if (has_post_thumbnail($post->ID)) {
            $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full');
            if ($image) {
                $schema['image'] = [
                    '@type' => 'ImageObject',
                    'url' => $image[0],
                    'width' => $image[1],
                    'height' => $image[2],
                ];
            }
        }
        
        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";
    }
    
    /**
     * Genera descrizione automatica
     */
    public static function getDescription(): string
    {
        global $post;
        
        // Prova meta description esistente
        $meta = get_post_meta($post->ID, '_yoast_wpseo_metadesc', true);
        if (!empty($meta)) {
            return $meta;
        }
        
        $meta = get_post_meta($post->ID, 'rank_math_description', true);
        if (!empty($meta)) {
            return $meta;
        }
        
        // Genera da excerpt
        if (!empty($post->post_excerpt)) {
            return wp_trim_words($post->post_excerpt, 25);
        }
        
        // Genera da contenuto
        $content = strip_shortcodes($post->post_content);
        $content = wp_strip_all_tags($content);
        $content = wp_trim_words($content, 25);
        
        return $content ?: get_bloginfo('description');
    }
    
    /**
     * Ottiene titolo ottimizzato
     */
    private static function getTitle(): string
    {
        // Prova SEO title
        $seo_title = get_post_meta(get_the_ID(), '_yoast_wpseo_title', true);
        if (!empty($seo_title)) {
            return $seo_title;
        }
        
        $seo_title = get_post_meta(get_the_ID(), 'rank_math_title', true);
        if (!empty($seo_title)) {
            return $seo_title;
        }
        
        return get_the_title();
    }
    
    /**
     * Migliora descrizione Yoast
     */
    public static function enhanceYoastDescription(?string $description): string
    {
        if (!empty($description)) {
            return $description;
        }
        
        return self::getDescription();
    }
    
    /**
     * Migliora descrizione RankMath
     */
    public static function enhanceRankMathDescription(?string $description): string
    {
        if (!empty($description)) {
            return $description;
        }
        
        return self::getDescription();
    }
    
    /**
     * Genera sitemap dinamica
     */
    public static function generateSitemap(): string
    {
        $posts = get_posts([
            'post_type' => 'post',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ]);
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        foreach ($posts as $post) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . esc_url(get_permalink($post->ID)) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . get_the_modified_date('c', $post->ID) . '</lastmod>' . "\n";
            $xml .= '    <changefreq>weekly</changefreq>' . "\n";
            $xml .= '    <priority>0.8</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }
        
        $xml .= '</urlset>';
        
        return $xml;
    }
}
