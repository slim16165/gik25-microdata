<?php
namespace gik25microdata\ListOfPosts;

use gik25microdata\ListOfPosts\Types\LinkBase;
use gik25microdata\Widgets\ColorWidget;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Builder unificato per la creazione di link con diverse opzioni di rendering
 * 
 * Astrarre la logica di creazione link da:
 * - linkIfNotSelf() / linkIfNotSelf2() in chiecosa_specific.php
 * - ListOfPostsHelper::GetLinkWithImage()
 * - ColorWidget::GetLinkWithImageCarousel()
 * 
 * @package gik25microdata\ListOfPosts
 */
class LinkBuilder
{
    /**
     * Opzioni di rendering per i link
     */
    public const RENDER_STANDARD = 'standard';      // Link semplice con testo
    public const RENDER_THUMBNAIL = 'thumbnail';    // Link con thumbnail (ListOfPostsHelper)
    public const RENDER_CAROUSEL = 'carousel';      // Link per carousel (ColorWidget)
    
    /**
     * Crea un singolo link con le opzioni specificate
     * 
     * @param string $url URL del link
     * @param string $title Testo del link
     * @param string $comment Commento opzionale
     * @param array $options Opzioni: render_type, remove_if_self, with_image, link_self
     * @return string HTML del link
     */
    public static function createLink(
        string $url,
        string $title,
        string $comment = '',
        array $options = []
    ): string {
        $renderType = $options['render_type'] ?? self::RENDER_STANDARD;
        $removeIfSelf = $options['remove_if_self'] ?? true;
        $withImage = $options['with_image'] ?? false;
        $linkSelf = $options['link_self'] ?? false;
        
        // Normalizza URL per staging
        $url = WPPostsHelper::ReplaceTargetUrlIfStaging($url);
        
        // Verifica se siamo sulla stessa pagina
        $isSamePost = WPPostsHelper::IsTargetUrlSamePost($url);
        
        if ($isSamePost && $removeIfSelf && !$linkSelf) {
            return self::renderCurrentPost($title, $comment, $renderType, $withImage);
        }
        
        // Ottieni dati del post
        list($target_post, $noLink, $debugMsg) = WPPostsHelper::GetPostData($url, $removeIfSelf);
        
        if ($target_post === null || $target_post->post_status !== 'publish') {
            return $debugMsg ?? '';
        }
        
        // Renderizza in base al tipo
        switch ($renderType) {
            case self::RENDER_CAROUSEL:
                return self::renderCarousel($url, $title, $target_post);
                
            case self::RENDER_THUMBNAIL:
                return self::renderThumbnail($url, $title, $comment, $target_post, $noLink);
                
            case self::RENDER_STANDARD:
            default:
                return self::renderStandard($url, $title, $comment, $noLink);
        }
    }
    
    /**
     * Crea una lista di link
     * 
     * @param array $links Array di array con chiavi: url, title, comment (opzionale)
     * @param array $options Opzioni globali per tutti i link
     * @return string HTML della lista
     */
    public static function createLinkList(array $links, array $options = []): string
    {
        $listClass = $options['list_class'] ?? 'my_shortcode_list';
        $wrapperTag = $options['wrapper_tag'] ?? 'ul';
        $columns = $options['columns'] ?? 1;
        
        $html = '';
        foreach ($links as $link) {
            $url = $link['url'] ?? $link['target_url'] ?? '';
            $title = $link['title'] ?? $link['nome'] ?? '';
            $comment = $link['comment'] ?? $link['commento'] ?? '';
            
            if (empty($url) || empty($title)) {
                continue;
            }
            
            $html .= self::createLink($url, $title, $comment, $options);
        }
        
        // Se colonne multiple, usa ListOfPostsHelper
        if ($columns > 1) {
            $helper = new ListOfPostsHelper(
                $options['remove_if_self'] ?? true,
                $options['with_image'] ?? true,
                $options['link_self'] ?? false,
                $columns
            );
            
            $collection = new \Illuminate\Support\Collection();
            foreach ($links as $link) {
                $url = $link['url'] ?? $link['target_url'] ?? '';
                $title = $link['title'] ?? $link['nome'] ?? '';
                $comment = $link['comment'] ?? $link['commento'] ?? '';
                
                if (!empty($url) && !empty($title)) {
                    $collection->add(new LinkBase($title, $url, $comment));
                }
            }
            
            return $helper->GetLinksWithImagesMulticolumn($collection);
        }
        
        return $html;
    }
    
    /**
     * Renderizza link standard (solo testo)
     */
    private static function renderStandard(string $url, string $title, string $comment, bool $noLink): string
    {
        $safeUrl = esc_url($url);
        $safeTitle = esc_html($title);
        $safeComment = esc_html($comment);
        
        if ($noLink) {
            return "<li>$safeTitle $safeComment (articolo corrente)</li>\n";
        }
        
        if (!empty($comment)) {
            return "<li><a href=\"$safeUrl\">$safeTitle</a> $safeComment</li>\n";
        }
        
        return "<li><a href=\"$safeUrl\">$safeTitle</a></li>\n";
    }
    
    /**
     * Renderizza link con thumbnail
     */
    private static function renderThumbnail(
        string $url,
        string $title,
        string $comment,
        $target_post,
        bool $noLink
    ): string {
        return HtmlTemplate::GetTemplateWithThumbnail($url, $title, $comment, $target_post, $noLink);
    }
    
    /**
     * Renderizza link per carousel
     */
    private static function renderCarousel(string $url, string $title, $target_post): string
    {
        $featured_img_url = get_the_post_thumbnail_url($target_post->ID, 'thumbnail');
        return ColorWidget::GetLinkTemplateCarousel($url, $title, $featured_img_url);
    }
    
    /**
     * Renderizza post corrente (senza link)
     */
    private static function renderCurrentPost(
        string $title,
        string $comment,
        string $renderType,
        bool $withImage
    ): string {
        global $post;
        
        if (!$post || !isset($post->ID)) {
            return '';
        }
        
        $safeTitle = esc_html($title);
        $safeComment = esc_html($comment);
        
        if ($renderType === self::RENDER_THUMBNAIL && $withImage) {
            $featured_img_url = get_the_post_thumbnail_url($post->ID, 'thumbnail');
            $imgHtml = '';
            
            if ($featured_img_url) {
                $imgHtml = HtmlTemplate::GetFeaturedImage($featured_img_url, $title);
            }
            
            $commentHtml = !empty($comment) ? "<div class='li-text'>$safeComment</div>" : '';
            
            return "<li><div class='li-img'>$imgHtml</div><div class='li-text'>$safeTitle</div>$commentHtml</li>\n";
        }
        
        if (!empty($comment)) {
            return "<li>$safeTitle $safeComment (articolo corrente)</li>\n";
        }
        
        return "<li>$safeTitle (articolo corrente)</li>\n";
    }
    
    /**
     * Crea link per carousel (metodo di convenienza)
     */
    public static function createCarouselLink(string $url, string $title): string
    {
        return self::createLink($url, $title, '', [
            'render_type' => self::RENDER_CAROUSEL,
            'remove_if_self' => false,
        ]);
    }
    
    /**
     * Crea link con thumbnail (metodo di convenienza)
     */
    public static function createThumbnailLink(
        string $url,
        string $title,
        string $comment = '',
        bool $removeIfSelf = true
    ): string {
        return self::createLink($url, $title, $comment, [
            'render_type' => self::RENDER_THUMBNAIL,
            'with_image' => true,
            'remove_if_self' => $removeIfSelf,
        ]);
    }
}
